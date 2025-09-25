@extends('layouts.app')

@section('title', __('Webhook Endpoint Details'))

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ $webhookEndpoint->name }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ $webhookEndpoint->description ?: __('No description provided.') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('webhook-endpoints.edit', $webhookEndpoint) }}"
               class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 shadow-sm hover:bg-blue-100">
                {{ __('Edit') }}
            </a>
            <a href="{{ route('webhook-endpoints.deliveries', $webhookEndpoint) }}"
               class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 shadow-sm hover:bg-indigo-100">
                {{ __('View Deliveries') }}
            </a>
            <a href="{{ route('webhook-endpoints.index') }}"
               class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                {{ __('Back to Endpoints') }}
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="py-12" x-data="webhookDetailPage()">
    <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white shadow-sm">
            <div class="grid grid-cols-1 gap-6 px-6 py-6 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-2">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-5">
                        <h3 class="text-sm font-semibold text-gray-800">{{ __('Endpoint URL') }}</h3>
                        <a href="{{ $webhookEndpoint->url }}" target="_blank" rel="noopener"
                           class="mt-2 inline-flex items-center text-sm text-blue-600 hover:text-blue-800 break-all">
                            {{ $webhookEndpoint->url }}
                        </a>
                        <p class="mt-3 text-xs text-gray-400">{{ __('Created') }} {{ $webhookEndpoint->created_at->diffForHumans() }} | {{ __('Last updated') }} {{ $webhookEndpoint->updated_at->diffForHumans() }}</p>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800">{{ __('Secret Key') }}</h3>
                            <button type="button" @click="regenerateSecret"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                {{ __('Regenerate') }}
                            </button>
                        </div>
                        <div class="mt-3 flex items-center gap-3">
                            <code class="rounded bg-gray-900 px-3 py-2 text-xs text-green-200" x-text="secret"></code>
                            <button type="button" @click="copySecret"
                                    class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                {{ __('Copy') }}
                            </button>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-5">
                        <h3 class="text-sm font-semibold text-gray-800">{{ __('Subscribed Events') }}</h3>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($webhookEndpoint->subscribed_events_labels as $label)
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $label }}</span>
                            @endforeach
                            @if(empty($webhookEndpoint->subscribed_events_labels))
                                <p class="text-xs text-gray-500">{{ __('No events configured.') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-800">{{ __('Recent Deliveries') }}</h3>
                                <p class="mt-1 text-xs text-gray-500">{{ __('Last 10 delivery attempts for this endpoint.') }}</p>
                            </div>
                            <a href="{{ route('webhook-endpoints.deliveries', $webhookEndpoint) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                {{ __('View full log') }}
                            </a>
                        </div>
                        <div class="mt-4 space-y-3">
                            @forelse($recentDeliveries as $delivery)
                                <div class="rounded border border-gray-100 bg-gray-50 px-4 py-3">
                                    <div class="flex items-center justify-between text-sm">
                                        <div>
                                            <span class="font-semibold text-gray-800">{{ $delivery->event_type }}</span>
                                            <span class="ml-2 text-xs text-gray-500">{{ $delivery->created_at->diffForHumans() }}</span>
                                        </div>
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium @class([
                                            'bg-green-100 text-green-800' => $delivery->status === 'sent',
                                            'bg-yellow-100 text-yellow-800' => $delivery->status === 'retrying',
                                            'bg-red-100 text-red-800' => $delivery->status === 'failed',
                                            'bg-blue-100 text-blue-800' => $delivery->status === 'pending',
                                        ])">
                                            {{ $delivery->status_label }}
                                        </span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-xs text-gray-500">
                                        <span>{{ __('Attempts') }}: {{ $delivery->attempts }}</span>
                                        <span>{{ __('Response') }}: {{ $delivery->formatted_response_time }}</span>
                                        <span>{{ __('HTTP') }}: {{ $delivery->http_status_code ?: '-' }}</span>
                                    </div>
                                    @if($delivery->error_message)
                                        <p class="mt-2 text-xs text-red-600">{{ $delivery->error_message }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">{{ __('No delivery history yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-5">
                        <h3 class="text-sm font-semibold text-gray-800">{{ __('Status') }}</h3>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="statusBadgeClass" x-text="statusLabel"></span>
                            <button type="button" @click="toggleStatus"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                {{ __('Toggle') }}
                            </button>
                        </div>
                        <dl class="mt-4 space-y-2 text-sm text-gray-600">
                            <div class="flex justify-between">
                                <dt>{{ __('Success Rate') }}</dt>
                                <dd>{{ number_format($webhookEndpoint->success_rate, 1) }}%</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>{{ __('Success Count') }}</dt>
                                <dd>{{ number_format($webhookEndpoint->success_count) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>{{ __('Failure Count') }}</dt>
                                <dd>{{ number_format($webhookEndpoint->failure_count) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>{{ __('Last Ping') }}</dt>
                                <dd>{{ $webhookEndpoint->last_ping_at ? $webhookEndpoint->last_ping_at->diffForHumans() : __('Never') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-5 space-y-3">
                        <h3 class="text-sm font-semibold text-gray-800">{{ __('Actions') }}</h3>
                        <button type="button" @click="testEndpoint"
                                class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                            {{ __('Send Test Payload') }}
                        </button>
                        <button type="button" @click="retryFailed"
                                class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                            {{ __('Retry Failed Deliveries') }}
                        </button>
                        <form method="POST" action="{{ route('webhook-endpoints.destroy', $webhookEndpoint) }}" onsubmit="return confirm('{{ __('Are you sure? This cannot be undone.') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="mt-2 w-full rounded-md border border-red-400 bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow-sm hover:bg-red-50">
                                {{ __('Delete Endpoint') }}
                            </button>
                        </form>
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-5">
                        <h3 class="text-sm font-semibold text-gray-800">{{ __('Delivery Summary (30 days)') }}</h3>
                        <dl class="mt-4 space-y-2 text-sm text-gray-600">
                            <div class="flex justify-between">
                                <dt>{{ __('Deliveries sent') }}</dt>
                                <dd>{{ number_format($stats['sent'] ?? 0) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>{{ __('Deliveries failed') }}</dt>
                                <dd>{{ number_format($stats['failed'] ?? 0) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>{{ __('Average response time') }}</dt>
                                <dd>{{ $stats['avg_response_time'] ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function webhookDetailPage() {
    return {
        status: {{ $webhookEndpoint->is_active ? 'true' : 'false' }},
        secret: '{{ $webhookEndpoint->secret_key }}',
        toggleStatus() {
            this.request('{{ route('webhook-endpoints.toggle-status', $webhookEndpoint) }}', 'POST')
                .then(response => {
                    if (response && response.success) {
                        this.status = response.is_active;
                    }
                });
        },
        regenerateSecret() {
            this.request('{{ route('webhook-endpoints.regenerate-secret', $webhookEndpoint) }}', 'POST')
                .then(response => {
                    if (response && response.success) {
                        this.secret = response.secret_key;
                    }
                });
        },
        testEndpoint() {
            this.request('{{ route('webhook-endpoints.test', $webhookEndpoint) }}', 'POST')
                .then(response => {
                    if (response) {
                        alert(response.message || '{{ __('Test completed') }}');
                    }
                });
        },
        retryFailed() {
            this.request('{{ route('webhook-endpoints.retry-failed', $webhookEndpoint) }}', 'POST')
                .then(response => {
                    if (response) {
                        alert(response.message || '{{ __('Retry queued') }}');
                    }
                });
        },
        copySecret() {
            navigator.clipboard.writeText(this.secret).then(() => {
                alert('{{ __('Secret copied to clipboard') }}');
            });
        },
        request(url, method) {
            return fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .catch(() => {
                alert('{{ __('Something went wrong. Please try again.') }}');
            });
        },
        get statusLabel() {
            return this.status ? '{{ __('Active') }}' : '{{ __('Inactive') }}';
        },
        get statusBadgeClass() {
            return this.status ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700';
        }
    };
}
</script>
@endsection



