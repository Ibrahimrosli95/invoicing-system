@extends('layouts.app')

@section('title', __('Webhook Deliveries'))

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ __('Delivery History') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Detailed log of webhook deliveries for :name', ['name' => $webhookEndpoint->name]) }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('webhook-endpoints.show', $webhookEndpoint) }}"
               class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                {{ __('Back to Endpoint') }}
            </a>
            <a href="{{ route('webhook-endpoints.index') }}"
               class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                {{ __('All Endpoints') }}
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="py-12">
    <div class="mx-auto max-w-6xl space-y-6 sm:px-6 lg:px-8">
        <div class="rounded-lg bg-white shadow-sm">
            <div class="px-6 py-5 border-b border-gray-200">
                <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-5">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                        <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All') }}</option>
                            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>{{ __('Sent') }}</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('Failed') }}</option>
                            <option value="retrying" {{ request('status') === 'retrying' ? 'selected' : '' }}>{{ __('Retrying') }}</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="event_type" class="block text-sm font-medium text-gray-700">{{ __('Event Type') }}</label>
                        <select name="event_type" id="event_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All events') }}</option>
                            @foreach($eventTypes as $type)
                                <option value="{{ $type }}" {{ request('event_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700">{{ __('From date') }}</label>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700">{{ __('To date') }}</label>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                            {{ __('Filter') }}
                        </button>
                        <a href="{{ route('webhook-endpoints.deliveries', $webhookEndpoint) }}"
                           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                            {{ __('Reset') }}
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Event') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Attempts') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('HTTP Code') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Response Time') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Created') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Error') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($deliveries as $delivery)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $delivery->event_type }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium @class([
                                        'bg-green-100 text-green-800' => $delivery->status === 'sent',
                                        'bg-yellow-100 text-yellow-800' => $delivery->status === 'retrying',
                                        'bg-red-100 text-red-800' => $delivery->status === 'failed',
                                        'bg-blue-100 text-blue-800' => $delivery->status === 'pending',
                                    ])">
                                        {{ $delivery->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $delivery->attempts }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $delivery->http_status_code ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $delivery->formatted_response_time }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $delivery->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($delivery->error_message)
                                        <span class="block max-w-xs truncate" title="{{ $delivery->error_message }}">{{ $delivery->error_message }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                    {{ __('No deliveries match the selected filters.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($deliveries->hasPages())
                <div class="border-t border-gray-200 px-6 py-3">
                    {{ $deliveries->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

