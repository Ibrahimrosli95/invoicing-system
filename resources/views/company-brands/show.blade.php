@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    @if($companyBrand->logo_path)
                        <img src="{{ $companyBrand->getLogoUrl() }}" alt="{{ $companyBrand->name }}" class="h-16 w-auto object-contain">
                    @endif
                    <div>
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                            {{ $companyBrand->name }}
                        </h2>
                        @if($companyBrand->legal_name)
                            <p class="mt-1 text-sm text-gray-500">{{ $companyBrand->legal_name }}</p>
                        @endif
                        <div class="mt-2 flex items-center space-x-2">
                            @if($companyBrand->is_default)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Default Brand
                                </span>
                            @endif
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $companyBrand->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $companyBrand->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('settings.brands.edit', $companyBrand) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                    <a href="{{ route('settings.brands.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Information --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Contact Information --}}
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Contact Information</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            @if($companyBrand->registration_number)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Registration Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $companyBrand->registration_number }}</dd>
                                </div>
                            @endif

                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Address</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $companyBrand->address }}<br>
                                    {{ $companyBrand->postal_code }} {{ $companyBrand->city }}, {{ $companyBrand->state }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $companyBrand->phone }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a href="mailto:{{ $companyBrand->email }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $companyBrand->email }}
                                    </a>
                                </dd>
                            </div>

                            @if($companyBrand->website)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Website</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="{{ $companyBrand->website }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                            {{ $companyBrand->website }}
                                        </a>
                                    </dd>
                                </div>
                            @endif

                            @if($companyBrand->tagline)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Tagline</dt>
                                    <dd class="mt-1 text-sm text-gray-900 italic">{{ $companyBrand->tagline }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- Bank Details --}}
                @if($companyBrand->hasOwnBankDetails())
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Bank Details</h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Bank Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $companyBrand->bank_name }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Account Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $companyBrand->bank_account_name }}</dd>
                                </div>

                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Account Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $companyBrand->bank_account_number }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                @endif

                {{-- Brand Colors --}}
                @if($companyBrand->color_primary || $companyBrand->color_secondary)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Brand Colors</h3>
                        </div>
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex items-center space-x-6">
                                @if($companyBrand->color_primary)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 mb-2">Primary Color</dt>
                                        <div class="flex items-center space-x-3">
                                            <div class="h-10 w-10 rounded border border-gray-300" style="background-color: {{ $companyBrand->color_primary }}"></div>
                                            <span class="text-sm text-gray-900">{{ $companyBrand->color_primary }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if($companyBrand->color_secondary)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 mb-2">Secondary Color</dt>
                                        <div class="flex items-center space-x-3">
                                            <div class="h-10 w-10 rounded border border-gray-300" style="background-color: {{ $companyBrand->color_secondary }}"></div>
                                            <span class="text-sm text-gray-900">{{ $companyBrand->color_secondary }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Usage Statistics --}}
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Usage Statistics</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Quotations</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $companyBrand->quotations_count }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Invoices</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $companyBrand->invoices_count }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Documents</dt>
                                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $companyBrand->getDocumentCount() }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6 space-y-3">
                        @unless($companyBrand->is_default)
                            <form action="{{ route('settings.brands.set-default', $companyBrand) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Set as Default
                                </button>
                            </form>
                        @endunless

                        <form action="{{ route('settings.brands.toggle-status', $companyBrand) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                {{ $companyBrand->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        @if($companyBrand->quotations_count == 0 && $companyBrand->invoices_count == 0)
                            <form action="{{ route('settings.brands.destroy', $companyBrand) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this brand?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                                    Delete Brand
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
