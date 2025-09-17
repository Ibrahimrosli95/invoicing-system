@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
    <!-- Search Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Global Search</h1>
                    <p class="text-gray-600 mt-1">Search across leads, quotations, invoices, and users</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="toggleAdvancedFilters()" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                        Advanced Filters
                    </button>
                    @if(!empty($recentSearches))
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Recent Searches
                            </button>
                            <div x-show="open" @click.away="open = false" 
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                <div class="p-4">
                                    <h3 class="text-sm font-medium text-gray-900 mb-3">Recent Searches</h3>
                                    <div class="space-y-2">
                                        @foreach(array_slice($recentSearches, 0, 5) as $recent)
                                            <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                                                <div class="flex-1 min-w-0">
                                                    <a href="{{ route('search.index', ['q' => $recent['query'], 'filters' => $recent['filters']]) }}" 
                                                       class="text-sm text-blue-600 hover:text-blue-800 truncate block">
                                                        "{{ $recent['query'] }}"
                                                    </a>
                                                    <p class="text-xs text-gray-500">{{ $recent['type'] }} • {{ \Carbon\Carbon::parse($recent['timestamp'])->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if(count($recentSearches) > 5)
                                        <div class="mt-3 pt-3 border-t border-gray-200">
                                            <button onclick="clearRecentSearches()" 
                                                    class="text-xs text-red-600 hover:text-red-800">
                                                Clear All Recent Searches
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('search.index') }}" class="space-y-4" id="searchForm">
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
                           placeholder="Search for customers, quotations, invoices, phone numbers..."
                           class="block w-full pl-10 pr-24 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                           autocomplete="off"
                           id="searchInput">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Search
                        </button>
                    </div>
                    
                    <!-- Search Suggestions Dropdown -->
                    <div id="searchSuggestions" 
                         class="absolute z-10 w-full bg-white mt-1 rounded-lg shadow-lg border border-gray-200 hidden">
                        <div id="suggestionsList" class="py-2"></div>
                    </div>
                </div>

                <!-- Advanced Filters (Hidden by default) -->
                <div id="advancedFilters" class="hidden bg-gray-50 p-4 rounded-lg space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input type="date" 
                                   name="filters[date_from]" 
                                   value="{{ $filters['date_from'] ?? '' }}"
                                   class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input type="date" 
                                   name="filters[date_to]" 
                                   value="{{ $filters['date_to'] ?? '' }}"
                                   class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Amount Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount From (RM)</label>
                            <input type="number" 
                                   name="filters[amount_from]" 
                                   value="{{ $filters['amount_from'] ?? '' }}"
                                   min="0" 
                                   step="0.01"
                                   placeholder="0.00"
                                   class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount To (RM)</label>
                            <input type="number" 
                                   name="filters[amount_to]" 
                                   value="{{ $filters['amount_to'] ?? '' }}"
                                   min="0" 
                                   step="0.01"
                                   placeholder="0.00"
                                   class="block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <button type="button" 
                                onclick="clearFilters()"
                                class="text-sm text-gray-500 hover:text-gray-700">
                            Clear Filters
                        </button>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($searchPerformed)
        <!-- Search Results -->
        <div class="space-y-6">
            @if(!empty($results))
                <!-- Results Summary -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900">
                            Search Results for "{{ $query }}" 
                            <span class="text-sm text-gray-500">
                                ({{ collect($results)->sum('count') }} total results)
                            </span>
                        </h2>
                        @if(collect($results)->sum('count') > 0)
                            <button onclick="exportResults()" 
                                    class="inline-flex items-center px-3 py-1 text-sm text-blue-600 hover:text-blue-800">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export
                            </button>
                        @endif
                    </div>

                    <!-- Results by Type -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        @foreach(['leads', 'quotations', 'invoices', 'users'] as $type)
                            @if(isset($results[$type]))
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">{{ $results[$type]['count'] }}</div>
                                    <div class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $type) }}</div>
                                    @if($results[$type]['total_count'] > $results[$type]['count'])
                                        <div class="text-xs text-gray-500">
                                            ({{ $results[$type]['total_count'] - $results[$type]['count'] }} more)
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Individual Result Sections -->
                @foreach($results as $type => $typeResults)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 capitalize flex items-center">
                                    @if($type === 'leads')
                                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    @elseif($type === 'quotations')
                                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    @elseif($type === 'invoices')
                                        <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    @endif
                                    {{ str_replace('_', ' ', $type) }} 
                                    <span class="ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">
                                        {{ $typeResults['count'] }}
                                    </span>
                                </h3>
                                @if($typeResults['total_count'] > $typeResults['count'])
                                    <a href="{{ route('search.advanced', $type) }}?q={{ urlencode($query) }}" 
                                       class="text-sm text-blue-600 hover:text-blue-800">
                                        View All {{ $typeResults['total_count'] }} Results →
                                    </a>
                                @endif
                            </div>

                            <div class="space-y-3">
                                @foreach($typeResults['results'] as $item)
                                    <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                                        @if($type === 'leads')
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-900">
                                                        <a href="{{ route('leads.show', $item) }}" class="hover:text-blue-600">
                                                            {{ $item->name }}
                                                        </a>
                                                    </h4>
                                                    <p class="text-sm text-gray-600">{{ $item->email }} • {{ $item->phone }}</p>
                                                    @if($item->company_name)
                                                        <p class="text-sm text-gray-500">{{ $item->company_name }}</p>
                                                    @endif
                                                </div>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                                    @if($item->status === 'NEW') bg-blue-100 text-blue-800
                                                    @elseif($item->status === 'CONTACTED') bg-yellow-100 text-yellow-800
                                                    @elseif($item->status === 'QUOTED') bg-purple-100 text-purple-800
                                                    @elseif($item->status === 'WON') bg-green-100 text-green-800
                                                    @else bg-red-100 text-red-800
                                                    @endif">
                                                    {{ $item->status }}
                                                </span>
                                            </div>
                                        @elseif($type === 'quotations')
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-900">
                                                        <a href="{{ route('quotations.show', $item) }}" class="hover:text-blue-600">
                                                            {{ $item->number }}
                                                        </a>
                                                    </h4>
                                                    <p class="text-sm text-gray-600">{{ $item->customer_name }}</p>
                                                    <p class="text-sm text-gray-500">RM {{ number_format($item->total, 2) }}</p>
                                                </div>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                                    @if($item->status === 'DRAFT') bg-gray-100 text-gray-800
                                                    @elseif($item->status === 'SENT') bg-blue-100 text-blue-800
                                                    @elseif($item->status === 'ACCEPTED') bg-green-100 text-green-800
                                                    @else bg-red-100 text-red-800
                                                    @endif">
                                                    {{ $item->status }}
                                                </span>
                                            </div>
                                        @elseif($type === 'invoices')
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-900">
                                                        <a href="{{ route('invoices.show', $item) }}" class="hover:text-blue-600">
                                                            {{ $item->number }}
                                                        </a>
                                                    </h4>
                                                    <p class="text-sm text-gray-600">{{ $item->customer_name }}</p>
                                                    <p class="text-sm text-gray-500">RM {{ number_format($item->total, 2) }}</p>
                                                </div>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                                    @if($item->status === 'DRAFT') bg-gray-100 text-gray-800
                                                    @elseif($item->status === 'SENT') bg-blue-100 text-blue-800
                                                    @elseif($item->status === 'PAID') bg-green-100 text-green-800
                                                    @elseif($item->status === 'OVERDUE') bg-red-100 text-red-800
                                                    @else bg-yellow-100 text-yellow-800
                                                    @endif">
                                                    {{ $item->status }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-gray-900">
                                                        <a href="{{ route('users.show', $item) }}" class="hover:text-blue-600">
                                                            {{ $item->name }}
                                                        </a>
                                                    </h4>
                                                    <p class="text-sm text-gray-600">{{ $item->email }}</p>
                                                    @if($item->phone)
                                                        <p class="text-sm text-gray-500">{{ $item->phone }}</p>
                                                    @endif
                                                </div>
                                                @if($item->roles->count() > 0)
                                                    <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                                        {{ $item->roles->first()->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- No Results -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No results found</h3>
                    <p class="mt-2 text-gray-500">
                        No results found for "{{ $query }}". Try adjusting your search terms or filters.
                    </p>
                    <div class="mt-6">
                        <button onclick="clearSearch()" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Clear Search
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @elseif(!empty($query))
        <!-- Loading State (if needed) -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-500">Searching...</p>
        </div>
    @endif

    <!-- Search Analytics -->
    @if(!empty($analytics))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Search Analytics</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $analytics['total_searches_today'] }}</div>
                    <div class="text-sm text-gray-600">Searches Today</div>
                </div>
                @if(!empty($analytics['search_frequency']))
                    @foreach($analytics['search_frequency'] as $type => $count)
                        <div class="text-center">
                            <div class="text-xl font-semibold text-gray-700">{{ $count }}</div>
                            <div class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $type) }}</div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    @endif
