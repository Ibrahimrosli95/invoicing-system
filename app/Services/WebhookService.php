<?php

namespace App\Services;

use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Jobs\DeliverWebhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Dispatch webhook for an event
     */
    public function dispatchEvent(string $eventType, array $payload, int $companyId): void
    {
        $endpoints = WebhookEndpoint::forCompany($companyId)
            ->active()
            ->subscribedTo($eventType)
            ->get();

        foreach ($endpoints as $endpoint) {
            $this->createDelivery($endpoint, $eventType, $payload);
        }
    }

    /**
     * Create a webhook delivery record and queue it
     */
    public function createDelivery(WebhookEndpoint $endpoint, string $eventType, array $payload): WebhookDelivery
    {
        // Enhance payload with metadata
        $enrichedPayload = $this->enrichPayload($payload, $eventType, $endpoint->company_id);

        // Generate signature
        $signature = $this->generateSignature($enrichedPayload, $endpoint->secret_key);

        // Create delivery record
        $delivery = WebhookDelivery::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event_type' => $eventType,
            'payload' => $enrichedPayload,
            'signature' => $signature,
            'status' => WebhookDelivery::STATUS_PENDING,
        ]);

        // Queue the delivery
        DeliverWebhook::dispatch($delivery)->onQueue('webhooks');

        return $delivery;
    }

    /**
     * Actually deliver the webhook
     */
    public function deliverWebhook(WebhookDelivery $delivery): void
    {
        $endpoint = $delivery->webhookEndpoint;
        $startTime = microtime(true);

        try {
            $headers = array_merge([
                'Content-Type' => 'application/json',
                'X-Webhook-Event' => $delivery->event_type,
                'X-Webhook-Signature-256' => 'sha256=' . $delivery->signature,
                'X-Webhook-Delivery' => $delivery->id,
                'X-Webhook-Timestamp' => $delivery->created_at->timestamp,
                'User-Agent' => 'Bina-Webhooks/1.0',
            ], $endpoint->headers ?? []);

            $response = Http::withHeaders($headers)
                ->timeout($endpoint->timeout)
                ->post($endpoint->url, $delivery->payload);

            $responseTime = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $delivery->markAsSent(
                    $response->status(),
                    $this->truncateResponse($response->body()),
                    $responseTime
                );
            } else {
                $delivery->markAsFailed(
                    "HTTP {$response->status()}: " . $response->reason(),
                    $response->status(),
                    $this->truncateResponse($response->body())
                );
            }

        } catch (\Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000);
            
            $delivery->markAsFailed(
                $e->getMessage(),
                null,
                null
            );

            Log::error('Webhook delivery failed', [
                'delivery_id' => $delivery->id,
                'endpoint_url' => $endpoint->url,
                'error' => $e->getMessage(),
                'response_time' => $responseTime,
            ]);
        }
    }

    /**
     * Generate HMAC signature for payload verification
     */
    public function generateSignature(array $payload, string $secretKey): string
    {
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        return hash_hmac('sha256', $jsonPayload, $secretKey);
    }

    /**
     * Verify webhook signature
     */
    public function verifySignature(string $payload, string $signature, string $secretKey): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $secretKey);
        return hash_equals($signature, $expectedSignature);
    }

    /**
     * Enrich payload with metadata
     */
    private function enrichPayload(array $payload, string $eventType, int $companyId): array
    {
        return array_merge($payload, [
            'event' => $eventType,
            'timestamp' => now()->toISOString(),
            'company_id' => $companyId,
            'api_version' => '1.0',
        ]);
    }

    /**
     * Truncate response body for storage
     */
    private function truncateResponse(?string $response): ?string
    {
        if (!$response) {
            return null;
        }

        return strlen($response) > 5000 ? substr($response, 0, 5000) . '...' : $response;
    }

    /**
     * Retry failed deliveries
     */
    public function retryFailedDeliveries(): int
    {
        $retryableDeliveries = WebhookDelivery::retryable()->get();
        $retryCount = 0;

        foreach ($retryableDeliveries as $delivery) {
            DeliverWebhook::dispatch($delivery)->onQueue('webhooks');
            $retryCount++;
        }

        return $retryCount;
    }

    /**
     * Test webhook endpoint with ping
     */
    public function testEndpoint(WebhookEndpoint $endpoint): array
    {
        $testPayload = [
            'event' => 'ping',
            'timestamp' => now()->toISOString(),
            'company_id' => $endpoint->company_id,
            'test' => true,
            'message' => 'This is a test webhook from your Bina Invoicing System',
        ];

        $signature = $this->generateSignature($testPayload, $endpoint->secret_key);
        $startTime = microtime(true);

        try {
            $headers = array_merge([
                'Content-Type' => 'application/json',
                'X-Webhook-Event' => 'ping',
                'X-Webhook-Signature-256' => 'sha256=' . $signature,
                'X-Webhook-Timestamp' => now()->timestamp,
                'User-Agent' => 'Bina-Webhooks/1.0',
            ], $endpoint->headers ?? []);

            $response = Http::withHeaders($headers)
                ->timeout($endpoint->timeout)
                ->post($endpoint->url, $testPayload);

            $responseTime = round((microtime(true) - $startTime) * 1000);

            $result = [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'response_time' => $responseTime,
                'response_body' => $this->truncateResponse($response->body()),
                'error' => $response->successful() ? null : $response->reason(),
            ];

            // Update endpoint status
            if ($response->successful()) {
                $endpoint->recordSuccess();
            } else {
                $endpoint->recordFailure();
            }

            return $result;

        } catch (\Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000);
            
            $endpoint->recordFailure();

            return [
                'success' => false,
                'status_code' => null,
                'response_time' => $responseTime,
                'response_body' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get delivery statistics for an endpoint
     */
    public function getDeliveryStats(WebhookEndpoint $endpoint, int $days = 30): array
    {
        $deliveries = $endpoint->deliveries()
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        $total = $deliveries->count();
        $successful = $deliveries->where('status', WebhookDelivery::STATUS_SENT)->count();
        $failed = $deliveries->where('status', WebhookDelivery::STATUS_FAILED)->count();
        $pending = $deliveries->where('status', WebhookDelivery::STATUS_PENDING)->count();
        $retrying = $deliveries->where('status', WebhookDelivery::STATUS_RETRYING)->count();

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'pending' => $pending,
            'retrying' => $retrying,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            'average_response_time' => $deliveries->where('response_time_ms', '>', 0)->avg('response_time_ms'),
        ];
    }
}