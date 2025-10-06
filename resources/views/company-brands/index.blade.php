@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Company Brands
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Manage different trading names and letterheads for your company
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('settings.brands.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Brand
                </a>
            </div>
        </div>

        {{-- Success Message --}}
        @if (session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md flex items-center">
                <svg class="h-5 w-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Error Messages --}}
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Brands Grid --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($brands as $brand)
                <div class="bg-white overflow-hidden shadow rounded-lg border {{ $brand->is_default ? 'border-blue-300 ring-2 ring-blue-100' : 'border-gray-200' }}">
                    <div class="p-5">
                        {{-- Logo and Name --}}
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-3">
                                @if($brand->logo_path)
                                    <img src="{{ $brand->getLogoUrl() }}" alt="{{ $brand->name }}" class="h-12 w-auto object-contain">
                                @else
                                    <div class="h-12 w-12 rounded bg-gray-100 flex items-center justify-center">
                                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">{{ $brand->name }}</h3>
                                    @if($brand->legal_name)
                                        <p class="text-sm text-gray-500">{{ $brand->legal_name }}</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Status Badges --}}
                            <div class="flex flex-col items-end space-y-1">
                                @if($brand->is_default)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Default
                                    </span>
                                @endif
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $brand->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $brand->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        {{-- Contact Info --}}
                        <div class="mt-4 space-y-2">
                            <p class="text-sm text-gray-600">
                                {{ $brand->address }}<br>
                                {{ $brand->postal_code }} {{ $brand->city }}, {{ $brand->state }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Phone:</span> {{ $brand->phone }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Email:</span> {{ $brand->email }}
                            </p>
                        </div>

                        {{-- Usage Stats --}}
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>{{ $brand->quotations_count }} Quotations</span>
                                <span>{{ $brand->invoices_count }} Invoices</span>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="mt-4 flex items-center justify-between">
                            <div class="flex space-x-2">
                                <a href="{{ route('settings.brands.show', $brand) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                    View
                                </a>
                                <a href="{{ route('settings.brands.edit', $brand) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                    Edit
                                </a>
                            </div>

                            <div class="flex space-x-2">
                                @unless($brand->is_default)
                                    <form action="{{ route('settings.brands.set-default', $brand) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-sm text-gray-600 hover:text-gray-800">
                                            Set Default
                                        </button>
                                    </form>
                                @endunless

                                <form action="{{ route('settings.brands.toggle-status', $brand) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm text-gray-600 hover:text-gray-800">
                                        {{ $brand->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                                @if($brand->quotations_count == 0 && $brand->invoices_count == 0)
                                    <form action="{{ route('settings.brands.destroy', $brand) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this brand?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:text-red-800">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No brands</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new brand.</p>
                    <div class="mt-6">
                        <a href="{{ route('settings.brands.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            New Brand
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
