@extends('layouts.app')

@section('title', 'Add New Pricing Item')

@section('header')
Add New Pricing Item
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('pricing.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-6">
                            <!-- Basic Information -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>

                                <div class="space-y-4">
                                    <div>
                                        <x-input-label for="name" :value="__('Item Name')" />
                                        <x-text-input id="name"
                                                      name="name"
                                                      type="text"
                                                      class="mt-1 block w-full"
                                                      :value="old('name')"
                                                      required />
                                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                    </div>

                                    <div>
                                        <x-input-label for="description" :value="__('Description')" />
                                        <textarea id="description"
                                                  name="description"
                                                  rows="3"
                                                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                                    </div>

                                    <div>
                                        <x-input-label for="pricing_category_id" :value="__('Category')" />
                                        <select id="pricing_category_id"
                                                name="pricing_category_id"
                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                required>
                                            <option value="">Select Category</option>
                                            @foreach($categories ?? [] as $category)
                                                <option value="{{ $category->id }}"
                                                        {{ old('pricing_category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error class="mt-2" :messages="$errors->get('pricing_category_id')" />
                                    </div>

                                    <div>
                                        <x-input-label for="unit" :value="__('Unit')" />
                                        <x-text-input id="unit"
                                                      name="unit"
                                                      type="text"
                                                      class="mt-1 block w-full"
                                                      :value="old('unit', 'pcs')"
                                                      required />
                                        <x-input-error class="mt-2" :messages="$errors->get('unit')" />
                                    </div>

                                    <div>
                                        <x-input-label for="item_code" :value="__('Item Code')" />
                                        <x-text-input id="item_code"
                                                      name="item_code"
                                                      type="text"
                                                      class="mt-1 block w-full"
                                                      :value="old('item_code')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('item_code')" />
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing Information -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Pricing Information</h3>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="cost_price" :value="__('Cost Price (RM)')" />
                                        <x-text-input id="cost_price"
                                                      name="cost_price"
                                                      type="number"
                                                      step="0.01"
                                                      min="0"
                                                      class="mt-1 block w-full"
                                                      :value="old('cost_price', '0.00')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('cost_price')" />
                                    </div>

                                    <div>
                                        <x-input-label for="selling_price" :value="__('Selling Price (RM)')" />
                                        <x-text-input id="selling_price"
                                                      name="selling_price"
                                                      type="number"
                                                      step="0.01"
                                                      min="0"
                                                      class="mt-1 block w-full"
                                                      :value="old('selling_price', '0.00')"
                                                      required />
                                        <x-input-error class="mt-2" :messages="$errors->get('selling_price')" />
                                    </div>

                                    <div>
                                        <x-input-label for="minimum_price" :value="__('Minimum Price (RM)')" />
                                        <x-text-input id="minimum_price"
                                                      name="minimum_price"
                                                      type="number"
                                                      step="0.01"
                                                      min="0"
                                                      class="mt-1 block w-full"
                                                      :value="old('minimum_price', '0.00')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('minimum_price')" />
                                    </div>

                                    <div>
                                        <x-input-label for="target_margin_percentage" :value="__('Target Margin %')" />
                                        <x-text-input id="target_margin_percentage"
                                                      name="target_margin_percentage"
                                                      type="number"
                                                      step="0.01"
                                                      min="0"
                                                      max="100"
                                                      class="mt-1 block w-full"
                                                      :value="old('target_margin_percentage', '20.00')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('target_margin_percentage')" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Image Upload -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Item Image</h3>

                                <div>
                                    <x-input-label for="image" :value="__('Upload Image')" />
                                    <input id="image"
                                           name="image"
                                           type="file"
                                           accept="image/*"
                                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                    <p class="mt-2 text-sm text-gray-600">PNG, JPG, GIF up to 10MB</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('image')" />
                                </div>
                            </div>

                            <!-- Stock Information -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Stock Information</h3>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="stock_quantity" :value="__('Stock Quantity')" />
                                        <x-text-input id="stock_quantity"
                                                      name="stock_quantity"
                                                      type="number"
                                                      min="0"
                                                      class="mt-1 block w-full"
                                                      :value="old('stock_quantity', '0')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('stock_quantity')" />
                                    </div>

                                    <div>
                                        <x-input-label for="minimum_stock" :value="__('Minimum Stock Level')" />
                                        <x-text-input id="minimum_stock"
                                                      name="minimum_stock"
                                                      type="number"
                                                      min="0"
                                                      class="mt-1 block w-full"
                                                      :value="old('minimum_stock', '10')" />
                                        <x-input-error class="mt-2" :messages="$errors->get('minimum_stock')" />
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="flex items-center">
                                        <input type="checkbox"
                                               name="track_stock"
                                               value="1"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                               {{ old('track_stock') ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-600">Track stock levels for this item</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>

                                <div class="space-y-4">
                                    <div>
                                        <x-input-label for="specifications" :value="__('Specifications')" />
                                        <textarea id="specifications"
                                                  name="specifications"
                                                  rows="3"
                                                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('specifications') }}</textarea>
                                        <x-input-error class="mt-2" :messages="$errors->get('specifications')" />
                                    </div>

                                    <div>
                                        <x-input-label for="tags" :value="__('Tags (comma separated)')" />
                                        <x-text-input id="tags"
                                                      name="tags"
                                                      type="text"
                                                      class="mt-1 block w-full"
                                                      :value="old('tags')"
                                                      placeholder="building, construction, tools" />
                                        <x-input-error class="mt-2" :messages="$errors->get('tags')" />
                                    </div>

                                    <div class="space-y-3">
                                        <label class="flex items-center">
                                            <input type="checkbox"
                                                   name="is_featured"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                                   {{ old('is_featured') ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-600">Feature this item</span>
                                        </label>

                                        <label class="flex items-center">
                                            <input type="checkbox"
                                                   name="is_active"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                                   {{ old('is_active', true) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-600">Active (available for use)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end mt-8 space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('pricing.index') }}"
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create Pricing Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection