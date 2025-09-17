<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Proof: {{ $proof->title }}
                </h2>
                <div class="flex items-center space-x-3 mt-1">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $proof->type_label }}
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $proof->getStatusColor() }}">
                        {{ $proof->status_label }}
                    </span>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('proofs.show', $proof->uuid) }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    View Proof
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
            <form action="{{ route('proofs.update', $proof->uuid) }}" method="POST" class="space-y-8" x-data="proofEditForm()">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Form -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Basic Information -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-6">Basic Information</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Proof Type -->
                                    <div>
                                        <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                                        <select name="type" id="type" x-model="formData.type"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Select type...</option>
                                            <option value="testimonial" {{ old('type', $proof->type) === 'testimonial' ? 'selected' : '' }}>Customer Testimonial</option>
                                            <option value="case_study" {{ old('type', $proof->type) === 'case_study' ? 'selected' : '' }}>Case Study</option>
                                            <option value="certification" {{ old('type', $proof->type) === 'certification' ? 'selected' : '' }}>Certification</option>
                                            <option value="award" {{ old('type', $proof->type) === 'award' ? 'selected' : '' }}>Award</option>
                                            <option value="media_coverage" {{ old('type', $proof->type) === 'media_coverage' ? 'selected' : '' }}>Media Coverage</option>
                                            <option value="client_logo" {{ old('type', $proof->type) === 'client_logo' ? 'selected' : '' }}>Client Logo</option>
                                            <option value="project_showcase" {{ old('type', $proof->type) === 'project_showcase' ? 'selected' : '' }}>Project Showcase</option>
                                            <option value="before_after" {{ old('type', $proof->type) === 'before_after' ? 'selected' : '' }}>Before/After</option>
                                            <option value="statistics" {{ old('type', $proof->type) === 'statistics' ? 'selected' : '' }}>Statistics</option>
                                            <option value="partnership" {{ old('type', $proof->type) === 'partnership' ? 'selected' : '' }}>Partnership</option>
                                        </select>
                                        @error('type')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Visibility -->
                                    <div>
                                        <label for="visibility" class="block text-sm font-medium text-gray-700">Visibility</label>
                                        <select name="visibility" id="visibility" x-model="formData.visibility"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="public" {{ old('visibility', $proof->visibility) === 'public' ? 'selected' : '' }}>Public</option>
                                            <option value="internal" {{ old('visibility', $proof->visibility) === 'internal' ? 'selected' : '' }}>Internal Only</option>
                                            <option value="client_specific" {{ old('visibility', $proof->visibility) === 'client_specific' ? 'selected' : '' }}>Client Specific</option>
                                        </select>
                                        @error('visibility')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Title -->
                                <div class="mt-6">
                                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                                    <input type="text" name="title" id="title" 
                                           value="{{ old('title', $proof->title) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="Enter proof title">
                                    @error('title')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="mt-6">
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="4"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                              placeholder="Detailed description of the proof...">{{ old('description', $proof->description) }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Asset Management -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h3 class="text-lg font-medium text-gray-900">Asset Management ({{ $proof->assets->count() }})</h3>
                                    <button type="button" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
                                            onclick="document.getElementById('upload-modal').classList.remove('hidden')">
                                        <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Upload Assets
                                    </button>
                                </div>

                                @if($proof->assets->count() > 0)
                                    <div class="space-y-4" x-data="assetManager()">
                                        @foreach($proof->assets as $asset)
                                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                                <div class="flex items-start space-x-4">
                                                    <!-- Asset Preview -->
                                                    <div class="flex-shrink-0">
                                                        <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden">
                                                            @if($asset->isImage())
                                                                <img src="{{ asset('storage/' . ($asset->thumbnail_path ?: $asset->file_path)) }}" 
                                                                     alt="{{ $asset->alt_text }}"
                                                                     class="w-full h-full object-cover">
                                                            @elseif($asset->isVideo())
                                                                <div class="w-full h-full flex items-center justify-center bg-gray-200 relative">
                                                                    @if($asset->thumbnail_path)
                                                                        <img src="{{ asset('storage/' . $asset->thumbnail_path) }}" 
                                                                             alt="Video thumbnail"
                                                                             class="w-full h-full object-cover">
                                                                    @else
                                                                        <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path d="M2 6a2 2 0 012-2h6l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM8 5.5a.5.5 0 01.5-.5h3a.5.5 0 01.5.5v1a.5.5 0 01-.5.5h-3a.5.5 0 01-.5-.5v-1z"/>
                                                                        </svg>
                                                                    @endif
                                                                    <div class="absolute inset-0 flex items-center justify-center">
                                                                        <div class="w-6 h-6 bg-black bg-opacity-50 rounded-full flex items-center justify-center">
                                                                            <svg class="w-3 h-3 text-white ml-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                                                <path d="M8 5v10l8-5-8-5z"/>
                                                                            </svg>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                                    <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                                                    </svg>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Asset Details -->
                                                    <div class="flex-1 min-w-0">
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                            <!-- Title -->
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                                                <input type="text" 
                                                                       name="assets[{{ $asset->id }}][title]"
                                                                       value="{{ $asset->title }}"
                                                                       class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                                       placeholder="Asset title">
                                                            </div>

                                                            <!-- Alt Text -->
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700">Alt Text</label>
                                                                <input type="text" 
                                                                       name="assets[{{ $asset->id }}][alt_text]"
                                                                       value="{{ $asset->alt_text }}"
                                                                       class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                                       placeholder="Descriptive alt text">
                                                            </div>
                                                        </div>

                                                        <!-- Description -->
                                                        <div class="mt-4">
                                                            <label class="block text-sm font-medium text-gray-700">Description</label>
                                                            <textarea name="assets[{{ $asset->id }}][description]" rows="2"
                                                                      class="mt-1 block w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                                      placeholder="Asset description...">{{ $asset->description }}</textarea>
                                                        </div>

                                                        <!-- Options -->
                                                        <div class="mt-4 flex flex-wrap items-center gap-4">
                                                            <div class="flex items-center">
                                                                <input type="checkbox" 
                                                                       name="assets[{{ $asset->id }}][is_primary]"
                                                                       value="1"
                                                                       {{ $asset->is_primary ? 'checked' : '' }}
                                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                                <label class="ml-2 text-sm text-gray-700">Primary Asset</label>
                                                            </div>

                                                            <div class="flex items-center">
                                                                <input type="checkbox" 
                                                                       name="assets[{{ $asset->id }}][show_in_gallery]"
                                                                       value="1"
                                                                       {{ $asset->show_in_gallery ? 'checked' : '' }}
                                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                                <label class="ml-2 text-sm text-gray-700">Show in Gallery</label>
                                                            </div>

                                                            <div class="text-sm text-gray-500">
                                                                {{ $asset->getHumanReadableSize() }}
                                                                @if($asset->width && $asset->height)
                                                                    • {{ $asset->width }}×{{ $asset->height }}px
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <!-- Processing Status -->
                                                        @if($asset->processing_status !== 'completed')
                                                            <div class="mt-2">
                                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $asset->processing_status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                                    Status: {{ ucfirst($asset->processing_status) }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Actions -->
                                                    <div class="flex-shrink-0 flex flex-col space-y-2">
                                                        <button type="button" 
                                                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                                                onclick="window.open('{{ asset('storage/' . $asset->file_path) }}', '_blank')">
                                                            Preview
                                                        </button>
                                                        <button type="button" 
                                                                class="text-red-600 hover:text-red-800 text-sm font-medium"
                                                                onclick="deleteAsset({{ $asset->id }})">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No assets uploaded</h3>
                                        <p class="mt-1 text-sm text-gray-500">Upload images, videos, or documents to showcase this proof.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Settings Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Display Settings -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Display Settings</h3>
                                
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <label for="is_featured" class="flex items-center text-sm font-medium text-gray-700">
                                            <svg class="w-4 h-4 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            Featured
                                        </label>
                                        <input type="checkbox" name="is_featured" id="is_featured" value="1"
                                               {{ old('is_featured', $proof->is_featured) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <label for="show_in_pdf" class="text-sm font-medium text-gray-700">Show in PDFs</label>
                                        <input type="checkbox" name="show_in_pdf" id="show_in_pdf" value="1"
                                               {{ old('show_in_pdf', $proof->show_in_pdf) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <label for="show_in_quotation" class="text-sm font-medium text-gray-700">Show in Quotations</label>
                                        <input type="checkbox" name="show_in_quotation" id="show_in_quotation" value="1"
                                               {{ old('show_in_quotation', $proof->show_in_quotation) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <label for="show_in_invoice" class="text-sm font-medium text-gray-700">Show in Invoices</label>
                                        <input type="checkbox" name="show_in_invoice" id="show_in_invoice" value="1"
                                               {{ old('show_in_invoice', $proof->show_in_invoice) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ordering & Expiration -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Settings</h3>
                                
                                <div class="space-y-4">
                                    <!-- Sort Order -->
                                    <div>
                                        <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                                        <input type="number" name="sort_order" id="sort_order" 
                                               value="{{ old('sort_order', $proof->sort_order) }}"
                                               min="0" max="999"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                                    </div>

                                    <!-- Expiration -->
                                    <div>
                                        <label for="expires_at" class="block text-sm font-medium text-gray-700">Expires At (Optional)</label>
                                        <input type="date" name="expires_at" id="expires_at" 
                                               value="{{ old('expires_at', $proof->expires_at ? $proof->expires_at->format('Y-m-d') : '') }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Leave blank for no expiration</p>
                                    </div>

                                    <!-- Conversion Impact -->
                                    <div>
                                        <label for="conversion_impact" class="block text-sm font-medium text-gray-700">Conversion Impact (%)</label>
                                        <input type="number" name="conversion_impact" id="conversion_impact" 
                                               value="{{ old('conversion_impact', $proof->conversion_impact) }}"
                                               min="0" max="100" step="0.1"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Estimated impact on conversions</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Scope Configuration -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Link to Specific Content</h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="scope_type" class="block text-sm font-medium text-gray-700">Content Type</label>
                                        <select name="scope_type" id="scope_type" x-model="formData.scope_type"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">None (General)</option>
                                            <option value="App\Models\Quotation" {{ old('scope_type', $proof->scope_type) === 'App\Models\Quotation' ? 'selected' : '' }}>Quotation</option>
                                            <option value="App\Models\Invoice" {{ old('scope_type', $proof->scope_type) === 'App\Models\Invoice' ? 'selected' : '' }}>Invoice</option>
                                            <option value="App\Models\Lead" {{ old('scope_type', $proof->scope_type) === 'App\Models\Lead' ? 'selected' : '' }}>Lead</option>
                                        </select>
                                    </div>

                                    <div x-show="formData.scope_type">
                                        <label for="scope_id" class="block text-sm font-medium text-gray-700">Content ID</label>
                                        <input type="number" name="scope_id" id="scope_id" 
                                               value="{{ old('scope_id', $proof->scope_id) }}"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Leave blank for general use</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="bg-white shadow rounded-lg">
                            <div class="px-4 py-5 sm:p-6">
                                <div class="space-y-3">
                                    <button type="submit" 
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                        Update Proof
                                    </button>
                                    
                                    <a href="{{ route('proofs.show', $proof->uuid) }}" 
                                       class="w-full bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium text-center block">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="upload-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" onclick="document.getElementById('upload-modal').classList.add('hidden')">
                <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <form action="{{ route('proofs.upload-assets', $proof->uuid) }}" method="POST" enctype="multipart/form-data" 
                      x-data="fileUpload()" x-init="initDropzone()">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Upload New Assets</h3>
                            <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')" 
                                    class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Drag and Drop Area -->
                        <div class="space-y-4">
                            <div id="dropzone" 
                                 class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors cursor-pointer"
                                 @dragover.prevent="dragActive = true"
                                 @dragleave.prevent="dragActive = false"
                                 @drop.prevent="handleDrop($event)"
                                 :class="{'border-blue-400 bg-blue-50': dragActive}"
                                 onclick="document.getElementById('file-input').click()">
                                
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                
                                <div class="mt-4">
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium text-blue-600 hover:text-blue-500 cursor-pointer">Click to upload</span> 
                                        or drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Images, videos, PDFs, documents up to 10MB each
                                    </p>
                                </div>
                            </div>

                            <input type="file" id="file-input" name="assets[]" multiple 
                                   accept="image/*,video/*,.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt"
                                   class="hidden"
                                   @change="handleFileSelect($event)">

                            <!-- Selected Files Preview -->
                            <div x-show="selectedFiles.length > 0" class="space-y-2">
                                <h4 class="text-sm font-medium text-gray-900">Selected Files:</h4>
                                <div class="max-h-40 overflow-y-auto space-y-1">
                                    <template x-for="(file, index) in selectedFiles" :key="index">
                                        <div class="flex items-center justify-between bg-gray-50 rounded px-3 py-2">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                                </svg>
                                                <span class="text-sm text-gray-700" x-text="file.name"></span>
                                                <span class="text-xs text-gray-500" x-text="formatFileSize(file.size)"></span>
                                            </div>
                                            <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                                :disabled="selectedFiles.length === 0"
                                :class="selectedFiles.length === 0 ? 'bg-gray-400' : 'bg-blue-600 hover:bg-blue-700'"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <span x-show="selectedFiles.length === 0">Select Files to Upload</span>
                            <span x-show="selectedFiles.length > 0" x-text="'Upload ' + selectedFiles.length + ' File(s)'"></span>
                        </button>
                        <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function proofEditForm() {
            return {
                formData: {
                    type: '{{ old('type', $proof->type) }}',
                    visibility: '{{ old('visibility', $proof->visibility) }}',
                    scope_type: '{{ old('scope_type', $proof->scope_type) }}'
                }
            }
        }

        function assetManager() {
            return {
                // Asset management functionality can be added here
            }
        }

        function fileUpload() {
            return {
                dragActive: false,
                selectedFiles: [],

                initDropzone() {
                    // Additional dropzone initialization if needed
                },

                handleDrop(e) {
                    this.dragActive = false;
                    const files = Array.from(e.dataTransfer.files);
                    this.addFiles(files);
                },

                handleFileSelect(e) {
                    const files = Array.from(e.target.files);
                    this.addFiles(files);
                },

                addFiles(files) {
                    files.forEach(file => {
                        if (this.isValidFile(file)) {
                            this.selectedFiles.push(file);
                        }
                    });
                    this.updateFileInput();
                },

                removeFile(index) {
                    this.selectedFiles.splice(index, 1);
                    this.updateFileInput();
                },

                isValidFile(file) {
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    const allowedTypes = [
                        'image/', 'video/', 'application/pdf', 
                        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/plain'
                    ];

                    if (file.size > maxSize) {
                        alert(`File "${file.name}" is too large. Maximum size is 10MB.`);
                        return false;
                    }

                    const isValidType = allowedTypes.some(type => file.type.startsWith(type) || file.type === type);
                    if (!isValidType) {
                        alert(`File "${file.name}" is not a supported file type.`);
                        return false;
                    }

                    return true;
                },

                updateFileInput() {
                    const fileInput = document.getElementById('file-input');
                    const dt = new DataTransfer();
                    this.selectedFiles.forEach(file => dt.items.add(file));
                    fileInput.files = dt.files;
                },

                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
                }
            }
        }

        function deleteAsset(assetId) {
            if (confirm('Are you sure you want to delete this asset? This action cannot be undone.')) {
                // Create a form to delete the asset
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/proofs/assets/${assetId}`;
                form.style.display = 'none';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodField);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    @endpush
</x-app-layout>