@extends('layouts.app')

@section('title', 'Audit Trail')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Audit Trail') }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('audit.dashboard') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    <i class="fas fa-chart-bar mr-2"></i>Dashboard
                </a>
                <button onclick="exportAuditLogs()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </button>
            </div>
        </div>
</div>
@endsection

@section('content')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Audit Logs</h3>

                    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Date Range -->
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">From Date</label>
                            <input type="date" name="date_from" id="date_from"
                                   value="{{ request('date_from') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">To Date</label>
                            <input type="date" name="date_to" id="date_to"
                                   value="{{ request('date_to') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- User Filter -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700">User</label>
                            <select name="user_id" id="user_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Users</option>
                                @foreach($filterOptions['users'] as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Event Filter -->
                        <div>
                            <label for="event" class="block text-sm font-medium text-gray-700">Event</label>
                            <select name="event" id="event"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Events</option>
                                @foreach($filterOptions['events'] as $value => $label)
                                    <option value="{{ $value }}" {{ request('event') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Filter -->
                        <div>
                            <label for="action" class="block text-sm font-medium text-gray-700">Action</label>
                            <select name="action" id="action"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Actions</option>
                                @foreach($filterOptions['actions'] as $value => $label)
                                    <option value="{{ $value }}" {{ request('action') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Model Type Filter -->
                        <div>
                            <label for="model_type" class="block text-sm font-medium text-gray-700">Model Type</label>
                            <select name="model_type" id="model_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Models</option>
                                @foreach($filterOptions['model_types'] as $modelType)
                                    <option value="{{ $modelType['value'] }}" {{ request('model_type') == $modelType['value'] ? 'selected' : '' }}>
                                        {{ $modelType['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Search -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" id="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search users, models, events..."
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- Filter Actions -->
                        <div class="flex items-end space-x-2">
                            <button type="submit"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-filter mr-2"></i>Filter
                            </button>
                            <a href="{{ route('audit.index') }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                                <i class="fas fa-times mr-2"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-list-alt text-gray-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Logs</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_logs']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-gray-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['total_users'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-database text-gray-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Models</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['total_models'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar text-gray-400 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Date Range</dt>
                                    <dd class="text-sm font-medium text-gray-900">
                                        @if($stats['date_range']['from'] && $stats['date_range']['to'])
                                            {{ \Carbon\Carbon::parse($stats['date_range']['from'])->format('M j') }} -
                                            {{ \Carbon\Carbon::parse($stats['date_range']['to'])->format('M j, Y') }}
                                        @else
                                            No data
                                        @endif
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Audit Log Entries
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Complete audit trail of all system activities and changes.
                    </p>
                </div>

                @if($auditLogs->count() > 0)
                    <!-- Desktop Table -->
                    <div class="hidden md:block">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date/Time
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Event
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Model
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Changes
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        IP Address
                                    </th>
                                    <th class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($auditLogs as $log)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>{{ $log->created_at->format('M j, Y') }}</div>
                                            <div class="text-gray-500">{{ $log->created_at->format('g:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8">
                                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">
                                                            {{ substr($log->getUserDisplayName(), 0, 1) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $log->getUserDisplayName() }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $log->event === 'created' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $log->event === 'updated' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $log->event === 'deleted' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ $log->event === 'login' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                                    {{ !in_array($log->event, ['created', 'updated', 'deleted', 'login']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                                    {{ $log->getEventDisplayName() }}
                                                </span>
                                                @if($log->getActionDisplayName())
                                                    <span class="text-xs text-gray-500 mt-1">{{ $log->getActionDisplayName() }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>{{ class_basename($log->auditable_type) }}</div>
                                            <div class="text-gray-500">#{{ $log->auditable_id }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                                            <div class="truncate">
                                                {{ $log->getChangesSummary() ?: 'No changes recorded' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $log->ip_address ?: 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('audit.show', $log) }}"
                                               class="text-indigo-600 hover:text-indigo-900">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden">
                        <div class="space-y-4 p-4">
                            @foreach($auditLogs as $log)
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $log->event === 'created' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $log->event === 'updated' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $log->event === 'deleted' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $log->event === 'login' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                                {{ !in_array($log->event, ['created', 'updated', 'deleted', 'login']) ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ $log->getEventDisplayName() }}
                                            </span>
                                            @if($log->getActionDisplayName())
                                                <span class="text-xs text-gray-500">{{ $log->getActionDisplayName() }}</span>
                                            @endif
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                                    </div>

                                    <div class="text-sm text-gray-900 mb-2">
                                        <strong>{{ $log->getUserDisplayName() }}</strong> modified
                                        <strong>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</strong>
                                    </div>

                                    @if($log->getChangesSummary())
                                        <div class="text-sm text-gray-600 mb-2">
                                            Changes: {{ $log->getChangesSummary() }}
                                        </div>
                                    @endif

                                    <div class="flex justify-between items-center text-xs text-gray-500">
                                        <span>IP: {{ $log->ip_address ?: 'N/A' }}</span>
                                        <a href="{{ route('audit.show', $log) }}"
                                           class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            View Details →
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $auditLogs->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-search text-gray-400 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No audit logs found</h3>
                        <p class="text-gray-500">Try adjusting your filters to see more results.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function exportAuditLogs() {
            // Get current filter parameters
            const params = new URLSearchParams(window.location.search);
            const exportUrl = '{{ route("audit.export") }}?' + params.toString();

            // Download the export
            window.location.href = exportUrl;
        }
    </script>
    @endpush
@endsection