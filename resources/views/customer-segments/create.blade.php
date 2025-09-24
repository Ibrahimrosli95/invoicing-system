@extends('layouts.app')

@section('title', 'Create Customer Segment')

@section('content')
<div class="bg-white shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Customer Segment</h1>
                    <p class="mt-1 text-sm text-gray-500">Define a new customer segment with pricing preferences</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('customer-segments.index') }}"
                       class="bg-gray-500 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Back to Segments
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('customer-segments.store') }}" x-data="segmentForm">
            @csrf

            <div class="space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Segment Name</label>
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
                        <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                        <input type="number"
                               id="sort_order"
                               name="sort_order"
                               value="{{ old('sort_order', 1) }}"
                               min="0"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Lower numbers appear first in lists</p>
                    </div>
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
                    <p class="mt-1 text-sm text-gray-500">Brief description of this customer segment</p>
                </div>

                <!-- Pricing Settings -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="default_discount_percentage" class="block text-sm font-medium text-gray-700">Default Discount Percentage</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="number"
                                   id="default_discount_percentage"
                                   name="default_discount_percentage"
                                   value="{{ old('default_discount_percentage', 0) }}"
                                   step="0.01"
                                   min="0"
                                   max="100"
                                   required
                                   class="block w-full pr-12 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">%</span>
                            </div>
                        </div>
                        @error('default_discount_percentage')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Default discount applied to base prices for this segment</p>
                    </div>

                    <div>
                        <label for="color" class="block text-sm font-medium text-gray-700">Segment Color</label>
                        <div class="mt-1 flex items-center space-x-3">
                            <input type="color"
                                   id="color"
                                   name="color"
                                   value="{{ old('color', '#3B82F6') }}"
                                   x-model="selectedColor"
                                   class="h-10 w-20 rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <div class="flex-1">
                                <input type="text"
                                       x-model="selectedColor"
                                       placeholder="#3B82F6"
                                       pattern="^#[0-9A-Fa-f]{6}$"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div class="w-8 h-8 rounded-full border-2 border-gray-300" :style="'background-color: ' + selectedColor"></div>
                        </div>
                        @error('color')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Color used to represent this segment in the interface</p>
                    </div>
                </div>

                <!-- Color Presets -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Color Presets</label>
                    <div class="flex space-x-2">
                        <button type="button" @click="selectedColor = '#3B82F6'" class="w-8 h-8 rounded-full border-2 border-gray-300" style="background-color: #3B82F6" title="Blue"></button>
                        <button type="button" @click="selectedColor = '#10B981'" class="w-8 h-8 rounded-full border-2 border-gray-300" style="background-color: #10B981" title="Green"></button>
                        <button type="button" @click="selectedColor = '#8B5CF6'" class="w-8 h-8 rounded-full border-2 border-gray-300" style="background-color: #8B5CF6" title="Purple"></button>
                        <button type="button" @click="selectedColor = '#F59E0B'" class="w-8 h-8 rounded-full border-2 border-gray-300" style="background-color: #F59E0B" title="Orange"></button>
                        <button type="button" @click="selectedColor = '#EF4444'" class="w-8 h-8 rounded-full border-2 border-gray-300" style="background-color: #EF4444" title="Red"></button>
                        <button type="button" @click="selectedColor = '#EC4899'" class="w-8 h-8 rounded-full border-2 border-gray-300" style="background-color: #EC4899" title="Pink"></button>
                        <button type="button" @click="selectedColor = '#84CC16'" class="w-8 h-8 rounded-full border-2 border-gray-300" style="background-color: #84CC16" title="Lime"></button>
                        <button type="button" @click="selectedColor = '#06B6D4'" class="w-8 h-8 rounded-full border-2 border-gray-300" style="background-color: #06B6D4" title="Cyan"></button>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               checked
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-600">Active (available for customer assignment)</span>
                    </label>
                </div>

                <!-- Preview -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Preview</h3>
                    <div class="flex items-center space-x-3">
                        <div class="w-4 h-4 rounded-full" :style="'background-color: ' + selectedColor"></div>
                        <span class="font-medium" x-text="document.getElementById('name').value || 'Customer Segment Name'"></span>
                        <span class="text-sm text-gray-500" x-text="'(' + document.getElementById('default_discount_percentage').value + '% discount)'"></span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('customer-segments.index') }}"
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </a>
                    <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Create Segment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function segmentForm() {
    return {
        selectedColor: '{{ old('color', '#3B82F6') }}'
    }
}
</script>
@endsection