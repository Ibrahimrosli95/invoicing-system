@extends('layouts.app')

@section('title', 'Add New Pricing Item')

@section('header')
<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Add New Pricing Item
    </h2>
    <div class="flex space-x-3">
        <a href="{{ route('pricing.import') }}"
           class="bg-green-100 hover:bg-green-200 text-green-800 px-4 py-2 rounded-lg text-sm font-medium">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
            </svg>
            Bulk Import
        </a>
        <a href="{{ route('pricing.index') }}"
           class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Back to Pricing Book
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="py-6" x-data="pricingForm">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('pricing.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="bg-white rounded-lg shadow">
                <!-- Basic Information Section -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Item Name</label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="item_code" class="block text-sm font-medium text-gray-700">Item Code</label>
                            <input type="text"
                                   id="item_code"
                                   name="item_code"
                                   value="{{ old('item_code') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('item_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="pricing_category_id" class="block text-sm font-medium text-gray-700">Category</label>
                            <select id="pricing_category_id"
                                    name="pricing_category_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Category</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}"
                                            {{ old('pricing_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pricing_category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="unit" class="block text-sm font-medium text-gray-700">Unit</label>
                            <input type="text"
                                   id="unit"
                                   name="unit"
                                   value="{{ old('unit', 'pcs') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('unit')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description"
                                      name="description"
                                      rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Cost Price Section -->
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Cost Information</h3>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label for="cost_price" class="block text-sm font-medium text-gray-700">Cost Price (RM)</label>
                            <input type="number"
                                   id="cost_price"
                                   name="cost_price"
                                   step="0.01"
                                   min="0"
                                   x-model="costPrice"
                                   @input="calculateMargins"
                                   value="{{ old('cost_price', '0.00') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('cost_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="minimum_price" class="block text-sm font-medium text-gray-700">Minimum Price (RM)</label>
                            <input type="number"
                                   id="minimum_price"
                                   name="minimum_price"
                                   step="0.01"
                                   min="0"
                                   value="{{ old('minimum_price', '0.00') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('minimum_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Segment Pricing Section -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Segment Pricing</h3>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox"
                                       x-model="useSegmentPricing"
                                       @change="toggleSegmentPricing"
                                       name="use_segment_pricing"
                                       value="1"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-600">Enable segment-specific pricing</span>
                            </label>
                            <button type="button"
                                    @click="generateSuggestedPrices"
                                    class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                Generate Suggested Prices
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4" x-show="useSegmentPricing" x-transition>
                        @foreach($segments as $segment)
                            <div class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center space-x-3 w-48">
                                    <div class="w-4 h-4 rounded-full" style="background-color: {{ $segment->color }}"></div>
                                    <span class="font-medium text-gray-900">{{ $segment->name }}</span>
                                </div>

                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700">Selling Price (RM)</label>
                                    <input type="number"
                                           name="segment_prices[{{ $segment->id }}]"
                                           step="0.01"
                                           min="0"
                                           x-model="segmentPrices[{{ $segment->id }}]"
                                           @input="calculateMargin({{ $segment->id }})"
                                           value="{{ old('segment_prices.' . $segment->id, '0.00') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('segment_prices.' . $segment->id)
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="w-32">
                                    <label class="block text-sm font-medium text-gray-700">Margin</label>
                                    <div class="mt-1 px-3 py-2 bg-gray-50 rounded-md">
                                        <span x-text="margins[{{ $segment->id }}] || '0.00'" class="text-sm font-medium"></span>
                                        <span class="text-xs text-gray-500">%</span>
                                    </div>
                                </div>

                                <div class="w-20">
                                    <div x-show="marginStatus[{{ $segment->id }}]"
                                         :class="getMarginColorClass(marginStatus[{{ $segment->id }}])"
                                         class="w-3 h-3 rounded-full mt-7"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Alternative: Base price with segment discounts -->
                    <div x-show="!useSegmentPricing" x-transition class="space-y-4">
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm text-blue-800">
                                <strong>Default pricing:</strong> Each customer segment will use their default discount percentage applied to the base selling price.
                            </p>
                        </div>

                        <div>
                            <label for="unit_price" class="block text-sm font-medium text-gray-700">Base Selling Price (RM)</label>
                            <input type="number"
                                   id="unit_price"
                                   name="unit_price"
                                   step="0.01"
                                   min="0"
                                   x-model="basePrice"
                                   @input="calculateDefaultMargin"
                                   value="{{ old('unit_price', '0.00') }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('unit_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Preview of segment pricing with default discounts -->
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Price Preview (with segment discounts)</h4>
                            <div class="space-y-2">
                                @foreach($segments as $segment)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $segment->color }}"></div>
                                            <span class="text-sm font-medium">{{ $segment->name }}</span>
                                            @if($segment->default_discount_percentage > 0)
                                                <span class="text-xs text-gray-500">({{ $segment->default_discount_percentage }}% discount)</span>
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium" x-text="'RM ' + getSegmentPreviewPrice({{ $segment->id }}, {{ $segment->default_discount_percentage }})"></span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Section -->
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Status</h3>

                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox"
                                   name="is_active"
                                   value="1"
                                   checked
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600">Active (available for use in quotations)</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox"
                                   name="is_featured"
                                   value="1"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-600">Featured item</span>
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg space-x-4">
                    <a href="{{ route('pricing.index') }}"
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </a>
                    <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Create Pricing Item
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function pricingForm() {
    return {
        useSegmentPricing: {{ old('use_segment_pricing') ? 'true' : 'false' }},
        costPrice: {{ old('cost_price', '0.00') }},
        basePrice: {{ old('unit_price', '0.00') }},
        segmentPrices: {
            @foreach($segments as $segment)
                {{ $segment->id }}: {{ old('segment_prices.' . $segment->id, '0.00') }},
            @endforeach
        },
        margins: {},
        marginStatus: {},

        toggleSegmentPricing() {
            if (this.useSegmentPricing) {
                this.calculateMargins();
            }
        },

        calculateMargins() {
            if (!this.useSegmentPricing) return;

            const cost = parseFloat(this.costPrice) || 0;

            Object.keys(this.segmentPrices).forEach(segmentId => {
                this.calculateMargin(segmentId);
            });
        },

        calculateMargin(segmentId) {
            const cost = parseFloat(this.costPrice) || 0;
            const price = parseFloat(this.segmentPrices[segmentId]) || 0;

            if (cost > 0 && price > 0) {
                const margin = ((price - cost) / price) * 100;
                this.margins[segmentId] = margin.toFixed(2);

                // Set margin status for color coding
                if (margin < 0) this.marginStatus[segmentId] = 'loss';
                else if (margin < 10) this.marginStatus[segmentId] = 'low';
                else if (margin < 20) this.marginStatus[segmentId] = 'medium';
                else this.marginStatus[segmentId] = 'good';
            } else {
                this.margins[segmentId] = '0.00';
                this.marginStatus[segmentId] = 'none';
            }
        },

        calculateDefaultMargin() {
            // Calculate margin for base price (used when segment pricing is disabled)
            const cost = parseFloat(this.costPrice) || 0;
            const price = parseFloat(this.basePrice) || 0;

            if (cost > 0 && price > 0) {
                const margin = ((price - cost) / price) * 100;
                // You could display this margin if needed
            }
        },

        getMarginColorClass(status) {
            const classes = {
                'loss': 'bg-red-500',
                'low': 'bg-orange-500',
                'medium': 'bg-yellow-500',
                'good': 'bg-green-500',
                'none': 'bg-gray-300'
            };
            return classes[status] || 'bg-gray-300';
        },

        getSegmentPreviewPrice(segmentId, discountPercentage) {
            const base = parseFloat(this.basePrice) || 0;
            const discount = discountPercentage || 0;
            const discountedPrice = base * (1 - discount / 100);
            return discountedPrice.toFixed(2);
        },

        generateSuggestedPrices() {
            const cost = parseFloat(this.costPrice) || 0;
            if (cost <= 0) {
                alert('Please enter a cost price first to generate suggested prices.');
                return;
            }

            // Default target margins per segment
            const targetMargins = {
                @foreach($segments as $segment)
                    {{ $segment->id }}: {{
                        $segment->name === 'End User' ? 25 :
                        ($segment->name === 'Contractor' ? 20 : 15)
                    }},
                @endforeach
            };

            Object.keys(targetMargins).forEach(segmentId => {
                const targetMargin = targetMargins[segmentId];
                const suggestedPrice = cost / (1 - targetMargin / 100);
                this.segmentPrices[segmentId] = suggestedPrice.toFixed(2);
                this.calculateMargin(segmentId);
            });
        }
    }
}
</script>
@endsection