<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ $proof->title }}
                    </h2>
                    <div class="flex items-center space-x-3 mt-1">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $proof->type_label }}
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $proof->getStatusColor() }}">
                            {{ $proof->status_label }}
                        </span>
                        @if($proof->is_featured)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Featured
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <!-- Quick Actions -->
                <div class="flex items-center space-x-2" x-data="{ showActions: false }">
                    <button @click="showActions = !showActions" 
                            class="relative bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                        Actions
                        <svg class="w-4 h-4 ml-1 inline" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div x-show="showActions" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         @click.outside="showActions = false"
                         class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                        <div class="py-1">
                            <a href="{{ route('proofs.duplicate', $proof->uuid) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                Duplicate
                            </a>
                            <form method="POST" action="{{ route('proofs.toggle-featured', $proof->uuid) }}" class="inline">
                                @csrf
                                <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-2 inline" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    {{ $proof->is_featured ? 'Remove from Featured' : 'Mark as Featured' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('proofs.toggle-status', $proof->uuid) }}" class="inline">
                                @csrf
                                <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                    </svg>
                                    {{ $proof->status === 'active' ? 'Archive' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('proofs.edit', $proof->uuid) }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Edit
                </a>
                <a href="{{ route('proofs.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Back to Proofs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Description -->
                    @if($proof->description)
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Description</h3>
                                <div class="prose prose-sm text-gray-700">
                                    {{ $proof->description }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Assets Gallery -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Assets ({{ $proof->assets->count() }})</h3>
                                <button type="button" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm font-medium"
                                        onclick="document.getElementById('upload-modal').classList.remove('hidden')">
                                    <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add Assets
                                </button>
                            </div>

                            @if($proof->assets->count() > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" x-data="assetGallery()">
                                    @foreach($proof->assets as $asset)
                                        <div class="relative group bg-gray-50 rounded-lg overflow-hidden border border-gray-200 hover:shadow-md transition-shadow">
                                            <!-- Asset Preview -->
                                            <div class="aspect-video bg-gray-100 relative">
                                                @if($asset->isImage())
                                                    <img src="{{ asset('storage/' . ($asset->thumbnail_path ?: $asset->file_path)) }}" 
                                                         alt="{{ $asset->alt_text }}"
                                                         class="w-full h-full object-cover cursor-pointer"
                                                         @click="openModal('{{ asset('storage/' . $asset->file_path) }}', '{{ $asset->title }}', '{{ $asset->type }}')">
                                                @elseif($asset->isVideo())
                                                    <div class="w-full h-full flex items-center justify-center bg-gray-200 cursor-pointer"
                                                         @click="openModal('{{ asset('storage/' . $asset->file_path) }}', '{{ $asset->title }}', '{{ $asset->type }}')">
                                                        @if($asset->thumbnail_path)
                                                            <img src="{{ asset('storage/' . $asset->thumbnail_path) }}" 
                                                                 alt="Video thumbnail"
                                                                 class="w-full h-full object-cover">
                                                        @else
                                                            <div class="text-center">
                                                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M2 6a2 2 0 012-2h6l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM8 5.5a.5.5 0 01.5-.5h3a.5.5 0 01.5.5v1a.5.5 0 01-.5.5h-3a.5.5 0 01-.5-.5v-1z"/>
                                                                </svg>
                                                                <p class="text-sm text-gray-500">Video</p>
                                                            </div>
                                                        @endif
                                                        <!-- Play button overlay -->
                                                        <div class="absolute inset-0 flex items-center justify-center">
                                                            <div class="w-12 h-12 bg-black bg-opacity-50 rounded-full flex items-center justify-center">
                                                                <svg class="w-6 h-6 text-white ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M8 5v10l8-5-8-5z"/>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                        <div class="text-center">
                                                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                                            </svg>
                                                            <p class="text-sm text-gray-500">{{ $asset->getHumanReadableSize() }}</p>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                <!-- Primary Asset Badge -->
                                                @if($asset->is_primary)
                                                    <div class="absolute top-2 left-2">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            Primary
                                                        </span>
                                                    </div>
                                                @endif

                                                <!-- Processing Status -->
                                                @if($asset->processing_status !== 'completed')
                                                    <div class="absolute top-2 right-2">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $asset->processing_status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                            {{ ucfirst($asset->processing_status) }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Asset Info -->
                                            <div class="p-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $asset->type === 'image' ? 'bg-green-100 text-green-800' : ($asset->type === 'video' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800') }}">
                                                        {{ ucfirst($asset->type) }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">{{ $asset->getHumanReadableSize() }}</span>
                                                </div>
                                                
                                                @if($asset->title)
                                                    <h4 class="text-sm font-medium text-gray-900 mb-1 truncate">{{ $asset->title }}</h4>
                                                @endif
                                                
                                                <p class="text-xs text-gray-600 mb-2 truncate">{{ $asset->original_filename }}</p>
                                                
                                                @if($asset->width && $asset->height)
                                                    <p class="text-xs text-gray-500">{{ $asset->width }} × {{ $asset->height }}px</p>
                                                @endif
                                            </div>

                                            <!-- Actions Overlay -->
                                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-200">
                                                <div class="absolute bottom-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                    <div class="flex space-x-1">
                                                        <button type="button" 
                                                                class="p-1 bg-white rounded text-gray-600 hover:text-gray-800"
                                                                onclick="window.open('{{ asset('storage/' . $asset->file_path) }}', '_blank')"
                                                                title="Download">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-4-4m4 4l4-4m-4-4V3"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Modal for Asset Preview -->
                                    <div x-show="showModal" 
                                         x-transition:enter="ease-out duration-300"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         x-transition:leave="ease-in duration-200"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0"
                                         class="fixed inset-0 z-50 overflow-y-auto" 
                                         style="display: none;">
                                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                            <div class="fixed inset-0 transition-opacity" @click="closeModal()">
                                                <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
                                            </div>

                                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                    <div class="flex justify-between items-center mb-4">
                                                        <h3 class="text-lg font-medium text-gray-900" x-text="modalTitle"></h3>
                                                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div class="text-center">
                                                        <template x-if="modalType.startsWith('image')">
                                                            <img :src="modalSrc" :alt="modalTitle" class="max-w-full max-h-96 mx-auto rounded">
                                                        </template>
                                                        <template x-if="modalType.startsWith('video')">
                                                            <video :src="modalSrc" controls class="max-w-full max-h-96 mx-auto rounded">
                                                                Your browser does not support the video tag.
                                                            </video>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No assets</h3>
                                    <p class="mt-1 text-sm text-gray-500">Upload images, videos, or documents to showcase this proof.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Analytics -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Analytics</h3>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                <div class="text-center">
                                    <div class="text-2xl font-semibold text-blue-600">{{ number_format($proof->views_count) }}</div>
                                    <div class="text-sm text-gray-500">Total Views</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-semibold text-green-600">{{ $proof->assets->count() }}</div>
                                    <div class="text-sm text-gray-500">Assets</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-semibold text-purple-600">{{ $proof->conversion_impact ?? 0 }}%</div>
                                    <div class="text-sm text-gray-500">Impact</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-semibold text-orange-600">{{ $proof->created_at->diffInDays() }}</div>
                                    <div class="text-sm text-gray-500">Days Old</div>
                                </div>
                            </div>

                            @if($recentViews->count() > 0)
                                <div class="border-t pt-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Views</h4>
                                    <div class="space-y-2">
                                        @foreach($recentViews as $view)
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-gray-600">{{ $view->created_at->format('M j, Y g:i A') }}</span>
                                                <span class="text-gray-500">{{ $view->ip_address }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Proof Details -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Details</h3>
                            <div class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $proof->getStatusColor() }}">
                                            {{ $proof->status_label }}
                                        </span>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Visibility</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $proof->visibility_label }}</dd>
                                </div>
                                
                                @if($proof->expires_at)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Expires</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $proof->expires_at->format('M j, Y') }}</dd>
                                    </div>
                                @endif
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $proof->sort_order }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $proof->created_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Updated</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $proof->updated_at->format('M j, Y g:i A') }}</dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Display Settings -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Display Settings</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">Show in PDFs</span>
                                    <span class="inline-flex items-center">
                                        @if($proof->show_in_pdf)
                                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">Show in Quotations</span>
                                    <span class="inline-flex items-center">
                                        @if($proof->show_in_quotation)
                                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">Show in Invoices</span>
                                    <span class="inline-flex items-center">
                                        @if($proof->show_in_invoice)
                                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Linked Content -->
                    @if($proof->scope_type && $proof->scope_id)
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Linked To</h3>
                                <div class="space-y-2">
                                    @if($proof->scope)
                                        <div class="flex items-center text-sm">
                                            <span class="font-medium text-gray-900">{{ class_basename($proof->scope_type) }}:</span>
                                            <span class="ml-2 text-blue-600">
                                                @if($proof->scope_type === 'App\Models\Quotation')
                                                    <a href="{{ route('quotations.show', $proof->scope->id) }}">{{ $proof->scope->number }}</a>
                                                @elseif($proof->scope_type === 'App\Models\Invoice')
                                                    <a href="{{ route('invoices.show', $proof->scope->id) }}">{{ $proof->scope->number }}</a>
                                                @elseif($proof->scope_type === 'App\Models\Lead')
                                                    <a href="{{ route('leads.show', $proof->scope->id) }}">{{ $proof->scope->name }}</a>
                                                @else
                                                    {{ $proof->scope->title ?? $proof->scope->name ?? 'Unknown' }}
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="upload-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" onclick="document.getElementById('upload-modal').classList.add('hidden')">
                <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('proofs.upload-assets', $proof->uuid) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Additional Assets</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="upload-assets" class="block text-sm font-medium text-gray-700">Select Files</label>
                                <input type="file" id="upload-assets" name="assets[]" multiple 
                                       accept="image/*,video/*,.pdf,.doc,.docx"
                                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Upload
                        </button>
                        <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function assetGallery() {
            return {
                showModal: false,
                modalSrc: '',
                modalTitle: '',
                modalType: '',

                openModal(src, title, type) {
                    this.modalSrc = src;
                    this.modalTitle = title || 'Asset Preview';
                    this.modalType = type || 'image';
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    this.modalSrc = '';
                    this.modalTitle = '';
                    this.modalType = '';
                }
            }
        }
    </script>
    @endpush
</x-app-layout>