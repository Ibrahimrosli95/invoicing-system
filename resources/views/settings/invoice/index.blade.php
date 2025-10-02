@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-6xl" x-data="invoiceSettings()" x-init="init()">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Invoice Settings</h1>
            <p class="text-gray-600 mt-1">Configure invoice appearance and content for PDF generation</p>
        </div>
        <div class="flex gap-3">
            <button @click="previewPDF"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Preview PDF
            </button>
            <button @click="saveSettings"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Settings
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <div x-show="message.show"
         x-transition
         :class="message.type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'"
         class="mb-6 p-4 rounded-lg border">
        <p x-text="message.text"></p>
    </div>

    <div>

        <!-- 1. Colors / Appearance -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
                Colors & Appearance
            </h2>
            <p class="text-sm text-gray-600 mb-6">These colors control the appearance of your PDF invoices</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Primary Color (Table Headers)
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color"
                               x-model="settings.appearance.accent_color"
                               class="h-12 w-20 rounded border border-gray-300 cursor-pointer">
                        <input type="text"
                               x-model="settings.appearance.accent_color"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
                               placeholder="#0b57d0">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Primary Text Color (On Headers)
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color"
                               x-model="settings.appearance.accent_text_color"
                               class="h-12 w-20 rounded border border-gray-300 cursor-pointer">
                        <input type="text"
                               x-model="settings.appearance.accent_text_color"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
                               placeholder="#ffffff">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Main Text Color
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color"
                               x-model="settings.appearance.text_color"
                               class="h-12 w-20 rounded border border-gray-300 cursor-pointer">
                        <input type="text"
                               x-model="settings.appearance.text_color"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
                               placeholder="#000000">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Secondary Text Color
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color"
                               x-model="settings.appearance.muted_text_color"
                               class="h-12 w-20 rounded border border-gray-300 cursor-pointer">
                        <input type="text"
                               x-model="settings.appearance.muted_text_color"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
                               placeholder="#4b5563">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Heading Color
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color"
                               x-model="settings.appearance.heading_color"
                               class="h-12 w-20 rounded border border-gray-300 cursor-pointer">
                        <input type="text"
                               x-model="settings.appearance.heading_color"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
                               placeholder="#000000">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Border Color
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color"
                               x-model="settings.appearance.border_color"
                               class="h-12 w-20 rounded border border-gray-300 cursor-pointer">
                        <input type="text"
                               x-model="settings.appearance.border_color"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
                               placeholder="#d0d5dd">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Table Columns -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Table Columns
            </h2>
            <p class="text-sm text-gray-600 mb-6">Configure which columns appear in your invoice table</p>

            <div class="space-y-3">
                <template x-for="(column, index) in settings.columns" :key="column.key">
                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center gap-2">
                            <input type="checkbox"
                                   x-model="column.visible"
                                   class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide min-w-[100px]" x-text="column.key"></span>
                        </div>
                        <input type="text"
                               x-model="column.label"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Column Label">
                        <input type="number"
                               x-model="column.order"
                               min="1"
                               class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Order">
                    </div>
                </template>
            </div>
        </div>

        <!-- 3. Visibility Toggles -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Show/Hide Sections
            </h2>
            <p class="text-sm text-gray-600 mb-6">Toggle visibility of sections in your PDF invoice</p>

            <div class="space-y-4">
                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100">
                    <input type="checkbox"
                           x-model="settings.sections.show_company_logo"
                           class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                    <div>
                        <div class="font-medium text-gray-900">Show Company Logo</div>
                        <div class="text-sm text-gray-600">Display your company logo at the top of the invoice</div>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100">
                    <input type="checkbox"
                           x-model="settings.sections.show_payment_instructions"
                           class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                    <div>
                        <div class="font-medium text-gray-900">Show Payment Instructions</div>
                        <div class="text-sm text-gray-600">Display bank details and payment information</div>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100">
                    <input type="checkbox"
                           x-model="settings.sections.show_signatures"
                           class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                    <div>
                        <div class="font-medium text-gray-900">Show Signature Lines</div>
                        <div class="text-sm text-gray-600">Display signature blocks at the bottom of the invoice</div>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100">
                    <input type="checkbox"
                           x-model="settings.sections.show_company_signature"
                           class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                    <div>
                        <div class="font-medium text-gray-900">Show Company Signature (Optional)</div>
                        <div class="text-sm text-gray-600">Display authorized company signatory signature (in addition to sales rep signature)</div>
                    </div>
                </label>

                <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100">
                    <input type="checkbox"
                           x-model="settings.sections.show_customer_signature"
                           class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                    <div>
                        <div class="font-medium text-gray-900">Show Customer Signature (Optional)</div>
                        <div class="text-sm text-gray-600">Display customer acceptance signature line</div>
                    </div>
                </label>
            </div>
        </div>

        <!-- 4. Payment Instructions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Payment Instructions
            </h2>
            <p class="text-sm text-gray-600 mb-6">Bank details that will appear on the invoice</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                    <input type="text"
                           x-model="settings.payment_instructions.bank_name"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., Maybank">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                    <input type="text"
                           x-model="settings.payment_instructions.account_number"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., 1234567890">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Account Holder</label>
                    <input type="text"
                           x-model="settings.payment_instructions.account_holder"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., Company Name Sdn Bhd">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Info</label>
                    <input type="text"
                           x-model="settings.payment_instructions.additional_info"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., Please include invoice number">
                </div>
            </div>
        </div>

        <!-- 5. Company Signature (Optional Authorized Signatory) -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Company Signature (Optional)
            </h2>
            <p class="text-sm text-gray-600 mb-6">
                Upload authorized company signatory signature for official invoices.
                <span class="text-orange-600 font-medium">Note:</span> Sales rep signatures are managed in their individual profiles. This is for company-level authorized signatories (MD, Finance Manager, etc.)
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Authorized Signatory Name</label>
                    <input type="text"
                           x-model="settings.company_signature.name"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., Tan Sri Ahmad">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Signatory Title</label>
                    <input type="text"
                           x-model="settings.company_signature.title"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="e.g., Managing Director">
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Signature Image</label>
                <input type="file"
                       @change="handleCompanySignatureUpload"
                       accept="image/jpeg,image/jpg,image/png"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-1">Accepted formats: JPG, PNG. Max size: 2MB.</p>

                <!-- Current Signature Preview -->
                <template x-if="settings.company_signature.image_path">
                    <div class="mt-4 flex items-start gap-4">
                        <div class="border border-gray-300 rounded-lg p-3 bg-gray-50">
                            <img :src="`/storage/${settings.company_signature.image_path}`"
                                 alt="Current Signature"
                                 class="h-16 max-w-xs">
                        </div>
                        <button @click="removeCompanySignature"
                                type="button"
                                class="px-3 py-1 bg-red-50 text-red-700 border border-red-200 rounded-lg hover:bg-red-100 text-sm">
                            Remove Signature
                        </button>
                    </div>
                </template>
            </div>
        </div>

    </div>
