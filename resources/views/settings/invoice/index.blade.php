@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-6" x-data="invoiceSettings()">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Invoice Settings</h1>
                <p class="text-gray-600 mt-1">Configure default invoice options and appearance settings</p>
            </div>
            <button @click="saveSettings"
                    class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Save Changes
            </button>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="max-w-4xl">
        <div class="space-y-8">
            <!-- Optional Sections -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Optional Invoice Sections</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox"
                                   x-model="settings.sections.show_shipping"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <span class="ml-3">
                                <span class="text-sm font-medium text-gray-900">Shipping Information</span>
                                <span class="block text-xs text-gray-500">Include ship-to address section</span>
                            </span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox"
                                   x-model="settings.sections.show_payment_instructions"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <span class="ml-3">
                                <span class="text-sm font-medium text-gray-900">Payment Instructions</span>
                                <span class="block text-xs text-gray-500">Show bank details and payment info</span>
                            </span>
                        </label>
                    </div>

                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox"
                                   x-model="settings.sections.show_signatures"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <span class="ml-3">
                                <span class="text-sm font-medium text-gray-900">Signature Blocks</span>
                                <span class="block text-xs text-gray-500">Include signature areas for both parties</span>
                            </span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox"
                                   x-model="settings.sections.show_additional_notes"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <span class="ml-3">
                                <span class="text-sm font-medium text-gray-900">Additional Notes</span>
                                <span class="block text-xs text-gray-500">Show extra notes section</span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Logo Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Company Logo</h2>
                <div class="space-y-4">
                    <label class="flex items-center">
                        <input type="checkbox"
                               x-model="settings.logo.show_company_logo"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <span class="ml-3 text-sm font-medium text-gray-900">Show company logo on invoices</span>
                    </label>

                    <div x-show="settings.logo.show_company_logo" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logo Position</label>
                            <select x-model="settings.logo.logo_position"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                                <option value="left">Left</option>
                                <option value="center">Center</option>
                                <option value="right">Right</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logo Size</label>
                            <select x-model="settings.logo.logo_size"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                                <option value="small">Small</option>
                                <option value="medium">Medium</option>
                                <option value="large">Large</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Default Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Default Values</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Terms (days)</label>
                        <input type="number"
                               x-model="settings.defaults.payment_terms"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                               min="1" max="365">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Late Fee (%)</label>
                        <input type="number"
                               x-model="settings.defaults.late_fee_percentage"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                               min="0" max="100" step="0.1">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Default Currency</label>
                        <select x-model="settings.defaults.currency"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <option value="RM">Malaysian Ringgit (RM)</option>
                            <option value="USD">US Dollar (USD)</option>
                            <option value="EUR">Euro (EUR)</option>
                            <option value="SGD">Singapore Dollar (SGD)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tax Percentage</label>
                        <input type="number"
                               x-model="settings.defaults.tax_percentage"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                               min="0" max="100" step="0.01">
                    </div>
                </div>

                <div class="mt-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-4">Column Visibility</h3>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox"
                                   x-model="settings.defaults.show_discount_column"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <span class="ml-3 text-sm font-medium text-gray-900">Show discount column by default</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox"
                                   x-model="settings.defaults.show_tax_column"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <span class="ml-3 text-sm font-medium text-gray-900">Show tax column by default</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Content Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Default Content</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Default Terms & Conditions</label>
                        <textarea x-model="settings.content.default_terms"
                                  rows="4"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                  placeholder="Enter default terms and conditions..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Default Notes</label>
                        <textarea x-model="settings.content.default_notes"
                                  rows="2"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                  placeholder="Enter default invoice notes..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Payment Instructions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Payment Instructions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                        <input type="text"
                               x-model="settings.content.payment_instructions.bank_name"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                               placeholder="e.g., Maybank">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                        <input type="text"
                               x-model="settings.content.payment_instructions.account_number"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                               placeholder="e.g., 1234567890">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Account Holder Name</label>
                        <input type="text"
                               x-model="settings.content.payment_instructions.account_holder"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                               placeholder="e.g., {{ auth()->user()->company->name }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SWIFT Code</label>
                        <input type="text"
                               x-model="settings.content.payment_instructions.swift_code"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                               placeholder="e.g., MBBEMYKL">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Payment Information</label>
                    <textarea x-model="settings.content.payment_instructions.additional_info"
                              rows="3"
                              class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                              placeholder="e.g., Please include invoice number in payment reference"></textarea>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between">
                <button @click="resetToDefaults"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Reset to Defaults
                </button>

                <div class="space-x-3">
                    <button @click="previewSettings"
                            class="px-4 py-2 bg-green-100 text-green-700 font-medium rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                        Preview Changes
                    </button>
                    <button @click="saveSettings"
                            class="px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div x-show="message.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed top-4 right-4 z-50 max-w-sm w-full"
         style="display: none;">
        <div class="rounded-md p-4"
             :class="message.type === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg x-show="message.type === 'success'" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <svg x-show="message.type === 'error'" class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium"
                       :class="message.type === 'success' ? 'text-green-800' : 'text-red-800'"
                       x-text="message.text"></p>
                </div>
                <div class="ml-auto pl-3">
                    <button @click="message.show = false"
                            class="inline-flex rounded-md p-1.5 focus:outline-none"
                            :class="message.type === 'success' ? 'text-green-500 hover:bg-green-100' : 'text-red-500 hover:bg-red-100'">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function invoiceSettings() {
    return {
        settings: {
            sections: {
                show_shipping: false,
                show_payment_instructions: true,
                show_signatures: false,
                show_additional_notes: false
            },
            logo: {
                show_company_logo: true,
                logo_position: 'left',
                logo_size: 'medium'
            },
            defaults: {
                payment_terms: 30,
                late_fee_percentage: 1.5,
                currency: 'RM',
                tax_percentage: 6.0,
                show_discount_column: true,
                show_tax_column: true
            },
            content: {
                default_terms: 'Payment is due within the specified payment terms. Late payments may incur additional charges.',
                default_notes: 'Thank you for your business!',
                payment_instructions: {
                    bank_name: '',
                    account_number: '',
                    account_holder: '',
                    swift_code: '',
                    additional_info: 'Please include invoice number in payment reference.'
                }
            }
        },

        message: {
            show: false,
            type: 'success',
            text: ''
        },

        init() {
            this.loadSettings();
        },

        loadSettings() {
            fetch('/invoice-settings/api')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.settings) {
                        this.settings = { ...this.settings, ...data.settings };
                    }
                })
                .catch(error => {
                    console.error('Failed to load settings:', error);
                    this.showMessage('error', 'Failed to load settings');
                });
        },

        saveSettings() {
            fetch('/invoice-settings', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(this.settings)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showMessage('success', 'Settings saved successfully!');
                } else {
                    this.showMessage('error', data.message || 'Failed to save settings');
                }
            })
            .catch(error => {
                console.error('Save error:', error);
                this.showMessage('error', 'Failed to save settings');
            });
        },

        resetToDefaults() {
            if (confirm('Are you sure you want to reset all settings to defaults? This action cannot be undone.')) {
                fetch('/invoice-settings', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ reset_to_defaults: true })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.settings = data.settings;
                        this.showMessage('success', 'Settings reset to defaults successfully!');
                    } else {
                        this.showMessage('error', data.message || 'Failed to reset settings');
                    }
                })
                .catch(error => {
                    console.error('Reset error:', error);
                    this.showMessage('error', 'Failed to reset settings');
                });
            }
        },

        previewSettings() {
            fetch('/invoice-settings/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(this.settings)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showMessage('success', 'Settings preview generated! Check the browser console for details.');
                    console.log('Preview Settings:', data.preview_settings);
                } else {
                    this.showMessage('error', data.message || 'Failed to generate preview');
                }
            })
            .catch(error => {
                console.error('Preview error:', error);
                this.showMessage('error', 'Failed to generate preview');
            });
        },

        showMessage(type, text) {
            this.message = {
                show: true,
                type: type,
                text: text
            };

            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    this.message.show = false;
                }, 5000);
            }
        }
    };
}
</script>
@endsection