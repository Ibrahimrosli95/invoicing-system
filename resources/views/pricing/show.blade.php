@extends('layouts.app')

@section('title', 'Pricing Item Details')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pricingItem->name }}
        </h2>
        <p class="text-sm text-gray-600 mt-1">Pricing Item Details</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('pricing.index') }}"
           class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Pricing Book
        </a>
        @can('edit', $pricingItem)
            <a href="{{ route('pricing.edit', $pricingItem) }}"
               class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Item
            </a>
        @endcan
        @can('duplicate', $pricingItem)
            <form method="POST" action="{{ route('pricing.duplicate', $pricingItem) }}" class="inline">
                @csrf
                <button type="submit"
                        class="bg-green-500 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Duplicate Item
                </button>
            </form>
        @endcan
    </div>
</div>
@endsection

@section('content')
<div class="py-6" x-data="{
    marginPercentage: {{ $pricingItem->getMarginPercentage() }},
    profitAmount: {{ $pricingItem->getProfit() }}
}">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Status and Quick Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $pricingItem->is_active ? 'bg-green-100' : 'bg-red-100' }}">
                            @if($pricingItem->is_active)
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $pricingItem->is_active ? 'Active' : 'Inactive' }}
                        </p>
                        <p class="text-xs text-gray-500">Status</p>
                    </div>
                </div>
            </div>

            <!-- Margin Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">
                            {{ number_format($pricingItem->getMarginPercentage(), 1) }}%
                        </p>
                        <p class="text-xs text-gray-500">Margin</p>
                    </div>
                </div>
            </div>

            <!-- Profit Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">
                            RM {{ number_format($pricingItem->getProfit(), 2) }}
                        </p>
                        <p class="text-xs text-gray-500">Profit</p>
                    </div>
                </div>
            </div>

            <!-- Category Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900 truncate" title="{{ $pricingItem->category->name ?? 'No Category' }}">
                            {{ $pricingItem->category->name ?? 'No Category' }}
                        </p>
                        <p class="text-xs text-gray-500">Category</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Status -->
            <div class="lg:col-span-1">

                <!-- Margin Progress Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Profit Margin Analysis</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Margin Percentage</span>
                                <span class="text-sm font-semibold {{ $pricingItem->getMarginPercentage() >= 30 ? 'text-green-600' : ($pricingItem->getMarginPercentage() >= 15 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ number_format($pricingItem->getMarginPercentage(), 1) }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $pricingItem->getMarginPercentage() >= 30 ? 'bg-green-500' : ($pricingItem->getMarginPercentage() >= 15 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                     style="width: {{ min($pricingItem->getMarginPercentage(), 100) }}%"></div>
                            </div>
                        </div>

                        <div class="text-xs text-gray-500">
                            @if($pricingItem->getMarginPercentage() >= 30)
                                <span class="text-green-600 font-medium">✓ Excellent margin</span>
                            @elseif($pricingItem->getMarginPercentage() >= 15)
                                <span class="text-yellow-600 font-medium">⚠ Acceptable margin</span>
                            @else
                                <span class="text-red-600 font-medium">⚠ Low margin</span>
                            @endif
                        </div>

                        @if($pricingItem->markup_percentage)
                            <div class="pt-2 border-t border-gray-100">
                                <span class="text-xs text-gray-500">Markup: {{ number_format($pricingItem->markup_percentage, 1) }}%</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column - Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Basic Information
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Item Name</label>
                            <div class="text-base text-gray-900 font-medium">{{ $pricingItem->name }}</div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Item Code</label>
                            <div class="text-base text-gray-900">
                                {{ $pricingItem->item_code ?: 'Not set' }}
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Category</label>
                            <div class="text-base text-gray-900">
                                {{ $pricingItem->category->name ?? 'No category assigned' }}
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Unit</label>
                            <div class="text-base text-gray-900">
                                {{ $pricingItem->unit ?: 'Not specified' }}
                            </div>
                        </div>

                        @if($pricingItem->description)
                            <div class="space-y-1 sm:col-span-2">
                                <label class="text-sm font-medium text-gray-500">Description</label>
                                <div class="text-base text-gray-900">{{ $pricingItem->description }}</div>
                            </div>
                        @endif

                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Created Date</label>
                            <div class="text-base text-gray-900">{{ $pricingItem->created_at->format('M j, Y') }}</div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Last Updated</label>
                            <div class="text-base text-gray-900">{{ $pricingItem->updated_at->format('M j, Y g:i A') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Pricing Information Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        Pricing Information
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Cost Price</label>
                            <div class="text-xl font-semibold text-gray-900">
                                RM {{ number_format($pricingItem->cost_price, 2) }}
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Selling Price</label>
                            <div class="text-xl font-semibold text-blue-600">
                                RM {{ number_format($pricingItem->unit_price, 2) }}
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-500">Minimum Price</label>
                            <div class="text-xl font-semibold text-red-600">
                                RM {{ number_format($pricingItem->minimum_price, 2) }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">
                                    RM {{ number_format($pricingItem->getProfit(), 2) }}
                                </div>
                                <div class="text-sm text-gray-500">Profit per Unit</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold {{ $pricingItem->getMarginPercentage() >= 30 ? 'text-green-600' : ($pricingItem->getMarginPercentage() >= 15 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ number_format($pricingItem->getMarginPercentage(), 1) }}%
                                </div>
                                <div class="text-sm text-gray-500">Profit Margin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection