@extends('layouts.app')

@section('title', 'Logo Bank')

@section('content')
<div x-data="logoBank()">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200 px-6 py-4 -mt-12">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Logo Bank') }}
            </h2>
            <button @click="showUploadModal = true"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                {{ __('Upload New Logo') }}
            </button>
        </div>
    </div>

    <!-- Content Section -->
    <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                @foreach($errors->all() as $error)
                    <span class="block sm:inline">{{ $error }}</span>
                @endforeach
            </div>
        @endif

        <!-- Logo Grid -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                @if($logos->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No logos</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by uploading your first logo.</p>
                        <div class="mt-6">
                            <button @click="showUploadModal = true" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Upload Logo
                            </button>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($logos as $logo)
                            <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow relative group">
                                <!-- Default Badge -->
                                @if($logo->is_default)
                                    <div class="absolute top-2 right-2 z-10">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Default
                                        </span>
                                    </div>
                                @endif

                                <!-- Logo Image -->
                                <div class="aspect-w-16 aspect-h-9 bg-gray-100 flex items-center justify-center p-4">
                                    <img src="{{ route('logo-bank.serve', $logo->id) }}?v={{ $logo->updated_at->timestamp }}"
                                         alt="{{ $logo->name }}"
                                         class="max-h-32 object-contain">
                                </div>

                                <!-- Logo Info -->
                                <div class="p-4">
                                    <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ $logo->name }}</h3>
                                    @if($logo->notes)
                                        <p class="text-xs text-gray-500 mb-3">{{ Str::limit($logo->notes, 60) }}</p>
                                    @endif

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2">
                                        @if(!$logo->is_default)
                                            <form action="{{ route('logo-bank.set-default', $logo) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                                    Set as Default
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('logo-bank.destroy', $logo) }}"
                                              method="POST"
                                              class="inline ml-auto"
                                              onsubmit="return confirm('Are you sure you want to delete this logo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div x-show="showUploadModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title"
         role="dialog"
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showUploadModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 aria-hidden="true"
                 @click="showUploadModal = false"></div>

            <!-- Modal panel -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showUploadModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                <form action="{{ route('logo-bank.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                    Upload New Logo
                                </h3>

                                <div class="space-y-4">
                                    <!-- Logo Name -->
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Logo Name</label>
                                        <input type="text"
                                               name="name"
                                               id="name"
                                               required
                                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                               placeholder="e.g., Primary Logo, Event Logo">
                                    </div>

                                    <!-- Logo File -->
                                    <div>
                                        <label for="logo" class="block text-sm font-medium text-gray-700">Logo File</label>
                                        <input type="file"
                                               name="logo"
                                               id="logo"
                                               required
                                               accept="image/*"
                                               class="mt-1 block w-full text-sm text-gray-500
                                                      file:mr-4 file:py-2 file:px-4
                                                      file:rounded-md file:border-0
                                                      file:text-sm file:font-medium
                                                      file:bg-blue-50 file:text-blue-700
                                                      hover:file:bg-blue-100">
                                        <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF or SVG (max 2MB)</p>
                                    </div>

                                    <!-- Notes -->
                                    <div>
                                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                                        <textarea name="notes"
                                                  id="notes"
                                                  rows="2"
                                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                  placeholder="When to use this logo..."></textarea>
                                    </div>

                                    <!-- Set as Default -->
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                               name="set_as_default"
                                               id="set_as_default"
                                               value="1"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="set_as_default" class="ml-2 block text-sm text-gray-900">
                                            Set as default logo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Upload Logo
                        </button>
                        <button type="button"
                                @click="showUploadModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div> <!-- Close py-12 div -->
</div> <!-- Close x-data div -->

<script>
function logoBank() {
    return {
        showUploadModal: false,
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
