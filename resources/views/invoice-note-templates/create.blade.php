@extends('layouts.app')

@section('title', 'Create Template')

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
                <h1 class="text-2xl font-bold text-gray-900">Create New Template</h1>
                <p class="text-gray-600 mt-1">Create a reusable template for notes, terms, or payment instructions</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow">
        <form method="POST" action="{{ route('invoice-note-templates.store') }}" class="space-y-6 p-6">
            @csrf

            <!-- Template Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Template Name</label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
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
                        <option value="{{ $key }}" {{ old('type', request('type')) === $key ? 'selected' : '' }}>
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
                          placeholder="Enter the template content...">{{ old('content') }}</textarea>
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
                               {{ old('is_default') ? 'checked' : '' }}
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
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 text-sm text-gray-700">
                            Active template
                        </label>
                    </div>
                    <p class="text-sm text-gray-500 ml-6">Only active templates are available for selection.</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('invoice-note-templates.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Create Template
                </button>
            </div>
        </form>
    </div>

    <!-- Example Templates -->
    <div class="mt-8 bg-blue-50 rounded-lg p-6">
        <h3 class="text-lg font-medium text-blue-900 mb-4">Example Templates</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Notes Example -->
            <div>
                <h4 class="font-medium text-blue-800 mb-2">Notes Example</h4>
                <div class="bg-white rounded-md p-3 text-sm text-gray-700">
                    "Thank you for your business! We appreciate your continued trust in our services and look forward to working with you again."
                </div>
            </div>

            <!-- Terms Example -->
            <div>
                <h4 class="font-medium text-blue-800 mb-2">Terms & Conditions Example</h4>
                <div class="bg-white rounded-md p-3 text-sm text-gray-700">
                    "Payment is due within 30 days from invoice date. Late payments may incur additional charges. All work is guaranteed for 12 months."
                </div>
            </div>

            <!-- Payment Instructions Example -->
            <div>
                <h4 class="font-medium text-blue-800 mb-2">Payment Instructions Example</h4>
                <div class="bg-white rounded-md p-3 text-sm text-gray-700">
                    "Please make payments to:<br><br>Bank: Maybank<br>Account: 1234567890<br>Company: Your Company Name<br><br>Please include invoice number in payment reference."
                </div>
            </div>
        </div>
    </div>
</div>
@endsection