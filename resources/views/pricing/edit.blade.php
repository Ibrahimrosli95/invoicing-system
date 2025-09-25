@extends('layouts.app')

@section('title', 'Edit Pricing Item')

@section('header')
<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit Pricing Item: {{ $pricingItem->name }}
    </h2>
    <div class="flex space-x-3">
        <a href="{{ route('pricing.show', $pricingItem) }}"
           class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm font-medium">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            View Item
        </a>
        <a href="{{ route('pricing.index') }}"
           class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Back to Pricing Book
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="py-6" x-data="pricingEditForm">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Display validation errors -->
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            There were {{ $errors->count() }} error(s) with your submission:
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('pricing.update', $pricingItem) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

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
                                   value="{{ old('name', $pricingItem->name) }}"
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
                                   value="{{ old('item_code', $pricingItem->item_code) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('item_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="pricing_category_id" class="block text-sm font-medium text-gray-700">Category</label>
                            <div class="mt-1 flex">
                                <select id="pricing_category_id"
                                        name="pricing_category_id"
                                        required
                                        class="flex-1 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Select Category --</option>
                                    @forelse($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('pricing_category_id', $pricingItem->pricing_category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @empty
                                        <option value="" disabled>No categories available</option>
                                    @endforelse
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
                            @error('pricing_category_id')
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
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $pricingItem->description) }}</textarea>
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
                                   @input="calculateCurrentMargin"
                                   value="{{ old('cost_price', $pricingItem->cost_price) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('cost_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="unit_price" class="block text-sm font-medium text-gray-700">Unit Price (RM)</label>
                            <input type="number"
                                   id="unit_price"
                                   name="unit_price"
                                   step="0.01"
                                   min="0"
                                   x-model="unitPrice"
                                   @input="calculateCurrentMargin"
                                   value="{{ old('unit_price', $pricingItem->unit_price) }}"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('unit_price')
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
                                   value="{{ old('minimum_price', $pricingItem->minimum_price) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('minimum_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="markup_percentage" class="block text-sm font-medium text-gray-700">Markup Percentage (%)</label>
                            <input type="number"
                                   id="markup_percentage"
                                   name="markup_percentage"
                                   step="0.01"
                                   min="0"
                                   max="1000"
                                   value="{{ old('markup_percentage', $pricingItem->markup_percentage) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('markup_percentage')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Percentage markup over cost price.</p>
                        </div>
                    </div>

                    <!-- Current Margin Display -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div>
                                <span class="text-sm font-medium text-blue-900">Current Margin:</span>
                                <span class="text-lg font-bold text-blue-700" x-text="currentMargin + '%'">{{ number_format($pricingItem->getMarginPercentage(), 2) }}%</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-blue-900">Profit:</span>
                                <span class="text-lg font-bold text-green-700" x-text="'RM ' + currentProfit">RM {{ number_format($pricingItem->getProfit(), 2) }}</span>
                            </div>
                            <div class="flex-1">
                                <div class="w-full bg-blue-200 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all duration-300"
                                         :class="getMarginColorClass(marginStatus)"
                                         :style="'width: ' + Math.min(100, Math.max(0, currentMargin)) + '%'"></div>
                                </div>
                                <p class="text-xs text-blue-600 mt-1" x-text="getMarginStatusText(marginStatus)">
                                    {{ $pricingItem->getMarginPercentage() >= 20 ? 'Good' : ($pricingItem->getMarginPercentage() >= 10 ? 'Medium' : ($pricingItem->getMarginPercentage() >= 0 ? 'Low' : 'Loss')) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Settings Section -->
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Item Settings</h3>

                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox"
                                   id="is_active"
                                   name="is_active"
                                   value="1"
                                   {{ old('is_active', $pricingItem->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Active (item is available for use in quotations)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                    <div class="flex items-center justify-end space-x-3">
                        <a href="{{ route('pricing.show', $pricingItem) }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Item
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function pricingEditForm() {
    return {
        showQuickAddCategory: false,
        newCategoryName: '',
        costPrice: {{ old('cost_price', $pricingItem->cost_price ?? 0) }},
        unitPrice: {{ old('unit_price', $pricingItem->unit_price ?? 0) }},
        currentMargin: {{ number_format($pricingItem->getMarginPercentage(), 2) }},
        currentProfit: {{ number_format($pricingItem->getProfit(), 2) }},
        marginStatus: '{{ $pricingItem->getMarginPercentage() >= 20 ? "good" : ($pricingItem->getMarginPercentage() >= 10 ? "medium" : ($pricingItem->getMarginPercentage() >= 0 ? "low" : "loss")) }}',

        init() {
            this.calculateCurrentMargin();
        },

        async addQuickCategory() {
            if (!this.newCategoryName.trim()) return;

            try {
                const response = await fetch('{{ route("pricing.categories.ajax-store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        name: this.newCategoryName.trim(),
                        description: `Quick category created for ${this.newCategoryName.trim()}`,
                        color: this.getRandomColor()
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Add new option to select
                    const select = document.getElementById('pricing_category_id');
                    const option = document.createElement('option');
                    option.value = data.category.id;
                    option.textContent = data.category.name;
                    option.selected = true;
                    select.appendChild(option);

                    // Reset form
                    this.cancelQuickAddCategory();

                    // Show success message
                    this.showNotification('Category created successfully!', 'success');
                } else {
                    alert('Failed to create category: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while creating the category.');
            }
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

        calculateCurrentMargin() {
            const cost = parseFloat(this.costPrice) || 0;
            const price = parseFloat(this.unitPrice) || 0;

            if (cost > 0 && price > 0) {
                const margin = ((price - cost) / price) * 100;
                this.currentMargin = margin.toFixed(2);
                this.currentProfit = (price - cost).toFixed(2);

                // Set margin status for color coding
                if (margin < 0) this.marginStatus = 'loss';
                else if (margin < 10) this.marginStatus = 'low';
                else if (margin < 20) this.marginStatus = 'medium';
                else this.marginStatus = 'good';
            } else if (price > 0 && cost === 0) {
                this.currentMargin = '100.00';
                this.currentProfit = price.toFixed(2);
                this.marginStatus = 'good';
            } else {
                this.currentMargin = '0.00';
                this.currentProfit = '0.00';
                this.marginStatus = 'none';
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

        getMarginStatusText(status) {
            const texts = {
                'loss': 'Loss',
                'low': 'Low Margin',
                'medium': 'Medium Margin',
                'good': 'Good Margin',
                'none': 'No Margin Data'
            };
            return texts[status] || 'No Margin Data';
        }
    }
}
</script>
@endsection