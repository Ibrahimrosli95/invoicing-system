@extends('layouts.app')

@section('content')
<script src="{{ asset('js/dateHelper.js') }}"></script>
<div class="min-h-screen bg-gray-50" x-data="invoiceBuilder()">
    <!-- Header Bar -->
    <div class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-40">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('invoices.index') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-lg font-semibold text-gray-900">Create New Invoice</h1>
                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded">DRAFT</span>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" @click="toggleSidebar" class="p-2 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <button type="button" @click="previewPDF" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Preview PDF
                </button>
                <button type="button" @click="saveInvoice" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                    Save Invoice
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex">
        <!-- Floating Sidebar -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             class="fixed right-0 top-16 bottom-0 w-80 bg-white border-l border-gray-200 shadow-lg z-30">

            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-900">Tools & Options</h2>
                    <button @click="sidebarOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="overflow-y-auto p-4 space-y-6">

                <!-- Optional Sections -->
                <div>
                    <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wide mb-3">Optional Sections</h3>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" x-model="optionalSections.show_shipping" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <span class="ml-2 text-sm text-gray-700">Shipping Information</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="optionalSections.show_payment_instructions" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <span class="ml-2 text-sm text-gray-700">Payment Instructions</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="optionalSections.show_signatures" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <span class="ml-2 text-sm text-gray-700">Signature Blocks</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="optionalSections.show_company_logo" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <span class="ml-2 text-sm text-gray-700">Company Logo</span>
                        </label>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div>
                    <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wide mb-3">Quick Actions</h3>
                    <div class="space-y-2">
                        <button @click="addLineItem" type="button"
                                class="w-full px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            + Add Line Item
                        </button>
                        <button @click="applyTemplate" type="button"
                                class="w-full px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Apply Template
                        </button>
                        <button @click="duplicateLastItem" type="button"
                                class="w-full px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Duplicate Last Item
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Preview Area -->
        <div class="flex-1 min-h-screen" :class="sidebarOpen ? 'mr-80' : 'mr-0'">
            <div class="max-w-4xl mx-auto p-8">
                <!-- Invoice Document -->
                <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200">
                    <!-- Document Header -->
                    <div class="px-12 py-8 border-b border-gray-200">
                        <div class="flex justify-between items-start">
                            <!-- Company Info -->
                            <div class="flex-1">
                                <div x-show="optionalSections.show_company_logo" class="mb-4 relative group">
                                    <img :src="companyLogo" alt="Company Logo" class="h-12 cursor-pointer" @click="$refs.logoUpload.click()">
                                    <input type="file" x-ref="logoUpload" @change="handleLogoUpload" accept="image/*" class="hidden">
                                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded" @click="$refs.logoUpload.click()">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ auth()->user()->company->name }}</h1>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <div>{{ auth()->user()->company->address }}</div>
                                    <div>{{ auth()->user()->company->city }}, {{ auth()->user()->company->state }} {{ auth()->user()->company->postal_code }}</div>
                                    <div>{{ auth()->user()->company->phone }} • {{ auth()->user()->company->email }}</div>
                                </div>
                            </div>

                            <!-- Invoice Details -->
                            <div class="text-right">
                                <h2 class="text-3xl font-bold text-blue-600 mb-4">INVOICE</h2>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-end">
                                        <span class="w-24 text-gray-600">Invoice #:</span>
                                        <span class="font-mono" x-text="invoiceNumber">INV-2025-000001</span>
                                    </div>
                                    <div class="flex justify-end">
                                        <span class="w-24 text-gray-600">Date:</span>
                                        <input type="text" x-model="invoiceDateFormatted" @blur="formatInvoiceDate"
                                               placeholder="DD/MM/YYYY"
                                               class="font-mono border-0 bg-transparent text-right p-0 focus:ring-0 w-28">
                                    </div>
                                    <div class="flex justify-end">
                                        <span class="w-24 text-gray-600">Due Date:</span>
                                        <input type="text" x-model="dueDateFormatted" @blur="formatDueDate"
                                               placeholder="DD/MM/YYYY"
                                               class="font-mono border-0 bg-transparent text-right p-0 focus:ring-0 w-28">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="px-12 py-8 bg-gray-50 border-b border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Bill To -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Bill To</h3>

                                <!-- Customer Selection -->
                                <div x-show="!selectedCustomer.name" class="mb-4">
                                    <div class="relative">
                                        <input type="text"
                                               x-model="customerSearch"
                                               @input="searchCustomers"
                                               @focus="showCustomerDropdown = true"
                                               placeholder="Search customers or leads..."
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">

                                        <!-- Customer Dropdown -->
                                        <div x-show="showCustomerDropdown && customerResults.length > 0"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                            <template x-for="customer in customerResults" :key="customer.id">
                                                <div @click="selectCustomer(customer)"
                                                     class="px-4 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                                    <div class="text-sm font-medium text-gray-900" x-text="customer.name"></div>
                                                    <div class="text-xs text-gray-500" x-text="customer.email || customer.phone"></div>
                                                    <div class="flex items-center mt-1">
                                                        <span x-text="customer.type" class="inline-flex px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded"></span>
                                                        <span x-show="customer.is_lead" class="ml-2 inline-flex px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded">From Lead</span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <button @click="showNewCustomerModal = true" type="button"
                                                class="text-sm font-medium text-blue-600 hover:text-blue-800 underline">
                                            + Create New Customer
                                        </button>
                                    </div>
                                </div>

                                <!-- Selected Customer Display -->
                                <div x-show="selectedCustomer.name" class="space-y-1 text-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="font-medium text-gray-900" x-text="selectedCustomer.name"></div>
                                        <button @click="selectedCustomer = {}; customerSearch = ''"
                                                class="text-xs text-blue-600 hover:text-blue-800 underline">
                                            Change Customer
                                        </button>
                                    </div>
                                    <div x-show="selectedCustomer.company" x-text="selectedCustomer.company" class="text-gray-600"></div>
                                    <div x-show="selectedCustomer.address" x-text="selectedCustomer.address" class="text-gray-600"></div>
                                    <div x-show="selectedCustomer.city" class="text-gray-600">
                                        <span x-text="selectedCustomer.city"></span><span x-show="selectedCustomer.state">, <span x-text="selectedCustomer.state"></span></span>
                                        <span x-show="selectedCustomer.postal_code" x-text="selectedCustomer.postal_code"></span>
                                    </div>
                                    <div x-show="selectedCustomer.phone || selectedCustomer.email" class="text-gray-600">
                                        <span x-show="selectedCustomer.phone" x-text="selectedCustomer.phone"></span>
                                        <span x-show="selectedCustomer.phone && selectedCustomer.email"> • </span>
                                        <span x-show="selectedCustomer.email" x-text="selectedCustomer.email"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Ship To (if enabled) -->
                            <div x-show="optionalSections.show_shipping">
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">Ship To</h3>
                                <div class="space-y-2">
                                    <input type="text" x-model="shippingInfo.name" placeholder="Name"
                                           class="w-full text-sm border-0 border-b border-gray-300 bg-transparent p-0 focus:ring-0 focus:border-blue-500">
                                    <input type="text" x-model="shippingInfo.address" placeholder="Address"
                                           class="w-full text-sm border-0 border-b border-gray-300 bg-transparent p-0 focus:ring-0 focus:border-blue-500">
                                    <div class="grid grid-cols-3 gap-2">
                                        <input type="text" x-model="shippingInfo.city" placeholder="City"
                                               class="text-sm border-0 border-b border-gray-300 bg-transparent p-0 focus:ring-0 focus:border-blue-500">
                                        <input type="text" x-model="shippingInfo.state" placeholder="State"
                                               class="text-sm border-0 border-b border-gray-300 bg-transparent p-0 focus:ring-0 focus:border-blue-500">
                                        <input type="text" x-model="shippingInfo.postal_code" placeholder="Postal Code"
                                               class="text-sm border-0 border-b border-gray-300 bg-transparent p-0 focus:ring-0 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Line Items Table -->
                    <div class="px-12 py-8">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Qty</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Rate</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Total</th>
                                    <th class="w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <template x-for="(item, index) in lineItems" :key="index">
                                    <tr>
                                        <td class="px-4 py-3 relative">
                                            <div class="relative">
                                                <input type="text"
                                                       x-model="item.description"
                                                       @input="searchPricingItems(index)"
                                                       @focus="showPricingDropdown[index] = true"
                                                       placeholder="Search pricing book or enter custom description..."
                                                       class="w-full border-0 bg-transparent p-0 text-sm focus:ring-0">

                                                <!-- Pricing Items Dropdown -->
                                                <div x-show="showPricingDropdown[index] && pricingResults[index] && pricingResults[index].length > 0"
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="transform opacity-0 scale-95"
                                                     x-transition:enter-end="transform opacity-100 scale-100"
                                                     class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-48 overflow-y-auto">
                                                    <template x-for="pricingItem in pricingResults[index]" :key="pricingItem.id">
                                                        <div @click="selectPricingItem(index, pricingItem)"
                                                             class="px-4 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                                            <div class="text-sm font-medium text-gray-900" x-text="pricingItem.name"></div>
                                                            <div class="text-xs text-gray-500" x-text="pricingItem.description"></div>
                                                            <div class="flex items-center justify-between mt-1">
                                                                <span class="text-xs text-gray-600" x-text="pricingItem.item_code"></span>
                                                                <span class="text-sm font-medium text-green-600">RM <span x-text="pricingItem.unit_price"></span></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" x-model="item.quantity" @input="calculateTotals"
                                                   class="w-full border-0 bg-transparent p-0 text-sm text-right focus:ring-0" min="1" step="0.01">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" x-model="item.unit_price" @input="calculateTotals"
                                                   class="w-full border-0 bg-transparent p-0 text-sm text-right focus:ring-0" min="0" step="0.01">
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-medium">
                                            RM <span x-text="(item.quantity * item.unit_price).toFixed(2)">0.00</span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button @click="removeLineItem(index)" type="button"
                                                    class="text-red-400 hover:text-red-600">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <!-- Add Item Row -->
                                <tr class="bg-gray-50">
                                    <td colspan="5" class="px-4 py-3">
                                        <button @click="addLineItem" type="button"
                                                class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100">
                                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Add Line Item
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals Section -->
                    <div class="px-12 py-8 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-end">
                            <div class="w-80 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium">RM <span x-text="subtotal.toFixed(2)">0.00</span></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Discount:</span>
                                    <div class="flex items-center">
                                        <input type="number" x-model="discountPercentage" @input="calculateTotals"
                                               class="w-16 border-0 bg-transparent p-0 text-sm text-right focus:ring-0" min="0" max="100" step="0.01">
                                        <span class="ml-1 text-gray-600">%</span>
                                        <span class="ml-2 font-medium">-RM <span x-text="discountAmount.toFixed(2)">0.00</span></span>
                                    </div>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tax (6%):</span>
                                    <span class="font-medium">RM <span x-text="taxAmount.toFixed(2)">0.00</span></span>
                                </div>
                                <div class="flex justify-between text-lg font-semibold border-t border-gray-300 pt-2">
                                    <span>Total:</span>
                                    <span class="text-blue-600">RM <span x-text="total.toFixed(2)">0.00</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Instructions (if enabled) -->
                    <div x-show="optionalSections.show_payment_instructions" class="px-12 py-8 border-t border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Payment Instructions</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                            <div>
                                <div class="space-y-1">
                                    <div><strong>Bank:</strong> Maybank</div>
                                    <div><strong>Account:</strong> 1234567890</div>
                                    <div><strong>Account Name:</strong> {{ auth()->user()->company->name }}</div>
                                </div>
                            </div>
                            <div class="text-gray-600">
                                Please include invoice number in payment reference. Payment is due within the specified payment terms.
                            </div>
                        </div>
                    </div>

                    <!-- Notes and Terms -->
                    <div class="px-12 py-8 border-t border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-semibold text-gray-900">Notes</h3>
                                    <div class="flex items-center space-x-2">
                                        <select @change="loadNotesTemplate" class="text-xs border-gray-300 rounded px-2 py-1">
                                            <option value="">Select template...</option>
                                            <template x-for="template in notesTemplates" :key="template.id">
                                                <option :value="template.content" x-text="template.name"></option>
                                            </template>
                                        </select>
                                        <button @click="saveNotesAsTemplate" type="button"
                                                class="text-xs text-blue-600 hover:text-blue-800 underline">
                                            Save as Template
                                        </button>
                                    </div>
                                </div>
                                <textarea x-model="notes" placeholder="Add any additional notes..."
                                          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          rows="3"></textarea>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Terms & Conditions</h3>
                                <textarea x-model="terms" placeholder="Add terms and conditions..."
                                          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Signatures (if enabled) -->
                    <div x-show="optionalSections.show_signatures" class="px-12 py-8 border-t border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <div class="h-20 border-t border-gray-400 mt-8">
                                    <div class="mt-2 text-sm text-gray-900 text-center font-medium" x-text="representativeName">{{ auth()->user()->name }}</div>
                                    <div class="mt-1 text-sm text-gray-600 text-center" x-text="representativeTitle">Sales Representative</div>
                                    <div class="mt-1 text-xs text-gray-500 text-center">{{ auth()->user()->company->name }}</div>
                                </div>
                            </div>
                            <div>
                                <div class="h-20 border-t border-gray-400 mt-8">
                                    <div class="mt-2 text-sm text-gray-600 text-center">Customer Acceptance</div>
                                    <div class="mt-1 text-sm text-gray-500 text-center">Date: _______________</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Customer Modal -->
    <div x-show="showNewCustomerModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showNewCustomerModal = false"></div>

            <div class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <form @submit.prevent="createCustomer">
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Create New Customer</h3>
                        <p class="mt-1 text-sm text-gray-500">Add a new customer to your database.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name *</label>
                            <input type="text" x-model="newCustomer.name" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" x-model="newCustomer.email"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                <input type="text" x-model="newCustomer.phone"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company</label>
                            <input type="text" x-model="newCustomer.company"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea x-model="newCustomer.address" rows="2"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="showNewCustomerModal = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                            Create Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function invoiceBuilder() {
    return {
        // UI State
        sidebarOpen: true,
        showCustomerDropdown: false,
        showNewCustomerModal: false,

        // Customer Management
        customerSearch: '',
        customerResults: [],
        selectedCustomer: {},

        // Pricing Book Integration
        showPricingDropdown: {},
        pricingResults: {},

        // New Customer Form
        newCustomer: {
            name: '',
            email: '',
            phone: '',
            company: '',
            address: ''
        },

        // Invoice Data
        invoiceNumber: 'INV-2025-000001',
        invoiceDate: new Date().toISOString().split('T')[0],
        invoiceDateFormatted: '',
        dueDate: '',
        dueDateFormatted: '',

        // Optional Sections
        optionalSections: {
            show_shipping: false,
            show_payment_instructions: true,
            show_signatures: false,
            show_company_logo: true
        },

        // Shipping Information
        shippingInfo: {
            name: '',
            address: '',
            city: '',
            state: '',
            postal_code: ''
        },

        // Line Items
        lineItems: [
            { description: '', quantity: 1, unit_price: 0, pricing_item_id: null, item_code: '' }
        ],

        // Financial Calculations
        subtotal: 0,
        discountPercentage: 0,
        discountAmount: 0,
        taxAmount: 0,
        total: 0,

        // Content
        notes: 'Thank you for your business!',
        terms: 'Payment is due within 30 days. Late payments may incur additional charges.',

        // Logo Management
        companyLogo: '{{ auth()->user()->company->logo ?? "/images/logo-placeholder.png" }}',

        // Notes Templates
        notesTemplates: [
            { id: 1, name: 'Standard Thank You', content: 'Thank you for your business! We appreciate your continued trust in our services.' },
            { id: 2, name: 'Payment Reminder', content: 'Please ensure payment is made by the due date to avoid any late fees.' },
            { id: 3, name: 'Warranty Info', content: 'This invoice includes warranty coverage as per our standard terms and conditions.' },
            { id: 4, name: 'Custom Service', content: 'Services provided as per custom specifications discussed.' }
        ],

        // Representative Information
        representativeName: '{{ auth()->user()->name }}',
        representativeTitle: 'Sales Representative',

        init() {
            // Set default dates with DD/MM/YYYY format
            this.invoiceDateFormatted = DateHelper.today();
            const dueDate = new Date();
            dueDate.setDate(dueDate.getDate() + 30);
            this.dueDate = DateHelper.toHtml5Input(dueDate);
            this.dueDateFormatted = DateHelper.format(dueDate);

            // Calculate initial totals
            this.calculateTotals();

            // Load invoice settings
            this.loadInvoiceSettings();

            // Load saved notes templates
            this.loadSavedTemplates();
        },

        // Sidebar Toggle
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        },

        // Date Formatting Methods
        formatInvoiceDate() {
            if (this.invoiceDateFormatted) {
                const parsed = DateHelper.parse(this.invoiceDateFormatted);
                if (parsed) {
                    this.invoiceDate = DateHelper.toHtml5Input(parsed);
                    this.invoiceDateFormatted = DateHelper.format(parsed);
                }
            }
        },

        formatDueDate() {
            if (this.dueDateFormatted) {
                const parsed = DateHelper.parse(this.dueDateFormatted);
                if (parsed) {
                    this.dueDate = DateHelper.toHtml5Input(parsed);
                    this.dueDateFormatted = DateHelper.format(parsed);
                }
            }
        },

        // Customer Search
        searchCustomers() {
            if (this.customerSearch.length < 2) {
                this.customerResults = [];
                return;
            }

            fetch(`/customers/search?q=${encodeURIComponent(this.customerSearch)}`)
                .then(response => response.json())
                .then(data => {
                    this.customerResults = data.customers || [];
                })
                .catch(error => {
                    console.error('Customer search error:', error);
                    this.customerResults = [];
                });
        },

        // Select Customer
        selectCustomer(customer) {
            this.selectedCustomer = customer;
            this.customerSearch = customer.name;
            this.showCustomerDropdown = false;

            // Pre-fill shipping if same as billing
            this.shippingInfo = {
                name: customer.name,
                address: customer.address || '',
                city: customer.city || '',
                state: customer.state || '',
                postal_code: customer.postal_code || ''
            };
        },

        // Create New Customer
        createCustomer() {
            fetch('/customers', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(this.newCustomer)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.selectCustomer(data.customer);
                    this.showNewCustomerModal = false;
                    this.resetNewCustomerForm();
                    // Show success notification
                    this.$dispatch('notify', { type: 'success', message: 'Customer created successfully!' });
                } else {
                    // Show error
                    this.$dispatch('notify', { type: 'error', message: data.message || 'Failed to create customer' });
                }
            })
            .catch(error => {
                console.error('Create customer error:', error);
                this.$dispatch('notify', { type: 'error', message: 'Failed to create customer' });
            });
        },

        resetNewCustomerForm() {
            this.newCustomer = {
                name: '',
                email: '',
                phone: '',
                company: '',
                address: ''
            };
        },

        // Pricing Book Search
        searchPricingItems(index) {
            const item = this.lineItems[index];
            if (item.description.length < 2) {
                this.pricingResults[index] = [];
                return;
            }

            fetch(`/pricing-items/search?q=${encodeURIComponent(item.description)}`)
                .then(response => response.json())
                .then(data => {
                    this.pricingResults[index] = data.items || [];
                })
                .catch(error => {
                    console.error('Pricing search error:', error);
                    this.pricingResults[index] = [];
                });
        },

        selectPricingItem(index, pricingItem) {
            this.lineItems[index].description = pricingItem.name + ' - ' + pricingItem.description;
            this.lineItems[index].unit_price = parseFloat(pricingItem.unit_price);
            this.lineItems[index].pricing_item_id = pricingItem.id;
            this.lineItems[index].item_code = pricingItem.item_code;

            this.showPricingDropdown[index] = false;
            this.calculateTotals();
        },

        // Logo Management
        handleLogoUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Validate file type
            if (!file.type.startsWith('image/')) {
                this.$dispatch('notify', { type: 'error', message: 'Please select an image file' });
                return;
            }

            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                this.$dispatch('notify', { type: 'error', message: 'Image size should be less than 2MB' });
                return;
            }

            // Create preview
            const reader = new FileReader();
            reader.onload = (e) => {
                this.companyLogo = e.target.result;
                this.$dispatch('notify', { type: 'success', message: 'Logo updated successfully' });
            };
            reader.readAsDataURL(file);
        },

        // Notes Template Management
        loadNotesTemplate(event) {
            const selectedTemplate = event.target.value;
            if (selectedTemplate) {
                this.notes = selectedTemplate;
                event.target.selectedIndex = 0; // Reset dropdown
                this.$dispatch('notify', { type: 'success', message: 'Template loaded successfully' });
            }
        },

        saveNotesAsTemplate() {
            if (!this.notes.trim()) {
                this.$dispatch('notify', { type: 'error', message: 'Please enter some notes to save as template' });
                return;
            }

            const templateName = prompt('Enter template name:');
            if (!templateName) return;

            // Add to templates array
            const newTemplate = {
                id: Date.now(),
                name: templateName,
                content: this.notes
            };

            this.notesTemplates.push(newTemplate);

            // Save to localStorage for persistence
            localStorage.setItem('invoice_notes_templates', JSON.stringify(this.notesTemplates));

            this.$dispatch('notify', { type: 'success', message: 'Template saved successfully!' });
        },

        loadSavedTemplates() {
            const saved = localStorage.getItem('invoice_notes_templates');
            if (saved) {
                try {
                    const templates = JSON.parse(saved);
                    // Merge with default templates
                    this.notesTemplates = [...this.notesTemplates, ...templates.filter(t => t.id > 100)];
                } catch (error) {
                    console.error('Error loading saved templates:', error);
                }
            }
        },

        // Line Items Management
        addLineItem() {
            this.lineItems.push({
                description: '',
                quantity: 1,
                unit_price: 0,
                pricing_item_id: null,
                item_code: ''
            });
        },

        removeLineItem(index) {
            if (this.lineItems.length > 1) {
                this.lineItems.splice(index, 1);
                this.calculateTotals();
            }
        },

        duplicateLastItem() {
            const lastItem = this.lineItems[this.lineItems.length - 1];
            this.lineItems.push({ ...lastItem });
        },

        // Financial Calculations
        calculateTotals() {
            this.subtotal = this.lineItems.reduce((sum, item) => {
                return sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0));
            }, 0);

            this.discountAmount = (this.subtotal * parseFloat(this.discountPercentage || 0)) / 100;
            const afterDiscount = this.subtotal - this.discountAmount;
            this.taxAmount = afterDiscount * 0.06; // 6% tax
            this.total = afterDiscount + this.taxAmount;
        },

        // Load Invoice Settings
        loadInvoiceSettings() {
            fetch('/invoice-settings')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.settings) {
                        // Apply default settings
                        this.optionalSections = { ...this.optionalSections, ...data.settings.optional_sections };
                        this.notes = data.settings.default_notes || this.notes;
                        this.terms = data.settings.default_terms || this.terms;
                    }
                })
                .catch(error => {
                    console.error('Failed to load invoice settings:', error);
                });
        },

        // Actions
        previewPDF() {
            // Generate preview URL
            const invoiceData = this.getInvoiceData();
            window.open('/invoices/preview?' + new URLSearchParams({
                data: JSON.stringify(invoiceData)
            }), '_blank');
        },

        saveInvoice() {
            const invoiceData = this.getInvoiceData();

            fetch('/invoices', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(invoiceData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.$dispatch('notify', { type: 'success', message: 'Invoice saved successfully!' });
                    // Redirect to invoice view
                    setTimeout(() => {
                        window.location.href = `/invoices/${data.invoice.id}`;
                    }, 1000);
                } else {
                    this.$dispatch('notify', { type: 'error', message: data.message || 'Failed to save invoice' });
                }
            })
            .catch(error => {
                console.error('Save invoice error:', error);
                this.$dispatch('notify', { type: 'error', message: 'Failed to save invoice' });
            });
        },

        getInvoiceData() {
            return {
                customer_id: this.selectedCustomer.id,
                invoice_date: this.invoiceDate,
                due_date: this.dueDate,
                subtotal: this.subtotal,
                discount_percentage: this.discountPercentage,
                discount_amount: this.discountAmount,
                tax_percentage: 6,
                tax_amount: this.taxAmount,
                total: this.total,
                notes: this.notes,
                terms_conditions: this.terms,
                optional_sections: this.optionalSections,
                shipping_info: this.optionalSections.show_shipping ? this.shippingInfo : null,
                items: this.lineItems.filter(item => item.description.trim() !== '')
            };
        },

        applyTemplate() {
            // TODO: Implement template application
            this.$dispatch('notify', { type: 'info', message: 'Template feature coming soon!' });
        }
    };
}
</script>
@endsection