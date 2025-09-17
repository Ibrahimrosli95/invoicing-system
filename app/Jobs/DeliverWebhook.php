<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Services\WebhookService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeliverWebhook implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $tries = 1; // We handle retries manually
    public $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public WebhookDelivery $delivery
    ) {
        $this->queue = 'webhooks';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $webhookService = app(WebhookService::class);
            $webhookService->deliverWebhook($this->delivery);
        } catch (\Exception $e) {
            Log::error('Webhook delivery job failed', [
                'delivery_id' => $this->delivery->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->delivery->markAsFailed($e->getMessage());
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Webhook delivery job permanently failed', [
            'delivery_id' => $this->delivery->id,
            'error' => $exception->getMessage(),
        ]);

        $this->delivery->markAsFailed($exception->getMessage());
    }
}
