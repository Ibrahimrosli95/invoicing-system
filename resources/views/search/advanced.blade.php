@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                        <a href="{{ route('search.index') }}" class="hover:text-gray-700">Search</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <span class="text-gray-900 capitalize">{{ str_replace('_', ' ', $type) }}</span>
                    </nav>
                    <h1 class="text-2xl font-bold text-gray-900 capitalize flex items-center">
                        @if($type === 'leads')
                            <svg class="w-6 h-6 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        @elseif($type === 'quotations')
                            <svg class="w-6 h-6 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        @elseif($type === 'invoices')
                            <svg class="w-6 h-6 mr-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        @else
                            <svg class="w-6 h-6 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        @endif
                        {{ str_replace('_', ' ', $type) }} Search
                    </h1>
                    <p class="text-gray-600 mt-1">Advanced search and filtering for {{ str_replace('_', ' ', $type) }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route($type . '.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        View All {{ ucfirst($type) }}
                    </a>
                </div>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('search.advanced', $type) }}" class="space-y-6" id="advancedSearchForm">
                <!-- Main Search Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" 
                           name="q" 
                           value="{{ $query }}"
                           placeholder="Search {{ str_replace('_', ' ', $type) }}..."
                           class="block w-full pl-10 pr-24 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                           autocomplete="off">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Search
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters -->
                <div class="bg-gray-50 p-6 rounded-lg space-y-6">
                    <h3 class="text-lg font-medium text-gray-900">Filters</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Date Range Filters -->
                        <div class="space-y-4">
                            <h4 class="text-sm font-medium text-gray-700">Date Range</h4>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">From</label>
                                    <input type="date" 
                                           name="filters[date_from]" 
                                           value="{{ $filters['date_from'] ?? '' }}"
                                           class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">To</label>
                                    <input type="date" 
                                           name="filters[date_to]" 
                                           value="{{ $filters['date_to'] ?? '' }}"
                                           class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Status Filters -->
                        @if(isset($filterOptions['status']) && !empty($filterOptions['status']))
                            <div class="space-y-4">
                                <h4 class="text-sm font-medium text-gray-700">Status</h4>
                                <div class="space-y-2 max-h-32 overflow-y-auto">
                                    @foreach($filterOptions['status'] as $statusValue => $statusLabel)
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="filters[status][]" 
                                                   value="{{ $statusValue }}"
                                                   {{ in_array($statusValue, $filters['status'] ?? []) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">{{ $statusLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Amount Range (for financial entities) -->
                        @if(in_array($type, ['quotations', 'invoices']) && isset($filterOptions['amount_ranges']))
                            <div class="space-y-4">
                                <h4 class="text-sm font-medium text-gray-700">Amount Range (RM)</h4>
                                <div class="space-y-2">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">From</label>
                                        <input type="number" 
                                               name="filters[amount_from]" 
                                               value="{{ $filters['amount_from'] ?? '' }}"
                                               min="0" 
                                               step="0.01"
                                               placeholder="0.00"
                                               class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">To</label>
                                        <input type="number" 
                                               name="filters[amount_to]" 
                                               value="{{ $filters['amount_to'] ?? '' }}"
                                               min="0" 
                                               step="0.01"
                                               placeholder="0.00"
                                               class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Team Filter -->
                        @if(isset($filterOptions['teams']) && !empty($filterOptions['teams']))
                            <div class="space-y-4">
                                <h4 class="text-sm font-medium text-gray-700">Team</h4>
                                <select name="filters[team_id]" 
                                        class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Teams</option>
                                    @foreach($filterOptions['teams'] as $teamId => $teamName)
                                        <option value="{{ $teamId }}" 
                                                {{ ($filters['team_id'] ?? '') == $teamId ? 'selected' : '' }}>
                                            {{ $teamName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- User Filter -->
                        @if(isset($filterOptions['users']) && !empty($filterOptions['users']))
                            <div class="space-y-4">
                                <h4 class="text-sm font-medium text-gray-700">Assigned To</h4>
                                <select name="filters[user_id]" 
                                        class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">All Users</option>
                                    @foreach($filterOptions['users'] as $userId => $userName)
                                        <option value="{{ $userId }}" 
                                                {{ ($filters['user_id'] ?? '') == $userId ? 'selected' : '' }}>
                                            {{ $userName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Tags Filter (for leads) -->
                        @if($type === 'leads' && isset($filterOptions['tags']) && !empty($filterOptions['tags']))
                            <div class="space-y-4">
                                <h4 class="text-sm font-medium text-gray-700">Tags</h4>
                                <div class="space-y-2 max-h-32 overflow-y-auto">
                                    @foreach($filterOptions['tags'] as $tag)
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="filters[tags][]" 
                                                   value="{{ $tag }}"
                                                   {{ in_array($tag, $filters['tags'] ?? []) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="ml-2 text-sm text-gray-700">{{ $tag }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <button type="button" 
                                onclick="clearAdvancedFilters()"
                                class="text-sm text-gray-500 hover:text-gray-700">
                            Clear All Filters
                        </button>
                        <div class="flex items-center space-x-3">
                            <button type="button" 
                                    onclick="saveSearch()"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                </svg>
                                Save Search
                            </button>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($searchPerformed)
        <!-- Search Results -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-medium text-gray-900">
                        @if(!empty($query))
                            Search Results for "{{ $query }}" 
                        @else
                            Filtered Results
                        @endif
                        <span class="text-sm text-gray-500">
                            ({{ $results->count() }} {{ $results->count() === 1 ? 'result' : 'results' }})
                        </span>
                    </h2>
                    @if($results->count() > 0)
                        <div class="flex items-center space-x-3">
                            <button onclick="exportResults()" 
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export
                            </button>
                        </div>
                    @endif
                </div>

                @if($results->count() > 0)
                    <!-- Results Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    @if($type === 'leads')
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lead</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    @elseif($type === 'quotations')
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                    @elseif($type === 'invoices')
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    @else
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    @endif
                                    <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($results as $item)
                                    <tr class="hover:bg-gray-50">
                                        @if($type === 'leads')
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $item->email }}</div>
                                                <div class="text-sm text-gray-500">{{ $item->phone }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->company_name ?: '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                                    @if($item->status === 'NEW') bg-blue-100 text-blue-800
                                                    @elseif($item->status === 'CONTACTED') bg-yellow-100 text-yellow-800
                                                    @elseif($item->status === 'QUOTED') bg-purple-100 text-purple-800
                                                    @elseif($item->status === 'WON') bg-green-100 text-green-800
                                                    @else bg-red-100 text-red-800
                                                    @endif">
                                                    {{ $item->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->assignedRep->name ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->created_at->format('M j, Y') }}
                                            </td>
                                        @elseif($type === 'quotations')
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                                {{ $item->number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $item->customer_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $item->customer_email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                RM {{ number_format($item->total, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                                    @if($item->status === 'DRAFT') bg-gray-100 text-gray-800
                                                    @elseif($item->status === 'SENT') bg-blue-100 text-blue-800
                                                    @elseif($item->status === 'ACCEPTED') bg-green-100 text-green-800
                                                    @else bg-red-100 text-red-800
                                                    @endif">
                                                    {{ $item->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->created_at->format('M j, Y') }}
                                            </td>
                                        @elseif($type === 'invoices')
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                                {{ $item->number }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $item->customer_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $item->customer_email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                RM {{ number_format($item->total, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                                    @if($item->status === 'DRAFT') bg-gray-100 text-gray-800
                                                    @elseif($item->status === 'SENT') bg-blue-100 text-blue-800
                                                    @elseif($item->status === 'PAID') bg-green-100 text-green-800
                                                    @elseif($item->status === 'OVERDUE') bg-red-100 text-red-800
                                                    @else bg-yellow-100 text-yellow-800
                                                    @endif">
                                                    {{ $item->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->due_date ? $item->due_date->format('M j, Y') : '-' }}
                                            </td>
                                        @else
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                                {{ $item->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->email }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->phone ?: '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->roles->first()->name ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                                    @if($item->is_active) bg-green-100 text-green-800
                                                    @else bg-red-100 text-red-800
                                                    @endif">
                                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route($type . '.show', $item) }}" 
                                               class="text-blue-600 hover:text-blue-900">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- No Results -->
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No results found</h3>
                        <p class="mt-2 text-gray-500">
                            @if(!empty($query))
                                No {{ str_replace('_', ' ', $type) }} found for "{{ $query }}" with the current filters.
                            @else
                                No {{ str_replace('_', ' ', $type) }} found with the current filters.
                            @endif
                        </p>
                        <div class="mt-6">
                            <button onclick="clearAdvancedFilters()" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Clear Filters
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Recent Searches -->
    @if(!empty($recentSearches) && !$searchPerformed)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Searches</h3>
            <div class="space-y-2">
                @foreach(array_slice($recentSearches, 0, 5) as $recent)
                    @if($recent['type'] === $type || $recent['type'] === 'global')
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('search.advanced', $type) }}?q={{ urlencode($recent['query']) }}&filters={{ http_build_query($recent['filters']) }}" 
                                   class="text-sm text-blue-600 hover:text-blue-800 truncate block">
                                    "{{ $recent['query'] }}"
                                </a>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($recent['timestamp'])->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
// Clear all advanced filters
function clearAdvancedFilters() {
    const form = document.getElementById('advancedSearchForm');
    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
        if (input.type === 'checkbox') {
            input.checked = false;
        } else if (input.name !== 'q') { // Don't clear the main search query
            input.value = '';
        }
    });
}

// Save search functionality
function saveSearch() {
    const form = document.getElementById('advancedSearchForm');
    const formData = new FormData(form);
    const query = formData.get('q');
    
    if (!query) {
        alert('Please enter a search query before saving.');
        return;
    }
    
    const name = prompt('Enter a name for this saved search:');
    if (!name) return;
    
    // Collect filters
    const filters = {};
    for (const [key, value] of formData.entries()) {
        if (key.startsWith('filters[')) {
            const filterKey = key.replace(/^filters\[|\]$/g, '');
            if (filterKey.endsWith('[]')) {
                const baseKey = filterKey.replace('[]', '');
                if (!filters[baseKey]) filters[baseKey] = [];
                filters[baseKey].push(value);
            } else {
                filters[filterKey] = value;
            }
        }
    }
    
    fetch('{{ route("search.save") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            name: name,
            query: query,
            type: '{{ $type }}',
            filters: filters
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Search saved successfully!');
        } else {
            alert('Failed to save search: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the search.');
    });
}

// Export results (placeholder)
function exportResults() {
    alert('Export functionality will be implemented with the existing export system.');
}
</script>
@endsection