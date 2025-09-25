@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center space-x-4">
            <a href="{{ route('pricing.categories.show', $category) }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Edit Category</h1>
                <p class="mt-1 text-sm text-gray-500">Update the category information</p>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form method="POST" action="{{ route('pricing.categories.update', $category) }}" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Category Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name', $category->name) }}"
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
                          class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('description') border-red-300 @enderror">{{ old('description', $category->description) }}</textarea>
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
                       value="{{ old('code', $category->code) }}"
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
                        <option value="{{ $parentCategory->id }}" {{ old('parent_id', $category->parent_id) == $parentCategory->id ? 'selected' : '' }}>
                            {{ $parentCategory->name }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Select a parent category to create a subcategory, or leave blank for a top-level category.</p>
                @if($category->children->count() > 0)
                    <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Warning:</strong> This category has {{ $category->children->count() }} subcategories.
                                    Changing the parent will affect the category hierarchy.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sort Order -->
            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-700">
                    Sort Order
                </label>
                <input type="number"
                       name="sort_order"
                       id="sort_order"
                       value="{{ old('sort_order', $category->sort_order) }}"
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
                       value="{{ old('icon', $category->icon) }}"
                       maxlength="50"
                       placeholder="e.g. fas fa-tools, fas fa-home"
                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('icon') border-red-300 @enderror">
                @error('icon')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Optional Font Awesome or similar icon class.</p>
                @if($category->icon)
                    <div class="mt-2 flex items-center space-x-2">
                        <span class="text-gray-400">Current icon:</span>
                        <i class="{{ $category->icon }}"></i>
                    </div>
                @endif
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
                           value="{{ old('color', $category->color ?? '#3B82F6') }}"
                           class="h-10 w-20 border border-gray-300 rounded-md cursor-pointer">
                    <input type="text"
                           name="color_text"
                           id="color_text"
                           value="{{ old('color', $category->color ?? '#3B82F6') }}"
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
                           {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Active Category
                    </label>
                </div>
                <p class="mt-1 text-sm text-gray-500">Only active categories will be available for selection when creating items.</p>
                @if($category->items()->count() > 0 && !$category->is_active)
                    <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Note:</strong> This category contains {{ $category->items()->count() }} items.
                                    Deactivating it will make it unavailable for new item assignments.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('pricing.categories.show', $category) }}"
                   class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>
                <button type="submit"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Update Category
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    @can('delete', $category)
        <div class="mt-8 bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 text-red-600">Danger Zone</h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500">
                    <p>Once you delete a category, there is no going back. Please be certain.</p>
                </div>
                <div class="mt-5">
                    @if($category->items()->count() > 0 || $category->children()->count() > 0)
                        <div class="rounded-md bg-red-50 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Cannot delete category</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p>This category cannot be deleted because it contains:</p>
                                        <ul class="list-disc pl-5 space-y-1 mt-2">
                                            @if($category->items()->count() > 0)
                                                <li>{{ $category->items()->count() }} pricing items</li>
                                            @endif
                                            @if($category->children()->count() > 0)
                                                <li>{{ $category->children()->count() }} subcategories</li>
                                            @endif
                                        </ul>
                                        <p class="mt-2">Please move or delete these items first.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('pricing.categories.destroy', $category) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="bg-red-600 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    onclick="return confirm('Are you sure you want to delete this category? This action cannot be undone.')">
                                Delete Category
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endcan
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