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

            <!-- Table Columns Configuration -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Table Columns</h2>
                <p class="text-sm text-gray-600 mb-4">Customize the column labels, visibility, and order for the invoice items table.</p>

                <div class="space-y-3">
                    <template x-for="(column, index) in settings.columns" :key="column.key">
                        <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-md border border-gray-200">
                            <!-- Drag Handle (visual only, ordering via inputs) -->
                            <div class="flex-shrink-0 text-gray-400 cursor-move">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V4a2 2 0 00-2-2H7zm3 14a1 1 0 100-2 1 1 0 000 2zm0-4a1 1 0 100-2 1 1 0 000 2zm0-4a1 1 0 100-2 1 1 0 000 2z"/>
                                </svg>
                            </div>

                            <!-- Order -->
                            <div class="flex-shrink-0 w-16">
                                <input type="number"
                                       x-model="column.order"
                                       min="1"
                                       class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       @change="sortColumns()">
                            </div>

                            <!-- Visibility Toggle -->
                            <div class="flex-shrink-0">
                                <input type="checkbox"
                                       x-model="column.visible"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            </div>

                            <!-- Column Key (readonly badge) -->
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
                                      x-text="column.key"></span>
                            </div>

                            <!-- Label Input -->
                            <div class="flex-1">
                                <input type="text"
                                       x-model="column.label"
                                       maxlength="50"
                                       class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       placeholder="Column Label">
                            </div>
                        </div>
                    </template>
                </div>

                <div class="mt-4 text-xs text-gray-500">
                    <p><strong>Tip:</strong> Adjust the order numbers to reorder columns. Uncheck the box to hide a column from PDF output.</p>
                </div>
            </div>

            <!-- Invoice Color Theme -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Invoice Color Theme</h2>
                <p class="text-sm text-gray-600 mb-6">Customize the color palette used in your PDF invoices to match your brand identity.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Background & Structure Colors -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-medium text-gray-900 border-b border-gray-200 pb-2">Background & Structure</h3>

                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <label class="block text-xs font-medium text-gray-700 w-24">Background:</label>
                                <input type="color"
                                       x-model="settings.appearance.background_color"
                                       @change="normalizeColor('background_color')"
                                       class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text"
                                       x-model="settings.appearance.background_color"
                                       @input="normalizeColor('background_color')"
                                       class="w-20 text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       placeholder="#ffffff">
                            </div>

                            <div class="flex items-center space-x-3">
                                <label class="block text-xs font-medium text-gray-700 w-24">Borders:</label>
                                <input type="color"
                                       x-model="settings.appearance.border_color"
                                       @change="normalizeColor('border_color')"
                                       class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text"
                                       x-model="settings.appearance.border_color"
                                       @input="normalizeColor('border_color')"
                                       class="w-20 text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       placeholder="#e5e7eb">
                            </div>

                            <div class="flex items-center space-x-3">
                                <label class="block text-xs font-medium text-gray-700 w-24">Alt Rows:</label>
                                <input type="color"
                                       x-model="settings.appearance.table_row_even"
                                       @change="normalizeColor('table_row_even')"
                                       class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text"
                                       x-model="settings.appearance.table_row_even"
                                       @input="normalizeColor('table_row_even')"
                                       class="w-20 text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       placeholder="#f8fafc">
                            </div>
                        </div>
                    </div>

                    <!-- Text Colors -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-medium text-gray-900 border-b border-gray-200 pb-2">Text Colors</h3>

                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <label class="block text-xs font-medium text-gray-700 w-24">Headings:</label>
                                <input type="color"
                                       x-model="settings.appearance.heading_color"
                                       @change="normalizeColor('heading_color')"
                                       class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text"
                                       x-model="settings.appearance.heading_color"
                                       @input="normalizeColor('heading_color')"
                                       class="w-20 text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       placeholder="#111827">
                            </div>

                            <div class="flex items-center space-x-3">
                                <label class="block text-xs font-medium text-gray-700 w-24">Subheadings:</label>
                                <input type="color"
                                       x-model="settings.appearance.subheading_color"
                                       @change="normalizeColor('subheading_color')"
                                       class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text"
                                       x-model="settings.appearance.subheading_color"
                                       @input="normalizeColor('subheading_color')"
                                       class="w-20 text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       placeholder="#1f2937">
                            </div>

                            <div class="flex items-center space-x-3">
                                <label class="block text-xs font-medium text-gray-700 w-24">Body Text:</label>
                                <input type="color"
                                       x-model="settings.appearance.text_color"
                                       @change="normalizeColor('text_color')"
                                       class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text"
                                       x-model="settings.appearance.text_color"
                                       @input="normalizeColor('text_color')"
                                       class="w-20 text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       placeholder="#111827">
                            </div>

                            <div class="flex items-center space-x-3">
                                <label class="block text-xs font-medium text-gray-700 w-24">Muted Text:</label>
                                <input type="color"
                                       x-model="settings.appearance.muted_text_color"
                                       @change="normalizeColor('muted_text_color')"
                                       class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text"
                                       x-model="settings.appearance.muted_text_color"
                                       @input="normalizeColor('muted_text_color')"
                                       class="w-20 text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       placeholder="#6b7280">
                            </div>
                        </div>
                    </div>

                    <!-- Accent Colors -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-medium text-gray-900 border-b border-gray-200 pb-2">Accent & Branding</h3>

                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <label class="block text-xs font-medium text-gray-700 w-24">Primary:</label>
                                <input type="color"
                                       x-model="settings.appearance.accent_color"
                                       @change="normalizeColor('accent_color')"
                                       class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text"
                                       x-model="settings.appearance.accent_color"
                                       @input="normalizeColor('accent_color')"
                                       class="w-20 text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       placeholder="#1d4ed8">
                            </div>

                            <div class="flex items-center space-x-3">
                                <label class="block text-xs font-medium text-gray-700 w-24">On Primary:</label>
                                <input type="color"
                                       x-model="settings.appearance.accent_text_color"
                                       @change="normalizeColor('accent_text_color')"
                                       class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text"
                                       x-model="settings.appearance.accent_text_color"
                                       @input="normalizeColor('accent_text_color')"
                                       class="w-20 text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       placeholder="#ffffff">
                            </div>
                        </div>
                    </div>

                    <!-- Table Colors -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-medium text-gray-900 border-b border-gray-200 pb-2">Table Headers</h3>

                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <label class="block text-xs font-medium text-gray-700 w-24">Header BG:</label>
                                <input type="color"
                                       x-model="settings.appearance.table_header_background"
                                       @change="normalizeColor('table_header_background')"
                                       class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text"
                                       x-model="settings.appearance.table_header_background"
                                       @input="normalizeColor('table_header_background')"
                                       class="w-20 text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       placeholder="#1d4ed8">
                            </div>

                            <div class="flex items-center space-x-3">
                                <label class="block text-xs font-medium text-gray-700 w-24">Header Text:</label>
                                <input type="color"
                                       x-model="settings.appearance.table_header_text"
                                       @change="normalizeColor('table_header_text')"
                                       class="w-12 h-8 border border-gray-300 rounded cursor-pointer">
                                <input type="text"
                                       x-model="settings.appearance.table_header_text"
                                       @input="normalizeColor('table_header_text')"
                                       class="w-20 text-xs border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                       placeholder="#ffffff">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Color Reset Button -->
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <button @click="resetColors"
                            class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Reset Colors to Default
                    </button>
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
                    <button @click="previewPDF"
                            class="px-4 py-2 bg-green-100 text-green-700 font-medium rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <svg class="inline-block w-4 h-4 mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Preview Invoice PDF
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
            appearance: {
                background_color: '#ffffff',
                border_color: '#e5e7eb',
                heading_color: '#111827',
                subheading_color: '#1f2937',
                text_color: '#111827',
                muted_text_color: '#6b7280',
                accent_color: '#1d4ed8',
                accent_text_color: '#ffffff',
                table_header_background: '#1d4ed8',
                table_header_text: '#ffffff',
                table_row_even: '#f8fafc'
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
            },
            columns: [
                { key: 'sl', label: 'Sl.', visible: true, order: 1 },
                { key: 'description', label: 'Description', visible: true, order: 2 },
                { key: 'quantity', label: 'Qty', visible: true, order: 3 },
                { key: 'rate', label: 'Rate', visible: true, order: 4 },
                { key: 'amount', label: 'Amount', visible: true, order: 5 }
            ]
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
                        // Deep merge to ensure all appearance settings are loaded
                        this.settings.sections = { ...this.settings.sections, ...data.settings.optional_sections };
                        this.settings.logo = { ...this.settings.logo, ...data.settings.logo_settings };
                        this.settings.appearance = { ...this.settings.appearance, ...data.settings.appearance };
                        this.settings.defaults = { ...this.settings.defaults, ...data.settings.defaults };
                        this.settings.content = {
                            ...this.settings.content,
                            default_terms: data.settings.default_terms,
                            default_notes: data.settings.default_notes,
                            payment_instructions: { ...this.settings.content.payment_instructions, ...data.settings.payment_instructions }
                        };
                    }
                })
                .catch(error => {
                    console.error('Failed to load settings:', error);
                    this.showMessage('error', 'Failed to load settings');
                });

            // Load columns configuration
            fetch('/invoice-settings/columns')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.columns) {
                        this.settings.columns = data.columns;
                    }
                })
                .catch(error => {
                    console.error('Failed to load columns:', error);
                });
        },

        sortColumns() {
            // Sort columns by order property
            this.settings.columns.sort((a, b) => a.order - b.order);
        },

        normalizeColor(colorKey) {
            let color = this.settings.appearance[colorKey];

            // Convert to uppercase and ensure it starts with #
            if (color && !color.startsWith('#')) {
                color = '#' + color;
            }

            // Validate hex format and normalize to #RRGGBB
            if (color && /^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/.test(color)) {
                if (color.length === 4) {
                    // Convert #RGB to #RRGGBB
                    color = '#' + color[1] + color[1] + color[2] + color[2] + color[3] + color[3];
                }
                this.settings.appearance[colorKey] = color.toUpperCase();
            }
        },

        resetColors() {
            if (confirm('Reset all colors to default values?')) {
                this.settings.appearance = {
                    background_color: '#FFFFFF',
                    border_color: '#E5E7EB',
                    heading_color: '#111827',
                    subheading_color: '#1F2937',
                    text_color: '#111827',
                    muted_text_color: '#6B7280',
                    accent_color: '#1D4ED8',
                    accent_text_color: '#FFFFFF',
                    table_header_background: '#1D4ED8',
                    table_header_text: '#FFFFFF',
                    table_row_even: '#F8FAFC'
                };
                this.showMessage('success', 'Colors reset to defaults!');
            }
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

        previewPDF() {
            // Create a form and submit it to open PDF in new tab
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/invoice-settings/preview-pdf';
            form.target = '_blank';

            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfInput);

            // Add settings data
            const settingsInput = document.createElement('input');
            settingsInput.type = 'hidden';
            settingsInput.name = 'settings';
            settingsInput.value = JSON.stringify(this.settings);
            form.appendChild(settingsInput);

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);

            this.showMessage('success', 'Opening PDF preview in new tab...');
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