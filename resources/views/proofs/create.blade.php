<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Create Proof') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Build credibility with powerful social proof</p>
            </div>
            <a href="{{ route('proofs.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                Back to Proofs
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('proofs.store') }}" method="POST" enctype="multipart/form-data" x-data="proofForm()">
                @csrf
                
                <div class="space-y-8">
                    <!-- Basic Information -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Type -->
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">Proof Type*</label>
                                    <select name="type" id="type" x-model="form.type" required
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value="">Select a type</option>
                                        @foreach(\App\Models\Proof::TYPES as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Title -->
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700">Title*</label>
                                    <input type="text" name="title" id="title" x-model="form.title" required
                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                           placeholder="Enter proof title">
                                    @error('title')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mt-6">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="3" x-model="form.description"
                                          class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                          placeholder="Provide a detailed description of this proof..."></textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Assets</h3>
                            
                            <!-- Drag and Drop Upload Area -->
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
                                 x-bind:class="isDragOver ? 'border-blue-400 bg-blue-50' : 'border-gray-300'"
                                 x-on:dragover.prevent="isDragOver = true"
                                 x-on:dragleave.prevent="isDragOver = false"
                                 x-on:drop.prevent="handleFileDrop($event)">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="assets" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload files</span>
                                            <input id="assets" name="assets[]" type="file" class="sr-only" multiple 
                                                   accept="image/*,video/*,.pdf,.doc,.docx"
                                                   x-on:change="handleFileSelect($event)">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">Images, videos, or documents up to 10MB each</p>
                                </div>
                            </div>

                            <!-- Selected Files -->
                            <div x-show="selectedFiles.length > 0" class="mt-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Selected Files (<span x-text="selectedFiles.length"></span>)</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <template x-for="(file, index) in selectedFiles" :key="file.name + index">
                                        <div class="relative bg-gray-50 rounded-lg p-3 border border-gray-200">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate" x-text="file.name"></p>
                                                    <p class="text-sm text-gray-500" x-text="formatFileSize(file.size)"></p>
                                                    
                                                    <!-- File Type Icon -->
                                                    <div class="mt-2">
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                              x-bind:class="getFileTypeColor(file.type)">
                                                            <span x-text="getFileTypeLabel(file.type)"></span>
                                                        </span>
                                                    </div>

                                                    <!-- Additional Fields for Each Asset -->
                                                    <div class="mt-3 space-y-2">
                                                        <input type="text" 
                                                               x-bind:name="'asset_titles[' + index + ']'"
                                                               x-model="file.title"
                                                               class="block w-full text-xs border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                                               placeholder="Asset title (optional)">
                                                        <textarea x-bind:name="'asset_descriptions[' + index + ']'"
                                                                  x-model="file.description"
                                                                  rows="2"
                                                                  class="block w-full text-xs border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                                                  placeholder="Asset description (optional)"></textarea>
                                                        <input type="text" 
                                                               x-bind:name="'asset_alt_texts[' + index + ']'"
                                                               x-model="file.altText"
                                                               class="block w-full text-xs border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                                               placeholder="Alt text for accessibility">
                                                    </div>
                                                </div>
                                                
                                                <!-- Remove Button -->
                                                <button type="button" 
                                                        x-on:click="removeFile(index)"
                                                        class="ml-2 text-gray-400 hover:text-red-500">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            
                                            <!-- Image Preview -->
                                            <div x-show="file.type.startsWith('image/')" class="mt-3">
                                                <img x-bind:src="file.preview" class="w-full h-20 object-cover rounded">
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            @error('assets')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('assets.*')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Settings</h3>
                            
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Visibility -->
                                <div>
                                    <label for="visibility" class="block text-sm font-medium text-gray-700">Visibility</label>
                                    <select name="visibility" id="visibility" x-model="form.visibility"
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        @foreach(\App\Models\Proof::VISIBILITY_LEVELS as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('visibility')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Expiry Date -->
                                <div>
                                    <label for="expires_at" class="block text-sm font-medium text-gray-700">Expires At</label>
                                    <input type="date" name="expires_at" id="expires_at" x-model="form.expires_at"
                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    @error('expires_at')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Display Options -->
                            <div class="mt-6">
                                <fieldset>
                                    <legend class="text-sm font-medium text-gray-900">Display Options</legend>
                                    <div class="mt-4 space-y-4">
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="show_in_pdf" name="show_in_pdf" type="checkbox" x-model="form.show_in_pdf"
                                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="show_in_pdf" class="font-medium text-gray-700">Show in PDFs</label>
                                                <p class="text-gray-500">Include this proof in generated quotation and invoice PDFs</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="show_in_quotation" name="show_in_quotation" type="checkbox" x-model="form.show_in_quotation"
                                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="show_in_quotation" class="font-medium text-gray-700">Show in Quotations</label>
                                                <p class="text-gray-500">Display this proof on quotation pages</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="show_in_invoice" name="show_in_invoice" type="checkbox" x-model="form.show_in_invoice"
                                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="show_in_invoice" class="font-medium text-gray-700">Show in Invoices</label>
                                                <p class="text-gray-500">Display this proof on invoice pages</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="is_featured" name="is_featured" type="checkbox" x-model="form.is_featured"
                                                       class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="is_featured" class="font-medium text-gray-700">Featured</label>
                                                <p class="text-gray-500">Mark as featured proof for priority display</p>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <!-- Advanced Options -->
                            <div class="mt-6">
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <!-- Sort Order -->
                                    <div>
                                        <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                                        <input type="number" name="sort_order" id="sort_order" x-model="form.sort_order" min="0"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                               placeholder="0">
                                        <p class="mt-1 text-sm text-gray-500">Lower numbers appear first</p>
                                        @error('sort_order')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Conversion Impact -->
                                    <div>
                                        <label for="conversion_impact" class="block text-sm font-medium text-gray-700">Conversion Impact (%)</label>
                                        <input type="number" name="conversion_impact" id="conversion_impact" x-model="form.conversion_impact" min="0" max="100" step="0.1"
                                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                               placeholder="0.0">
                                        <p class="mt-1 text-sm text-gray-500">Expected impact on conversion rate</p>
                                        @error('conversion_impact')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="window.history.back()" 
                                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit" name="action" value="draft"
                                class="bg-gray-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Save as Draft
                        </button>
                        <button type="submit" name="action" value="publish"
                                class="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Publish Proof
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function proofForm() {
            return {
                form: {
                    type: '',
                    title: '',
                    description: '',
                    visibility: 'public',
                    expires_at: '',
                    show_in_pdf: true,
                    show_in_quotation: true,
                    show_in_invoice: false,
                    is_featured: false,
                    sort_order: 0,
                    conversion_impact: 0
                },
                selectedFiles: [],
                isDragOver: false,

                handleFileDrop(e) {
                    this.isDragOver = false;
                    const files = Array.from(e.dataTransfer.files);
                    this.addFiles(files);
                },

                handleFileSelect(e) {
                    const files = Array.from(e.target.files);
                    this.addFiles(files);
                },

                addFiles(files) {
                    files.forEach((file, index) => {
                        // Validate file
                        if (this.validateFile(file)) {
                            const fileObj = {
                                file: file,
                                name: file.name,
                                size: file.size,
                                type: file.type,
                                title: '',
                                description: '',
                                altText: '',
                                preview: null
                            };

                            // Generate preview for images
                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = (e) => {
                                    fileObj.preview = e.target.result;
                                    this.$nextTick();
                                };
                                reader.readAsDataURL(file);
                            }

                            this.selectedFiles.push(fileObj);
                        }
                    });

                    // Update the file input
                    this.updateFileInput();
                },

                removeFile(index) {
                    this.selectedFiles.splice(index, 1);
                    this.updateFileInput();
                },

                validateFile(file) {
                    const maxSize = 10 * 1024 * 1024; // 10MB
                    const allowedTypes = [
                        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                        'video/mp4', 'video/webm', 'video/quicktime',
                        'application/pdf', 'application/msword', 
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ];

                    if (file.size > maxSize) {
                        alert(`File "${file.name}" is too large. Maximum size is 10MB.`);
                        return false;
                    }

                    if (!allowedTypes.includes(file.type)) {
                        alert(`File type "${file.type}" is not allowed.`);
                        return false;
                    }

                    return true;
                },

                updateFileInput() {
                    const input = document.getElementById('assets');
                    const dt = new DataTransfer();
                    
                    this.selectedFiles.forEach(fileObj => {
                        dt.items.add(fileObj.file);
                    });
                    
                    input.files = dt.files;
                },

                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },

                getFileTypeLabel(type) {
                    if (type.startsWith('image/')) return 'Image';
                    if (type.startsWith('video/')) return 'Video';
                    if (type.includes('pdf')) return 'PDF';
                    if (type.includes('word')) return 'Document';
                    return 'File';
                },

                getFileTypeColor(type) {
                    if (type.startsWith('image/')) return 'bg-green-100 text-green-800';
                    if (type.startsWith('video/')) return 'bg-purple-100 text-purple-800';
                    if (type.includes('pdf')) return 'bg-red-100 text-red-800';
                    if (type.includes('word')) return 'bg-blue-100 text-blue-800';
                    return 'bg-gray-100 text-gray-800';
                }
            }
        }
    </script>
    @endpush
</x-app-layout>