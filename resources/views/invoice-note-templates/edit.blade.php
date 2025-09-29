@extends('layouts.app')

@section('title', 'Edit Template')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-4">
            <a href="{{ route('invoice-note-templates.index') }}"
               class="flex items-center text-gray-600 hover:text-gray-900">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Template</h1>
                <p class="text-gray-600 mt-1">Update {{ $invoiceNoteTemplate->name }}</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('invoice-note-templates.update', $invoiceNoteTemplate) }}" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            <!-- Template Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Template Name</label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $invoiceNoteTemplate->name) }}"
                       required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                       placeholder="e.g., Standard Thank You, Payment Reminder">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Template Type -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Template Type</label>
                <select id="type"
                        name="type"
                        required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-500 @enderror">
                    <option value="">Select template type...</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" {{ old('type', $invoiceNoteTemplate->type) === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Choose the type of content this template will contain.</p>
            </div>

            <!-- Template Content -->
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Template Content</label>
                <textarea id="content"
                          name="content"
                          rows="8"
                          required
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('content') border-red-500 @enderror"
                          placeholder="Enter the template content...">{{ old('content', $invoiceNoteTemplate->content) }}</textarea>
                @error('content')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">This content will be used when the template is applied to invoices.</p>
            </div>

            <!-- Template Settings -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Template Settings</h3>

                <div class="space-y-4">
                    <!-- Set as Default -->
                    <div class="flex items-center">
                        <input type="checkbox"
                               id="is_default"
                               name="is_default"
                               value="1"
                               {{ old('is_default', $invoiceNoteTemplate->is_default) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_default" class="ml-2 text-sm text-gray-700">
                            Set as default template for this type
                        </label>
                    </div>
                    <p class="text-sm text-gray-500 ml-6">Default templates are automatically applied when creating new invoices.</p>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <input type="checkbox"
                               id="is_active"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $invoiceNoteTemplate->is_active) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">
                            Active template
                        </label>
                    </div>
                    <p class="text-sm text-gray-500 ml-6">Only active templates are available for selection.</p>
                </div>
            </div>

            <!-- Template Info -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Template Information</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">Created:</span> {{ $invoiceNoteTemplate->created_at->format('M j, Y g:i A') }}
                    </div>
                    <div>
                        <span class="font-medium">Last Modified:</span> {{ $invoiceNoteTemplate->updated_at->format('M j, Y g:i A') }}
                    </div>
                    <div>
                        <span class="font-medium">Type:</span> {{ $invoiceNoteTemplate->getTypeDisplayName() }}
                    </div>
                    <div>
                        <span class="font-medium">Status:</span>
                        @if($invoiceNoteTemplate->is_default)
                            <span class="text-blue-600">Default</span>
                        @elseif($invoiceNoteTemplate->is_active)
                            <span class="text-green-600">Active</span>
                        @else
                            <span class="text-gray-500">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('invoice-note-templates.index') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </a>
                    <a href="{{ route('invoice-note-templates.show', $invoiceNoteTemplate) }}"
                       class="px-4 py-2 text-sm font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        View Template
                    </a>
                </div>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Update Template
                </button>
            </div>
        </form>
    </div>
</div>
@endsection