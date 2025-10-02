<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">Signature Management</h2>
                <p class="mt-1 text-sm text-gray-600">Manage your personal signature for quotations and invoices</p>
            </div>
            <a href="{{ route('profile') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Profile
            </a>
        </div>
    </x-slot>

    <div class="py-6 md:py-8" x-data="signatureManager()" x-init="init()">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Success/Error Messages -->
            <div x-show="message.show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 :class="message.type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'"
                 class="mb-6 p-4 rounded-lg border"
                 style="display: none;">
                <div class="flex items-center gap-3">
                    <svg x-show="message.type === 'success'" class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="message.type === 'error'" class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p :class="message.type === 'success' ? 'text-green-800' : 'text-red-800'" class="text-sm font-medium" x-text="message.text"></p>
                </div>
            </div>

            <!-- Signature Preview Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Signature Preview
                </h3>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <div class="flex flex-col items-center">
                        <!-- Signature Image -->
                        <template x-if="signature.image_path">
                            <img :src="`/storage/${signature.image_path}`"
                                 alt="Your Signature"
                                 class="h-16 mb-4 max-w-full">
                        </template>
                        <template x-if="!signature.image_path">
                            <div class="h-16 mb-4 flex items-center justify-center text-gray-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </div>
                        </template>

                        <!-- Signature Line -->
                        <div class="border-t border-gray-400 w-64 mt-2"></div>

                        <!-- Signature Title -->
                        <div class="mt-3 text-sm text-gray-900 text-center font-medium" x-text="signature.title || 'Sales Representative'">
                            Sales Representative
                        </div>

                        <!-- Signature Name -->
                        <div class="mt-1 text-sm text-gray-600 text-center" x-text="signature.name || '{{ auth()->user()->name }}'">
                            {{ auth()->user()->name }}
                        </div>

                        <!-- Company Name -->
                        <div class="mt-1 text-xs text-gray-500 text-center">
                            {{ auth()->user()->company->name }}
                        </div>
                    </div>
                </div>

                <p class="mt-4 text-sm text-gray-600">
                    This signature will appear on all quotations and invoices you create.
                </p>
            </div>

            <!-- Signature Settings Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    Signature Settings
                </h3>

                <form @submit.prevent="updateSignature" class="space-y-6">
                    <!-- Signature Name -->
                    <div>
                        <label for="signature_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Signature Name
                        </label>
                        <input type="text"
                               id="signature_name"
                               x-model="signature.name"
                               placeholder="Your full name"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">The name that will appear below your signature</p>
                    </div>

                    <!-- Signature Title -->
                    <div>
                        <label for="signature_title" class="block text-sm font-medium text-gray-700 mb-2">
                            Job Title
                        </label>
                        <input type="text"
                               id="signature_title"
                               x-model="signature.title"
                               placeholder="e.g., Sales Representative, Account Manager"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Your job title or role</p>
                    </div>

                    <!-- Signature Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Signature Image
                        </label>

                        <!-- Current Signature Display -->
                        <template x-if="signature.image_path">
                            <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <img :src="`/storage/${signature.image_path}`"
                                             alt="Current Signature"
                                             class="h-12 max-w-xs">
                                        <span class="text-sm text-gray-600">Current signature</span>
                                    </div>
                                    <button type="button"
                                            @click="removeSignature"
                                            class="px-3 py-1 text-sm text-red-600 hover:text-red-700 font-medium">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- File Upload Input -->
                        <div class="relative">
                            <input type="file"
                                   id="signature_image"
                                   @change="handleFileChange"
                                   accept="image/jpeg,image/jpg,image/png"
                                   class="hidden">
                            <label for="signature_image"
                                   class="flex items-center justify-center gap-2 px-4 py-3 bg-white border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-colors">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <span class="text-sm text-gray-600">
                                    <span class="font-medium text-blue-600">Click to upload</span> or drag and drop
                                </span>
                            </label>
                        </div>

                        <p class="mt-2 text-xs text-gray-500">
                            PNG or JPG (max 2MB). Use a clear, professional signature image with transparent background for best results.
                        </p>

                        <!-- Selected File Preview -->
                        <template x-if="selectedFile">
                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm text-blue-900 font-medium" x-text="selectedFile.name"></span>
                                    </div>
                                    <button type="button"
                                            @click="clearFileSelection"
                                            class="text-blue-600 hover:text-blue-700">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <button type="button"
                                @click="resetForm"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Reset
                        </button>

                        <button type="submit"
                                :disabled="saving"
                                :class="saving ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                                class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 text-white font-medium rounded-lg transition-colors">
                            <svg x-show="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" style="display: none;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="saving ? 'Saving...' : 'Save Signature'">Save Signature</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Help Card -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-blue-900 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Tips for a Professional Signature
                </h4>
                <ul class="text-sm text-blue-800 space-y-1 ml-6 list-disc">
                    <li>Use a clear image with a transparent background (PNG format recommended)</li>
                    <li>Keep your signature simple and legible</li>
                    <li>Ensure the image is high quality but under 2MB in size</li>
                    <li>Your signature will be automatically sized to fit documents</li>
                </ul>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function signatureManager() {
            return {
                signature: {
                    name: '{{ $user->signature_name ?? $user->name }}',
                    title: '{{ $user->signature_title ?? "Sales Representative" }}',
                    image_path: '{{ $user->signature_path ?? "" }}'
                },
                selectedFile: null,
                saving: false,
                message: {
                    show: false,
                    type: 'success',
                    text: ''
                },

                init() {
                    // Load current signature on init
                    this.loadSignature();
                },

                async loadSignature() {
                    try {
                        const response = await fetch('/profile/signature/show');
                        const data = await response.json();

                        if (data.success && data.signature) {
                            this.signature = data.signature;
                        }
                    } catch (error) {
                        console.error('Failed to load signature:', error);
                    }
                },

                handleFileChange(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                    if (!allowedTypes.includes(file.type)) {
                        this.showMessage('error', 'Please upload a JPG or PNG image.');
                        event.target.value = '';
                        return;
                    }

                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        this.showMessage('error', 'Image size must be less than 2MB.');
                        event.target.value = '';
                        return;
                    }

                    this.selectedFile = file;
                },

                clearFileSelection() {
                    this.selectedFile = null;
                    document.getElementById('signature_image').value = '';
                },

                async updateSignature() {
                    this.saving = true;

                    try {
                        const formData = new FormData();
                        formData.append('signature_name', this.signature.name);
                        formData.append('signature_title', this.signature.title);

                        if (this.selectedFile) {
                            formData.append('signature_image', this.selectedFile);
                        }

                        const response = await fetch('/profile/signature', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.signature = data.signature;
                            this.selectedFile = null;
                            document.getElementById('signature_image').value = '';
                            this.showMessage('success', 'Signature updated successfully!');
                        } else {
                            this.showMessage('error', data.message || 'Failed to update signature.');
                        }
                    } catch (error) {
                        console.error('Error updating signature:', error);
                        this.showMessage('error', 'An error occurred while updating your signature.');
                    } finally {
                        this.saving = false;
                    }
                },

                async removeSignature() {
                    if (!confirm('Are you sure you want to remove your signature?')) {
                        return;
                    }

                    this.saving = true;

                    try {
                        const response = await fetch('/profile/signature', {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.signature.image_path = '';
                            this.showMessage('success', 'Signature removed successfully!');
                        } else {
                            this.showMessage('error', 'Failed to remove signature.');
                        }
                    } catch (error) {
                        console.error('Error removing signature:', error);
                        this.showMessage('error', 'An error occurred while removing your signature.');
                    } finally {
                        this.saving = false;
                    }
                },

                resetForm() {
                    this.loadSignature();
                    this.selectedFile = null;
                    document.getElementById('signature_image').value = '';
                    this.showMessage('success', 'Form reset to saved values.');
                },

                showMessage(type, text) {
                    this.message = { show: true, type, text };
                    setTimeout(() => {
                        this.message.show = false;
                    }, 5000);
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
