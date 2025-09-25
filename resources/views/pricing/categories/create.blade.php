@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center space-x-4">
            <a href="{{ route('pricing.categories.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Create New Category</h1>
                <p class="mt-1 text-sm text-gray-500">Add a new pricing category to organize your items</p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('pricing.categories.store') }}" class="space-y-6 p-6">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Category Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name') }}"
                       required
                       maxlength="100"
                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('name') border-red-300 @enderror">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">
                    Description
                </label>
                <textarea name="description"
                          id="description"
                          rows="3"
                          placeholder="Optional description of this category..."
                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Code -->
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700">
                    Category Code
                </label>
                <input type="text"
                       name="code"
                       id="code"
                       value="{{ old('code') }}"
                       maxlength="20"
                       placeholder="Optional unique code (e.g. CAT001)"
                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('code') border-red-300 @enderror">
                @error('code')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Optional unique identifier for this category.</p>
            </div>

            <!-- Parent Category -->
            <div>
                <label for="parent_id" class="block text-sm font-medium text-gray-700">
                    Parent Category
                </label>
                <select name="parent_id"
                        id="parent_id"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md @error('parent_id') border-red-300 @enderror">
                    <option value="">-- No Parent (Top Level) --</option>
                    @foreach($parentCategories as $parentCategory)
                        <option value="{{ $parentCategory->id }}" {{ old('parent_id') == $parentCategory->id ? 'selected' : '' }}>
                            {{ $parentCategory->name }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Select a parent category to create a subcategory, or leave blank for a top-level category.</p>
            </div>

            <!-- Sort Order -->
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700">
                    Sort Order
                </label>
                <input type="number"
                       name="sort_order"
                       id="sort_order"
                       value="{{ old('sort_order', 0) }}"
                       min="0"
                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('sort_order') border-red-300 @enderror">
                @error('sort_order')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Lower numbers appear first. Leave as 0 for default ordering.</p>
            </div>

            <!-- Icon -->
            <div>
                <label for="icon" class="block text-sm font-medium text-gray-700">
                    Icon Class
                </label>
                <input type="text"
                       name="icon"
                       id="icon"
                       value="{{ old('icon') }}"
                       maxlength="50"
                       placeholder="e.g. fas fa-tools, fas fa-home"
                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('icon') border-red-300 @enderror">
                @error('icon')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Optional Font Awesome or similar icon class.</p>
            </div>

            <!-- Color -->
            <div>
                <label for="color" class="block text-sm font-medium text-gray-700">
                    Category Color
                </label>
                <div class="mt-1 flex items-center space-x-3">
                    <input type="color"
                           name="color"
                           id="color"
                           value="{{ old('color', '#3B82F6') }}"
                           class="h-10 w-20 border border-gray-300 rounded-md cursor-pointer">
                    <input type="text"
                           name="color_text"
                           id="color_text"
                           value="{{ old('color', '#3B82F6') }}"
                           pattern="^#[0-9A-F]{6}$"
                           placeholder="#3B82F6"
                           class="focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('color') border-red-300 @enderror">
                </div>
                @error('color')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Choose a color to identify this category in the interface.</p>
            </div>

            <!-- Status -->
            <div>
                <div class="flex items-center">
                    <input id="is_active"
                           name="is_active"
                           type="checkbox"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Active Category
                    </label>
                </div>
                <p class="mt-1 text-sm text-gray-500">Only active categories will be available for selection when creating items.</p>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('pricing.categories.index') }}"
                   class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create Category
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Sync color picker with text input
    document.getElementById('color').addEventListener('input', function() {
        document.getElementById('color_text').value = this.value.toUpperCase();
        document.querySelector('input[name="color"]').value = this.value.toUpperCase();
    });

    document.getElementById('color_text').addEventListener('input', function() {
        if (/^#[0-9A-F]{6}$/i.test(this.value)) {
            document.getElementById('color').value = this.value;
            document.querySelector('input[name="color"]').value = this.value.toUpperCase();
        }
    });
</script>
@endsection