@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                        Edit Brand
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Update brand information and settings
                    </p>
                </div>
                <a href="{{ route('settings.brands.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Brands
                </a>
            </div>
        </div>

        {{-- Form --}}
        <form action="{{ route('settings.brands.update', $companyBrand) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            @include('company-brands._form', ['brand' => $companyBrand])

            {{-- Submit Buttons --}}
            <div class="flex items-center justify-between">
                <div>
                    @if($companyBrand->quotations_count == 0 && $companyBrand->invoices_count == 0)
                        <button type="button" onclick="if(confirm('Are you sure you want to delete this brand?')) { document.getElementById('delete-form').submit(); }" class="px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                            Delete Brand
                        </button>
                    @endif
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('settings.brands.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Brand
                    </button>
                </div>
            </div>
        </form>

        {{-- Delete Form (Hidden) --}}
        @if($companyBrand->quotations_count == 0 && $companyBrand->invoices_count == 0)
            <form id="delete-form" action="{{ route('settings.brands.destroy', $companyBrand) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>
@endsection
