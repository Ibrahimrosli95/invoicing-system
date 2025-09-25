@extends('layouts.app')

@section('title', 'Pricing Book')

@section('header')
<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Pricing Book
    </h2>
    <div class="flex space-x-3">
        <a href="{{ route('pricing.import') }}"
           class="bg-green-100 hover:bg-green-200 text-green-800 px-4 py-2 rounded-lg text-sm font-medium">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
            </svg>
            Bulk Import
        </a>
        <a href="{{ route('pricing.segments') }}"
           class="bg-purple-100 hover:bg-purple-200 text-purple-800 px-4 py-2 rounded-lg text-sm font-medium">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Manage Segments
        </a>
        <a href="{{ route('pricing.categories.index') }}"
           class="bg-orange-100 hover:bg-orange-200 text-orange-800 px-4 py-2 rounded-lg text-sm font-medium">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            Manage Categories
        </a>
        <a href="{{ route('pricing.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Item
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="py-6" x-data="pricingBook">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-gray-500">Total Items</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_items'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-gray-500">Active Items</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['active_items'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-gray-500">Segments</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $segments->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5">
                        <p class="text-sm font-medium text-gray-500">Segment Pricing</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $stats['segment_pricing_items'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text"
                                   x-model="search"
                                   @input="filterItems"
                                   placeholder="Search items by name, code, or description..."
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <select x-model="selectedCategory" @change="filterItems" class="rounded-md border-gray-300 text-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <select x-model="selectedSegment" @change="filterItems" class="rounded-md border-gray-300 text-sm">
                            <option value="">All Segments</option>
                            @foreach($segments as $segment)
                                <option value="{{ $segment->id }}">
                                    <span style="background-color: {{ $segment->color }}; width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 4px;"></span>
                                    {{ $segment->name }}
                                </option>
                            @endforeach
                        </select>
                        <select x-model="statusFilter" @change="filterItems" class="rounded-md border-gray-300 text-sm">
                            <option value="">All Status</option>
                            <option value="active">Active Only</option>
                            <option value="inactive">Inactive Only</option>
                            <option value="segment_pricing">Segment Pricing</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Segment Tabs -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    @foreach($segments as $index => $segment)
                        <button @click="activeSegment = {{ $segment->id }}"
                                :class="{
                                    'border-{{ $segment->color ?? 'blue' }}-500 text-{{ $segment->color ?? 'blue' }}-600': activeSegment === {{ $segment->id }},
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeSegment !== {{ $segment->id }}
                                }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 rounded-full" style="background-color: {{ $segment->color }}"></div>
                                <span>{{ $segment->name }}</span>
                                <span class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs">
                                    {{ $items->count() }}
                                </span>
                            </div>
                        </button>
                    @endforeach
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                @foreach($segments as $segment)
                    <div x-show="activeSegment === {{ $segment->id }}" x-transition class="space-y-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ $segment->name }} Pricing</h3>
                                <p class="text-sm text-gray-500">{{ $segment->description }}</p>
                            </div>
                            <div class="flex space-x-2">
                                <button @click="exportSegment({{ $segment->id }})"
                                        class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    Export {{ $segment->name }}
                                </button>
                                <button @click="bulkUpdatePrices({{ $segment->id }})"
                                        class="text-sm text-green-600 hover:text-green-700 font-medium">
                                    Bulk Update
                                </button>
                            </div>
                        </div>

                        <!-- Pricing Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Product
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cost Price
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Selling Price
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Margin
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($items as $item)
                                        @php
                                            $sellingPrice = $item->getSellingPriceForSegment($segment->id);
                                            $margin = $item->getMarginForSegment($segment->id);
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($item->image_path)
                                                        <img src="{{ Storage::url($item->image_path) }}"
                                                             alt="{{ $item->name }}"
                                                             class="w-10 h-10 rounded-lg object-cover mr-3">
                                                    @else
                                                        <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $item->item_code }}</div>
                                                        @if($item->category)
                                                            <div class="text-xs text-gray-400">{{ $item->category->name }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($item->cost_price)
                                                    <span class="font-medium">RM {{ number_format($item->cost_price, 2) }}</span>
                                                @else
                                                    <span class="text-gray-400">Not set</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm font-medium text-gray-900">
                                                        RM {{ number_format($sellingPrice, 2) }}
                                                    </span>
                                                    @if($item->hasSegmentPricing() && isset($item->segment_selling_prices[$segment->id]))
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            Custom
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($margin['margin_percentage'] !== null)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $margin['color'] }} {{ $margin['bg_color'] }}">
                                                        {{ $margin['margin_percentage'] }}%
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 text-sm">N/A</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <button @click="editPrice({{ $item->id }}, {{ $segment->id }})"
                                                            class="text-blue-600 hover:text-blue-900">
                                                        Edit Price
                                                    </button>
                                                    <a href="{{ route('pricing.show', $item) }}"
                                                       class="text-gray-600 hover:text-gray-900">
                                                        View
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($items->isEmpty())
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No pricing items</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by creating your first pricing item.</p>
                                <div class="mt-6">
                                    <a href="{{ route('pricing.create') }}"
                                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add Pricing Item
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Pagination -->
        @if($items->hasPages())
            <div class="mt-6">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function pricingBook() {
    return {
        activeSegment: {{ $segments->first()->id ?? 1 }},
        search: '',
        selectedCategory: '',
        selectedSegment: '',
        statusFilter: '',

        filterItems() {
            // Build query parameters
            const params = new URLSearchParams();
            if (this.search) params.append('search', this.search);
            if (this.selectedCategory) params.append('category', this.selectedCategory);
            if (this.selectedSegment) params.append('segment', this.selectedSegment);
            if (this.statusFilter) params.append('status', this.statusFilter);

            // Reload page with filters
            const url = params.toString() ? `{{ route('pricing.index') }}?${params.toString()}` : '{{ route('pricing.index') }}';
            window.location.href = url;
        },

        editPrice(itemId, segmentId) {
            // Open modal or redirect to edit price for specific segment
            window.location.href = `/pricing/${itemId}/edit?segment=${segmentId}`;
        },

        exportSegment(segmentId) {
            // Export segment pricing data
            window.location.href = `/pricing/export?segment=${segmentId}`;
        },

        bulkUpdatePrices(segmentId) {
            // Open bulk update modal or page
            window.location.href = `/pricing/bulk-update?segment=${segmentId}`;
        }
    }
}
</script>
@endsection