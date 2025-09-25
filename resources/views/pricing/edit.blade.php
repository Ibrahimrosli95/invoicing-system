@extends('layouts.app')

@section('title', 'Edit Pricing Item')

@section('header')
Edit: {{ $pricingItem->name }}
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('pricing.update', $pricingItem) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')

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
                                                      :value="old('name', $pricingItem->name)"
                                                      required />
                                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                    </div>

                                    <div>
                                        <x-input-label for="description" :value="__('Description')" />
                                        <textarea id="description"
                                                  name="description"
                                                  rows="3"
                                                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $pricingItem->description) }}</textarea>
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
                                                        {{ old('pricing_category_id', $pricingItem->pricing_category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error class="mt-2" :messages="$errors->get('pricing_category_id')" />
                                    </div>


                                    <div>
                                        <x-input-label for="item_code" :value="__('Item Code')" />
                                        <x-text-input id="item_code"
                                                      name="item_code"
                                                      type="text"
                                                      class="mt-1 block w-full"
                                                      :value="old('item_code', $pricingItem->item_code)" />
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
                                                      :value="old('cost_price', $pricingItem->cost_price)" />
                                        <x-input-error class="mt-2" :messages="$errors->get('cost_price')" />
                                    </div>

                                    <div>
                                        <x-input-label for="unit_price" :value="__('Unit Price (RM)')" />
                                        <x-text-input id="unit_price"
                                                      name="unit_price"
                                                      type="number"
                                                      step="0.01"
                                                      min="0"
                                                      class="mt-1 block w-full"
                                                      :value="old('unit_price', $pricingItem->unit_price)"
                                                      required />
                                        <x-input-error class="mt-2" :messages="$errors->get('unit_price')" />
                                    </div>

                                    <div>
                                        <x-input-label for="minimum_price" :value="__('Minimum Price (RM)')" />
                                        <x-text-input id="minimum_price"
                                                      name="minimum_price"
                                                      type="number"
                                                      step="0.01"
                                                      min="0"
                                                      class="mt-1 block w-full"
                                                      :value="old('minimum_price', $pricingItem->minimum_price)" />
                                        <x-input-error class="mt-2" :messages="$errors->get('minimum_price')" />
                                    </div>

                                    <div>
                                        <x-input-label for="markup_percentage" :value="__('Markup Percentage (%)')" />
                                        <x-text-input id="markup_percentage"
                                                      name="markup_percentage"
                                                      type="number"
                                                      step="0.01"
                                                      min="0"
                                                      max="1000"
                                                      class="mt-1 block w-full"
                                                      :value="old('markup_percentage', $pricingItem->markup_percentage)" />
                                        <x-input-error class="mt-2" :messages="$errors->get('markup_percentage')" />
                                        <p class="mt-1 text-sm text-gray-500">Percentage markup over cost price.</p>
                                    </div>
                                </div>

                                <!-- Current Margin Display -->
                                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                    <p class="text-sm text-gray-600">
                                        Current margin: <span class="font-semibold">{{ number_format($pricingItem->getMarginPercentage(), 2) }}%</span>
                                        | Profit: <span class="font-semibold text-green-600">RM {{ number_format($pricingItem->getProfit(), 2) }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-6">
                            <!-- Current Image & Upload -->
                            <div class="border-b border-gray-200 pb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Item Image</h3>

                                @if($pricingItem->image_path)
                                    <div class="mb-4">
                                        <img src="{{ Storage::url($pricingItem->image_path) }}"
                                             alt="{{ $pricingItem->name }}"
                                             class="w-32 h-32 object-cover rounded-lg">
                                        <p class="text-sm text-gray-500 mt-2">Current image</p>
                                    </div>
                                @endif

                                <div>
                                    <x-input-label for="image" :value="__('Upload New Image')" />
                                    <input id="image"
                                           name="image"
                                           type="file"
                                           accept="image/*"
                                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                    <p class="mt-2 text-sm text-gray-600">PNG, JPG, GIF up to 10MB. Leave empty to keep current image.</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('image')" />
                                </div>
                            </div>


                            <!-- Additional Information -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>

                                <div class="space-y-4">

                                        <label class="flex items-center">
                                            <input type="checkbox"
                                                   name="is_active"
                                                   value="1"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                                   {{ old('is_active', $pricingItem->is_active) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-600">Active (available for use)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end mt-8 space-x-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('pricing.show', $pricingItem) }}"
                           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Cancel
                        </a>
                        <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Update Pricing Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection