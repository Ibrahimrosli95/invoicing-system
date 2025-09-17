<?php

namespace App\Http\Controllers;

use App\Models\WebhookEndpoint;
use App\Models\WebhookDelivery;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class WebhookEndpointController extends Controller
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
        $this->authorizeResource(WebhookEndpoint::class, 'webhook_endpoint');
    }

    /**
     * Display a listing of webhook endpoints.
     */
    public function index(Request $request): View
    {
        $query = WebhookEndpoint::forCompany()
            ->with(['deliveries' => function ($query) {
                $query->latest()->limit(5);
            }]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by health status
        if ($request->filled('health')) {
            $query->having('success_rate', $this->getHealthRateRange($request->health));
        }

        $endpoints = $query->paginate(15)->withQueryString();

        // Get summary statistics
        $stats = [
            'total' => WebhookEndpoint::forCompany()->count(),
            'active' => WebhookEndpoint::forCompany()->active()->count(),
            'inactive' => WebhookEndpoint::forCompany()->where('is_active', false)->count(),
            'total_deliveries' => WebhookDelivery::whereHas('webhookEndpoint', function ($q) {
                $q->forCompany();
            })->count(),
            'successful_deliveries' => WebhookDelivery::whereHas('webhookEndpoint', function ($q) {
                $q->forCompany();
            })->where('status', 'sent')->count(),
        ];

        return view('webhooks.index', compact('endpoints', 'stats'));
    }

    /**
     * Show the form for creating a new webhook endpoint.
     */
    public function create(): View
    {
        $availableEvents = WebhookEndpoint::getAvailableEvents();
        
        return view('webhooks.create', compact('availableEvents'));
    }

    /**
     * Store a newly created webhook endpoint.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'description' => 'nullable|string|max:1000',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:' . implode(',', array_keys(WebhookEndpoint::getAvailableEvents())),
            'timeout' => 'nullable|integer|min:5|max:120',
            'max_retries' => 'nullable|integer|min:0|max:10',
            'headers' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        // Parse custom headers if provided
        if ($validated['headers']) {
            try {
                $validated['headers'] = json_decode($validated['headers'], true);
            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['headers' => 'Invalid JSON format for headers.']);
            }
        }

        $validated['company_id'] = auth()->user()->company_id;

        $endpoint = WebhookEndpoint::create($validated);

        return redirect()->route('webhook-endpoints.show', $endpoint)
            ->with('success', 'Webhook endpoint created successfully.');
    }

    /**
     * Display the specified webhook endpoint.
     */
    public function show(WebhookEndpoint $webhookEndpoint): View
    {
        $webhookEndpoint->load(['deliveries' => function ($query) {
            $query->latest()->with('webhookEndpoint');
        }]);

        // Get delivery statistics
        $stats = $this->webhookService->getDeliveryStats($webhookEndpoint);

        // Get recent deliveries (last 50)
        $recentDeliveries = $webhookEndpoint->deliveries()
            ->latest()
            ->limit(50)
            ->get();

        return view('webhooks.show', compact('webhookEndpoint', 'stats', 'recentDeliveries'));
    }

    /**
     * Show the form for editing the webhook endpoint.
     */
    public function edit(WebhookEndpoint $webhookEndpoint): View
    {
        $availableEvents = WebhookEndpoint::getAvailableEvents();
        
        return view('webhooks.edit', compact('webhookEndpoint', 'availableEvents'));
    }

    /**
     * Update the specified webhook endpoint.
     */
    public function update(Request $request, WebhookEndpoint $webhookEndpoint): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'description' => 'nullable|string|max:1000',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:' . implode(',', array_keys(WebhookEndpoint::getAvailableEvents())),
            'timeout' => 'nullable|integer|min:5|max:120',
            'max_retries' => 'nullable|integer|min:0|max:10',
            'headers' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        // Parse custom headers if provided
        if ($validated['headers']) {
            try {
                $validated['headers'] = json_decode($validated['headers'], true);
            } catch (\Exception $e) {
                return back()->withInput()->withErrors(['headers' => 'Invalid JSON format for headers.']);
            }
        }

        $webhookEndpoint->update($validated);

        return redirect()->route('webhook-endpoints.show', $webhookEndpoint)
            ->with('success', 'Webhook endpoint updated successfully.');
    }

    /**
     * Remove the specified webhook endpoint.
     */
    public function destroy(WebhookEndpoint $webhookEndpoint): RedirectResponse
    {
        $webhookEndpoint->delete();

        return redirect()->route('webhook-endpoints.index')
            ->with('success', 'Webhook endpoint deleted successfully.');
    }

    /**
     * Test the webhook endpoint.
     */
    public function test(WebhookEndpoint $webhookEndpoint): JsonResponse
    {
        $this->authorize('view', $webhookEndpoint);

        $result = $this->webhookService->testEndpoint($webhookEndpoint);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['success'] 
                ? 'Webhook test successful!' 
                : 'Webhook test failed: ' . $result['error'],
            'data' => $result,
        ]);
    }

    /**
     * Toggle the active status of the webhook endpoint.
     */
    public function toggleStatus(WebhookEndpoint $webhookEndpoint): JsonResponse
    {
        $this->authorize('update', $webhookEndpoint);

        $webhookEndpoint->update([
            'is_active' => !$webhookEndpoint->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => $webhookEndpoint->is_active 
                ? 'Webhook endpoint activated.' 
                : 'Webhook endpoint deactivated.',
            'is_active' => $webhookEndpoint->is_active,
        ]);
    }

    /**
     * Generate a new secret key for the webhook endpoint.
     */
    public function regenerateSecret(WebhookEndpoint $webhookEndpoint): JsonResponse
    {
        $this->authorize('update', $webhookEndpoint);

        $newSecret = $webhookEndpoint->generateNewSecretKey();

        return response()->json([
            'success' => true,
            'message' => 'Secret key regenerated successfully.',
            'secret_key' => $newSecret,
        ]);
    }

    /**
     * Show delivery logs for the webhook endpoint.
     */
    public function deliveries(Request $request, WebhookEndpoint $webhookEndpoint): View
    {
        $this->authorize('view', $webhookEndpoint);

        $query = $webhookEndpoint->deliveries()->with('webhookEndpoint');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by event type
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $deliveries = $query->latest()->paginate(25)->withQueryString();

        // Get available event types for filtering
        $eventTypes = $webhookEndpoint->deliveries()
            ->select('event_type')
            ->distinct()
            ->pluck('event_type')
            ->toArray();

        return view('webhooks.deliveries', compact('webhookEndpoint', 'deliveries', 'eventTypes'));
    }

    /**
     * Retry failed deliveries for the webhook endpoint.
     */
    public function retryFailed(WebhookEndpoint $webhookEndpoint): JsonResponse
    {
        $this->authorize('update', $webhookEndpoint);

        $failedDeliveries = $webhookEndpoint->deliveries()
            ->where('status', 'failed')
            ->get();

        $retryCount = 0;
        foreach ($failedDeliveries as $delivery) {
            // Reset the delivery for retry
            $delivery->update([
                'status' => 'retrying',
                'attempts' => 0,
                'next_retry_at' => now(),
                'error_message' => null,
            ]);
            
            // Queue for retry
            \App\Jobs\DeliverWebhook::dispatch($delivery)->onQueue('webhooks');
            $retryCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Queued {$retryCount} failed deliveries for retry.",
            'retry_count' => $retryCount,
        ]);
    }

    /**
     * Get health rate range for filtering.
     */
    private function getHealthRateRange(string $health): string
    {
        return match($health) {
            'excellent' => '>=95',
            'good' => '>=80',
            'warning' => '>=60',
            'critical' => '<60',
            default => '>=0',
        };
    }
}