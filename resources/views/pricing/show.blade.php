@extends('layouts.app')

@section('title', 'Pricing Item Details')

@section('header')
{{ $pricingItem->name }}
<span class="text-sm font-normal text-gray-600">Pricing Item Details</span>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column - Image -->
                    <div>
                        @if($pricingItem->image_path)
                            <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                <img src="{{ Storage::url($pricingItem->image_path) }}"
                                     alt="{{ $pricingItem->name }}"
                                     class="w-full h-full object-cover">
                            </div>
                        @else
                            <div class="aspect-square bg-gray-100 rounded-lg flex items-center justify-center">
                                <span class="text-gray-400">No Image</span>
                            </div>
                        @endif


                        <!-- Status Badges -->
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pricingItem->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $pricingItem->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Middle Column - Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

                            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Item Code</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $pricingItem->item_code ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Category</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $pricingItem->category->name ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $pricingItem->created_at->format('M j, Y') }}</dd>
                                </div>
                                @if($pricingItem->description)
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $pricingItem->description }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        <!-- Pricing Information -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Pricing Information</h3>

                            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Cost Price</dt>
                                    <dd class="mt-1 text-lg font-semibold text-gray-900">RM {{ number_format($pricingItem->cost_price, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Selling Price</dt>
                                    <dd class="mt-1 text-lg font-semibold text-blue-600">RM {{ number_format($pricingItem->selling_price, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Minimum Price</dt>
                                    <dd class="mt-1 text-lg font-semibold text-red-600">RM {{ number_format($pricingItem->minimum_price, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Margin</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ number_format($pricingItem->getMarginPercentage(), 2) }}%
                                        @if($pricingItem->target_margin_percentage)
                                            <span class="text-gray-500">(Target: {{ number_format($pricingItem->target_margin_percentage, 2) }}%)</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Profit</dt>
                                    <dd class="mt-1 text-sm text-green-600">RM {{ number_format($pricingItem->getProfit(), 2) }}</dd>
                                </div>
                            </dl>
                        </div>


                        <!-- Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('pricing.index') }}"
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Back to List
                            </a>

                            @can('edit', $pricingItem)
                                <a href="{{ route('pricing.edit', $pricingItem) }}"
                                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Edit Item
                                </a>
                            @endcan

                            @can('duplicate', $pricingItem)
                                <form method="POST" action="{{ route('pricing.duplicate', $pricingItem) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Duplicate
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection