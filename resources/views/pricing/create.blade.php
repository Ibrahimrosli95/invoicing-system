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
                    </div>
                </div>

                <!-- Segment Pricing Section -->
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Customer Segment Pricing</h3>
                        <div class="flex items-center space-x-4">
                            <button type="button"
                                    @click="generateSuggestedPrices"
                                    class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                Generate Suggested Prices
                            </button>
                            <button type="button"
                                    @click="fillFromBasePrice"
                                    class="text-sm text-gray-600 hover:text-gray-700 font-medium">
                                Fill from Base Price
                            </button>
                        </div>
                    </div>

                    <!-- Pricing Table -->
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Customer Segment
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Selling Price (RM)
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Profit Margin
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($segments as $segment)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-4 h-4 rounded-full" style="background-color: {{ $segment->color }}"></div>
                                                <div>
                                                    <span class="text-sm font-medium text-gray-900">{{ $segment->name }}</span>
                                                    @if($segment->description)
                                                        <div class="text-xs text-gray-500">{{ $segment->description }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number"
                                                   name="segment_prices[{{ $segment->id }}]"
                                                   step="0.01"
                                                   min="0"
                                                   placeholder="0.00"
                                                   x-model="segmentPrices[{{ $segment->id }}]"
                                                   @input="calculateMargin({{ $segment->id }})"
                                                   value="{{ old('segment_prices.' . $segment->id, '0.00') }}"
                                                   class="w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                            @error('segment_prices.' . $segment->id)
                                                <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <span x-text="margins[{{ $segment->id }}] || '0.00'"
                                                      class="text-sm font-medium"
                                                      :class="getMarginTextColor(marginStatus[{{ $segment->id }}])"></span>
                                                <span class="text-xs text-gray-500">%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div x-show="marginStatus[{{ $segment->id }}]"
                                                     :class="getMarginColorClass(marginStatus[{{ $segment->id }}])"
                                                     class="w-3 h-3 rounded-full mr-2"></div>
                                                <span x-show="marginStatus[{{ $segment->id }}]"
                                                      x-text="getMarginStatusText(marginStatus[{{ $segment->id }}])"
                                                      class="text-xs font-medium"
                                                      :class="getMarginTextColor(marginStatus[{{ $segment->id }}])"></span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Base Price Input -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <label for="unit_price" class="block text-sm font-medium text-gray-700">Base Selling Price (RM)</label>
                                <p class="text-xs text-gray-500">Used as fallback when segment-specific pricing is not available</p>
                            </div>
                            <div class="w-48">
                                <input type="number"
                                       id="unit_price"
                                       name="unit_price"
                                       step="0.01"
                                       min="0"
                                       x-model="basePrice"
                                       @input="calculateDefaultMargin"
                                       value="{{ old('unit_price', '0.00') }}"
                                       required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                        @error('unit_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
        costPrice: {{ old('cost_price', '0.00') }},
        basePrice: {{ old('unit_price', '0.00') }},
        segmentPrices: {
            @foreach($segments as $segment)
                {{ $segment->id }}: {{ old('segment_prices.' . $segment->id, '0.00') }},
            @endforeach
        },
        margins: {},
        marginStatus: {},

        init() {
            // Calculate initial margins
            this.calculateMargins();
        },

        calculateMargins() {
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
            } else if (price > 0 && cost === 0) {
                this.margins[segmentId] = '100.00';
                this.marginStatus[segmentId] = 'good';
            } else {
                this.margins[segmentId] = '0.00';
                this.marginStatus[segmentId] = 'none';
            }
        },

        calculateDefaultMargin() {
            // Update all segment margins when cost price changes
            this.calculateMargins();
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

        getMarginTextColor(status) {
            const classes = {
                'loss': 'text-red-600',
                'low': 'text-orange-600',
                'medium': 'text-yellow-600',
                'good': 'text-green-600',
                'none': 'text-gray-500'
            };
            return classes[status] || 'text-gray-500';
        },

        getMarginStatusText(status) {
            const texts = {
                'loss': 'Loss',
                'low': 'Low',
                'medium': 'Medium',
                'good': 'Good',
                'none': 'None'
            };
            return texts[status] || 'None';
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
        },

        fillFromBasePrice() {
            const basePrice = parseFloat(this.basePrice) || 0;
            if (basePrice <= 0) {
                alert('Please enter a base selling price first.');
                return;
            }

            // Apply segment discounts to base price
            @foreach($segments as $segment)
                const discount{{ $segment->id }} = {{ $segment->default_discount_percentage }} || 0;
                const segmentPrice{{ $segment->id }} = basePrice * (1 - discount{{ $segment->id }} / 100);
                this.segmentPrices[{{ $segment->id }}] = segmentPrice{{ $segment->id }}.toFixed(2);
                this.calculateMargin({{ $segment->id }});
            @endforeach
        }
    }
}
</script>
@endsection