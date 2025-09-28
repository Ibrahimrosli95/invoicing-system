@extends('layouts.app')

@section('title', 'Webhook Endpoints')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ __('Webhook Endpoints') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Monitor, manage, and troubleshoot outbound webhooks for your integrations.') }}</p>
        </div>
        @can('create', \App\Models\WebhookEndpoint::class)
            <a href="{{ route('webhook-endpoints.create') }}"
               class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('Add Endpoint') }}
            </a>
        @endcan
    </div>
</div>
@endsection

@section('content')
<div class="py-12">
    <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

        {{-- Summary statistics --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3 xl:grid-cols-5">
            @php
                $statCards = [
                    [
                        'label' => 'Total Endpoints',
                        'value' => number_format($stats['total'] ?? 0),
                        'icon' => 'server',
                        'tone' => 'text-blue-600 bg-blue-100'
                    ],
                    [
                        'label' => 'Active',
                        'value' => number_format($stats['active'] ?? 0),
                        'icon' => 'bolt',
                        'tone' => 'text-green-600 bg-green-100'
                    ],
                    [
                        'label' => 'Inactive',
                        'value' => number_format($stats['inactive'] ?? 0),
                        'icon' => 'pause',
                        'tone' => 'text-gray-600 bg-gray-100'
                    ],
                    [
                        'label' => 'Deliveries',
                        'value' => number_format($stats['total_deliveries'] ?? 0),
                        'icon' => 'paper-airplane',
                        'tone' => 'text-indigo-600 bg-indigo-100'
                    ],
                    [
                        'label' => 'Successful',
                        'value' => number_format($stats['successful_deliveries'] ?? 0),
                        'icon' => 'check-circle',
                        'tone' => 'text-emerald-600 bg-emerald-100'
                    ],
                ];

                $iconPaths = [
                    'server' => 'M4.5 6.75A2.25 2.25 0 016.75 4.5h10.5A2.25 2.25 0 0119.5 6.75v2.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 9.25v-2.5zM4.5 14.75A2.25 2.25 0 016.75 12.5h10.5a2.25 2.25 0 012.25 2.25v2.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 17.25v-2.5z',
                    'bolt' => 'M12 2.25a.75.75 0 01.674.418l5.25 10.5A.75.75 0 0117.25 14h-4.5l1.5 7.5-7.5-10.5h4.5L11.326 2.668A.75.75 0 0112 2.25z',
                    'pause' => 'M8.25 4.5A1.5 1.5 0 009.75 3h.75A1.5 1.5 0 0112 4.5v15a1.5 1.5 0 01-1.5 1.5h-.75a1.5 1.5 0 01-1.5-1.5v-15zm6 0A1.5 1.5 0 0115.75 3h.75A1.5 1.5 0 0118 4.5v15a1.5 1.5 0 01-1.5 1.5h-.75a1.5 1.5 0 01-1.5-1.5v-15z',
                    'paper-airplane' => 'M11.7 1.6a.75.75 0 011.37 0l8.25 18.562a.75.75 0 01-1.042.96l-6.656-2.998a.75.75 0 00-.64 0L6.323 21.12a.75.75 0 01-1.042-.96L11.7 1.6z',
                    'check-circle' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                ];
            @endphp

            @foreach($statCards as $card)
                <div class="rounded-lg bg-white shadow-sm">
                    <div class="flex items-center justify-between p-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __($card['label']) }}</p>
                            <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $card['value'] }}</p>
                        </div>
                        <span class="flex h-11 w-11 items-center justify-center rounded-full {{ $card['tone'] }}">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="{{ $iconPaths[$card['icon']] }}" />
                            </svg>
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Filters --}}
        <div class="rounded-lg bg-white shadow-sm">
            <div class="p-6">
                <form method="GET" action="{{ route('webhook-endpoints.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Name or URL') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                        <select id="status" name="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All') }}</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="health" class="block text-sm font-medium text-gray-700">{{ __('Health') }}</label>
                        <select id="health" name="health"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All') }}</option>
                            <option value="excellent" {{ request('health') === 'excellent' ? 'selected' : '' }}>{{ __('Excellent (>=95%)') }}</option>
                            <option value="good" {{ request('health') === 'good' ? 'selected' : '' }}>{{ __('Good (>=80%)') }}</option>
                            <option value="warning" {{ request('health') === 'warning' ? 'selected' : '' }}>{{ __('Warning (>=60%)') }}</option>
                            <option value="critical" {{ request('health') === 'critical' ? 'selected' : '' }}>{{ __('Critical (<60%)') }}</option>
                        </select>
                    </div>

                    <div class="flex items-end space-x-2">
                        <button type="submit"
                                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            {{ __('Apply Filters') }}
                        </button>
                        <a href="{{ route('webhook-endpoints.index') }}"
                           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Endpoints table --}}
        <div class="overflow-hidden rounded-lg bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Endpoint') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Health') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Subscribed Events') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Recent Deliveries') }}</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($endpoints as $endpoint)
                            @php
                                $statusClass = $endpoint->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600';
                                $healthClasses = [
                                    'excellent' => 'bg-green-100 text-green-800',
                                    'good' => 'bg-blue-100 text-blue-800',
                                    'warning' => 'bg-yellow-100 text-yellow-800',
                                    'critical' => 'bg-red-100 text-red-800',
                                    'default' => 'bg-gray-100 text-gray-800',
                                ];
                                $healthClass = $healthClasses[$endpoint->health_status] ?? $healthClasses['default'];
                                $healthLabel = ucfirst($endpoint->health_status);
                                $eventLabels = collect($endpoint->subscribed_events_labels ?? []);
                                $deliveryColors = [
                                    'green' => 'bg-green-100 text-green-800',
                                    'blue' => 'bg-blue-100 text-blue-800',
                                    'yellow' => 'bg-yellow-100 text-yellow-800',
                                    'red' => 'bg-red-100 text-red-800',
                                    'gray' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <tr>
                                <td class="px-6 py-4 align-top text-sm text-gray-900">
                                    <div class="flex flex-col space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-base font-semibold text-gray-900">{{ $endpoint->name }}</span>
                                            @if($endpoint->last_ping_status === 'success')
                                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">{{ __('Healthy') }}</span>
                                            @elseif($endpoint->last_ping_status === 'failed')
                                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">{{ __('Issues') }}</span>
                                            @endif
                                        </div>
                                        <a href="{{ $endpoint->url }}" target="_blank" rel="noopener"
                                           class="text-sm text-blue-600 hover:text-blue-800">{{ $endpoint->url }}</a>
                                        @if($endpoint->description)
                                            <p class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($endpoint->description, 120) }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400">{{ __('Created') }} {{ $endpoint->created_at->diffForHumans() }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top text-sm text-gray-900">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">
                                        {{ $endpoint->is_active ? __('Active') : __('Inactive') }}
                                    </span>
                                    <div class="mt-2 text-xs text-gray-500">
                                        {{ __('Timeout') }}: {{ $endpoint->timeout ? $endpoint->timeout . __('s') : __('Default') }}
                                        <br>
                                        {{ __('Retries') }}: {{ $endpoint->max_retries ?? 0 }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top text-sm text-gray-900">
                                    <div class="flex flex-col space-y-1">
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $healthClass }}">
                                            {{ $healthLabel }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ __('Success Rate') }}: {{ number_format($endpoint->success_rate, 1) }}%</span>
                                        <span class="text-xs text-gray-500">
                                            {{ __('Last Ping') }}:
                                            {{ $endpoint->last_ping_at ? $endpoint->last_ping_at->diffForHumans() : __('Never') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top text-sm text-gray-900">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($eventLabels->take(6) as $label)
                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $label }}</span>
                                        @empty
                                            <span class="text-xs text-gray-500">{{ __('No events configured') }}</span>
                                        @endforelse
                                    </div>
                                    @if($eventLabels->count() > 6)
                                        <p class="mt-1 text-xs text-gray-500">+ {{ $eventLabels->count() - 6 }} {{ __('more') }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 align-top text-sm text-gray-900">
                                    <div class="space-y-2">
                                        @forelse($endpoint->deliveries as $delivery)
                                            @php
                                                $statusTone = $deliveryColors[$delivery->status_color] ?? $deliveryColors['gray'];
                                            @endphp
                                            <div class="rounded border border-gray-100 px-3 py-2">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-700">{{ $delivery->event_type }}</span>
                                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusTone }}">
                                                        {{ $delivery->status_label }}
                                                    </span>
                                                </div>
                                                <div class="mt-1 text-[11px] text-gray-500">
                                                    {{ __('Attempt') }}: {{ $delivery->attempts }}  |  {{ __('Response') }}: {{ $delivery->formatted_response_time }}
                                                </div>
                                                <div class="mt-1 text-[11px] text-gray-400">
                                                    {{ $delivery->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-xs text-gray-500">{{ __('No recent deliveries') }}</p>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-top text-right text-sm">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('webhook-endpoints.show', $endpoint) }}"
                                           class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                            {{ __('View') }}
                                        </a>
                                        @can('update', $endpoint)
                                            <a href="{{ route('webhook-endpoints.edit', $endpoint) }}"
                                               class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100">
                                                {{ __('Edit') }}
                                            </a>
                                        @endcan
                                        <a href="{{ route('webhook-endpoints.deliveries', $endpoint) }}"
                                           class="inline-flex items-center rounded-md border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-100">
                                            {{ __('Deliveries') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center space-y-2">
                                        <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5.25C3 4.007 4.007 3 5.25 3h13.5C19.993 3 21 4.007 21 5.25v13.5A2.25 2.25 0 0118.75 21H5.25A2.25 2.25 0 013 18.75V5.25zM8.25 8.25h7.5M8.25 12h4.5" />
                                        </svg>
                                        <p>{{ __('No webhook endpoints found. Create your first endpoint to start receiving events.') }}</p>
                                        @can('create', \App\Models\WebhookEndpoint::class)
                                            <a href="{{ route('webhook-endpoints.create') }}"
                                               class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                                                {{ __('Create Endpoint') }}
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($endpoints->hasPages())
                <div class="border-t border-gray-200 px-6 py-3">
                    {{ $endpoints->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection





