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
            },
            payment_instructions: {
                bank_name: '',
                account_number: '',
                account_holder: '',
                additional_info: 'Please include invoice number in payment reference.',
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
        }
    }
}
</script>
@endsection