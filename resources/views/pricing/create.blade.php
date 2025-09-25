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
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                            <div class="mt-1 flex">
                                <select id="category_id"
                                        name="category_id"
                                        class="flex-1 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', request('category_id')) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @can('create', App\Models\PricingCategory::class)
                                    <button type="button"
                                            @click="showQuickAddCategory = !showQuickAddCategory"
                                            class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 bg-gray-50 rounded-r-md text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </button>
                                @endcan
                            </div>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <!-- Quick Add Category Form -->
                            @can('create', App\Models\PricingCategory::class)
                                <div x-show="showQuickAddCategory" x-transition class="mt-3 p-4 border border-gray-200 bg-gray-50 rounded-lg">
                                    <div class="space-y-3">
                                        <div>
                                            <input type="text"
                                                   x-model="newCategoryName"
                                                   placeholder="Category name..."
                                                   @keydown.enter="addQuickCategory"
                                                   @keydown.escape="cancelQuickAddCategory"
                                                   class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button type="button"
                                                    @click="addQuickCategory"
                                                    :disabled="!newCategoryName.trim()"
                                                    class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Add Category
                                            </button>
                                            <button type="button"
                                                    @click="cancelQuickAddCategory"
                                                    class="flex-1 inline-flex justify-center items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endcan
                        </div>

                        <div>
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
                            <!-- Quick Add Segment -->
                            <div class="flex items-center space-x-2" x-show="!showAddSegment">
                                <button type="button"
                                        @click="showAddSegment = true"
                                        class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Segment
                                </button>
                            </div>

                            <!-- Quick Add Form -->
                            <div x-show="showAddSegment" x-transition class="flex items-center space-x-2">
                                <input type="text"
                                       x-model="newSegmentName"
                                       placeholder="Segment name"
                                       @keydown.enter="addSegment"
                                       @keydown.escape="cancelAddSegment"
                                       class="w-40 text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <button type="button"
                                        @click="addSegment"
                                        class="text-green-600 hover:text-green-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                                <button type="button"
                                        @click="cancelAddSegment"
                                        class="text-red-600 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
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
                                <template x-for="segment in activeSegments" :key="segment.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-4 h-4 rounded-full" :style="'background-color: ' + segment.color"></div>
                                                    <div>
                                                        <span class="text-sm font-medium text-gray-900" x-text="segment.name"></span>
                                                        <div class="text-xs text-gray-500" x-show="segment.description" x-text="segment.description"></div>
                                                    </div>
                                                </div>
                                                <button type="button"
                                                        @click="removeSegment(segment.id)"
                                                        x-show="segment.isNew"
                                                        class="text-red-600 hover:text-red-700 text-xs">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number"
                                                   :name="'segment_prices[' + segment.id + ']'"
                                                   step="0.01"
                                                   min="0"
                                                   placeholder="0.00"
                                                   x-model="segmentPrices[segment.id]"
                                                   @input="calculateMargin(segment.id)"
                                                   class="w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <span x-text="margins[segment.id] || '0.00'"
                                                      class="text-sm font-medium"
                                                      :class="getMarginTextColor(marginStatus[segment.id])"></span>
                                                <span class="text-xs text-gray-500">%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div x-show="marginStatus[segment.id]"
                                                     :class="getMarginColorClass(marginStatus[segment.id])"
                                                     class="w-3 h-3 rounded-full mr-2"></div>
                                                <span x-show="marginStatus[segment.id]"
                                                      x-text="getMarginStatusText(marginStatus[segment.id])"
                                                      class="text-xs font-medium"
                                                      :class="getMarginTextColor(marginStatus[segment.id])"></span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Empty State -->
                                <tr x-show="activeSegments.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 9a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium text-gray-900">No segments added</h3>
                                            <p class="mt-1 text-sm text-gray-500">Add customer segments to set specific pricing.</p>
                                        </div>
                                    </td>
                                </tr>
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

                    <!-- Item Status -->
                    <div class="mt-4">
                        <div class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Item is active and visible in pricing book
                            </label>
                        </div>
                        @error('is_active')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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

        // Dynamic segment management
        showAddSegment: false,
        newSegmentName: '',

        // Quick category addition
        showQuickAddCategory: false,
        newCategoryName: '',
        activeSegments: [
            @foreach($segments as $segment)
                {
                    id: {{ $segment->id }},
                    name: '{{ $segment->name }}',
                    discount: {{ $segment->default_discount_percentage }},
                    color: '{{ $segment->color }}',
                    isNew: false
                },
            @endforeach
        ],
        newSegmentCounter: 1000, // Start from high number to avoid conflicts

        init() {
            // Calculate initial margins
            this.calculateMargins();
        },

        addSegment() {
            if (!this.newSegmentName.trim()) {
                alert('Please enter a segment name.');
                return;
            }

            // Generate temporary ID for new segment
            const tempId = 'new_' + this.newSegmentCounter++;

            // Add to active segments
            this.activeSegments.push({
                id: tempId,
                name: this.newSegmentName.trim(),
                discount: 0, // Default discount
                color: this.getRandomColor(),
                isNew: true
            });

            // Initialize pricing for new segment
            this.segmentPrices[tempId] = '0.00';
            this.margins[tempId] = '0.00';
            this.marginStatus[tempId] = 'none';

            // Reset form
            this.newSegmentName = '';
            this.showAddSegment = false;

            // Recalculate margins
            this.calculateMargins();
        },

        removeSegment(segmentId) {
            if (confirm('Are you sure you want to remove this segment from pricing?')) {
                // Remove from active segments
                this.activeSegments = this.activeSegments.filter(segment => segment.id !== segmentId);

                // Remove pricing data
                delete this.segmentPrices[segmentId];
                delete this.margins[segmentId];
                delete this.marginStatus[segmentId];
            }
        },

        cancelAddSegment() {
            this.newSegmentName = '';
            this.showAddSegment = false;
        },

        addQuickCategory() {
            if (!this.newCategoryName.trim()) {
                alert('Please enter a category name.');
                return;
            }

            // Send AJAX request to create category
            fetch('{{ route("pricing.categories.ajax-store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    name: this.newCategoryName.trim()
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add new option to select
                    const categorySelect = document.getElementById('category_id');
                    const option = new Option(data.category.name, data.category.id, true, true);
                    categorySelect.add(option);

                    // Reset form
                    this.newCategoryName = '';
                    this.showQuickAddCategory = false;

                    // Show success message
                    this.showNotification('Category created successfully!', 'success');
                } else {
                    alert('Failed to create category: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the category.');
            });
        },

        cancelQuickAddCategory() {
            this.newCategoryName = '';
            this.showQuickAddCategory = false;
        },

        showNotification(message, type = 'success') {
            // Simple notification - you can enhance this
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-sm font-medium z-50 ${
                type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        },

        getRandomColor() {
            const colors = ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444', '#EC4899', '#84CC16', '#06B6D4'];
            return colors[Math.floor(Math.random() * colors.length)];
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
        }
    }
}
</script>
@endsection