</div>

<script>
// Search functionality
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const suggestionsDiv = document.getElementById('searchSuggestions');
const suggestionsList = document.getElementById('suggestionsList');

// Search suggestions
searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const query = this.value.trim();
    
    if (query.length >= 2) {
        searchTimeout = setTimeout(() => {
            fetchSuggestions(query);
        }, 300);
    } else {
        hideSuggestions();
    }
});

// Hide suggestions when clicking outside
document.addEventListener('click', function(e) {
    if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
        hideSuggestions();
    }
});

function fetchSuggestions(query) {
    fetch(`{{ route('search.suggestions') }}?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.suggestions.length > 0) {
                displaySuggestions(data.suggestions);
            } else {
                hideSuggestions();
            }
        })
        .catch(error => {
            console.error('Error fetching suggestions:', error);
            hideSuggestions();
        });
}

function displaySuggestions(suggestions) {
    suggestionsList.innerHTML = '';
    
    suggestions.forEach(suggestion => {
        const item = document.createElement('div');
        item.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer flex items-center';
        item.innerHTML = `
            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <span class="text-sm">${suggestion.label}</span>
        `;
        
        item.addEventListener('click', function() {
            searchInput.value = suggestion.value;
            hideSuggestions();
            document.getElementById('searchForm').submit();
        });
        
        suggestionsList.appendChild(item);
    });
    
    suggestionsDiv.classList.remove('hidden');
}

function hideSuggestions() {
    suggestionsDiv.classList.add('hidden');
}

// Advanced filters toggle
function toggleAdvancedFilters() {
    const filtersDiv = document.getElementById('advancedFilters');
    filtersDiv.classList.toggle('hidden');
}

// Clear filters
function clearFilters() {
    const form = document.getElementById('searchForm');
    const inputs = form.querySelectorAll('input[name^="filters"]');
    inputs.forEach(input => input.value = '');
}

// Clear search
function clearSearch() {
    searchInput.value = '';
    clearFilters();
    window.location.href = '{{ route('search.index') }}';
}

// Clear recent searches
function clearRecentSearches() {
    if (confirm('Are you sure you want to clear all recent searches?')) {
        fetch('{{ route('search.clear-recent') }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Export results (placeholder)
function exportResults() {
    // This would trigger the export functionality
    alert('Export functionality will be implemented with the existing export system.');
}
</script>
@endsection