</div>

<script>
function invoiceSettings() {
    return {
        settings: {
            appearance: {
                accent_color: '#0b57d0',
                accent_text_color: '#ffffff',
                text_color: '#000000',
                muted_text_color: '#4b5563',
                heading_color: '#000000',
                border_color: '#d0d5dd',
            },
            columns: [
                { key: 'sl', label: 'Sl.', visible: true, order: 1 },
                { key: 'description', label: 'Description', visible: true, order: 2 },
                { key: 'quantity', label: 'Qty', visible: true, order: 3 },
                { key: 'rate', label: 'Rate', visible: true, order: 4 },
                { key: 'amount', label: 'Amount', visible: true, order: 5 },
            ],
            sections: {
                show_company_logo: true,
                show_payment_instructions: true,
                show_signatures: true,
                show_company_signature: false,
                show_customer_signature: false,
            },
            payment_instructions: {
                bank_name: '',
                account_number: '',
                account_holder: '',
                additional_info: 'Please include invoice number in payment reference.',
            },
            company_signature: {
                name: '',
                title: '',
                image_path: '',
            }
        },

        message: {
            show: false,
            type: '',
            text: ''
        },

        init() {
            this.loadSettings();
        },

        loadSettings() {
            // Load columns
            fetch('/invoice-settings/columns')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.columns) {
                        this.settings.columns = data.columns;
                    }
                });

            // Load appearance
            fetch('/invoice-settings/api')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.settings) {
                        if (data.settings.appearance) {
                            this.settings.appearance = { ...this.settings.appearance, ...data.settings.appearance };
                        }
                        if (data.settings.sections) {
                            this.settings.sections = { ...this.settings.sections, ...data.settings.sections };
                        }
                        if (data.settings.payment_instructions) {
                            this.settings.payment_instructions = { ...this.settings.payment_instructions, ...data.settings.payment_instructions };
                        }
                        if (data.settings.company_signature) {
                            this.settings.company_signature = { ...this.settings.company_signature, ...data.settings.company_signature };
                        }
                    }
                })
                .catch(error => {
                    console.error('Failed to load settings:', error);
                });
        },

        async saveSettings() {
            try {
                console.log('Saving settings:', this.settings);

                // Save main settings (sections + payment_instructions)
                const mainSettings = {
                    sections: this.settings.sections,
                    payment_instructions: this.settings.payment_instructions
                };

                const mainResponse = await fetch('/invoice-settings', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(mainSettings)
                });

                if (!mainResponse.ok) {
                    const errorData = await mainResponse.json();
                    console.error('Main settings errors:', errorData);
                    throw new Error(errorData.message || 'Failed to save main settings');
                }

                // Save columns
                const columnsResponse = await fetch('/invoice-settings/columns', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ columns: this.settings.columns })
                });

                if (!columnsResponse.ok) {
                    const errorData = await columnsResponse.json();
                    console.error('Columns errors:', errorData);
                    throw new Error('Failed to save columns');
                }

                // Save appearance
                const appearanceResponse = await fetch('/invoice-settings/appearance', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ appearance: this.settings.appearance })
                });

                if (!appearanceResponse.ok) {
                    const errorData = await appearanceResponse.json();
                    console.error('Appearance errors:', errorData);
                    throw new Error('Failed to save appearance');
                }

                this.showMessage('success', 'Settings saved successfully!');
            } catch (error) {
                console.error('Save error:', error);
                this.showMessage('error', error.message || 'Failed to save settings');
            }
        },

        previewPDF() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/invoice-settings/preview-pdf';
            form.target = '_blank';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.innerHTML = `
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="settings" value='${JSON.stringify(this.settings)}'>
            `;

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        },

        showMessage(type, text) {
            this.message = { show: true, type, text };
            setTimeout(() => {
                this.message.show = false;
            }, 5000);
        },

        async handleCompanySignatureUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file
            if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                this.showMessage('error', 'Please upload a JPG or PNG image.');
                event.target.value = '';
                return;
            }

            if (file.size > 2 * 1024 * 1024) { // 2MB
                this.showMessage('error', 'File size must be less than 2MB.');
                event.target.value = '';
                return;
            }

            // Upload company signature
            const formData = new FormData();
            formData.append('signature_image', file);
            formData.append('signature_name', this.settings.company_signature.name);
            formData.append('signature_title', this.settings.company_signature.title);

            try {
                const response = await fetch('/invoice-settings/company-signature', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.settings.company_signature = data.company_signature;
                    this.showMessage('success', 'Company signature uploaded successfully!');
                } else {
                    throw new Error(data.message || 'Failed to upload company signature');
                }
            } catch (error) {
                console.error('Upload error:', error);
                this.showMessage('error', error.message || 'Failed to upload company signature');
            }

            // Clear file input
            event.target.value = '';
        },

        async removeCompanySignature() {
            if (!confirm('Are you sure you want to remove the company signature?')) {
                return;
            }

            const formData = new FormData();
            formData.append('signature_name', this.settings.company_signature.name);
            formData.append('signature_title', this.settings.company_signature.title);
            formData.append('remove_signature', '1');

            try {
                const response = await fetch('/invoice-settings/company-signature', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    this.settings.company_signature.image_path = '';
                    this.showMessage('success', 'Company signature removed successfully!');
                } else {
                    throw new Error(data.message || 'Failed to remove company signature');
                }
            } catch (error) {
                console.error('Remove error:', error);
                this.showMessage('error', error.message || 'Failed to remove company signature');
            }
        }
    }
}
</script>
@endsection