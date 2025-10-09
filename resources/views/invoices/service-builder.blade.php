@extends('layouts.app')

@section('content')
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
            <div class="flex items-center space-x-3 relative z-50">
                <button type="button" @click="previewPDF" class="relative z-50 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                    Preview PDF
                </button>
                <button type="button" @click="saveInvoice" class="relative z-50 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer">
                    Save Invoice
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div>
        <!-- Document Preview Area -->
        <div class="flex-1 min-h-screen">
            <div class="max-w-4xl mx-auto px-4 md:px-6 lg:px-8 py-6">
                <!-- Invoice Document -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                    <!-- Row 1: Invoice Title at Top Center -->
                    <div class="px-6 md:px-8 lg:px-12 py-6">
                        <div class="text-center mb-6">
                            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 tracking-wide">INVOICE</h1>
                        </div>

                        <!-- Row 2: Sender Details and Company Logo -->
                        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start mb-6">
                            <!-- Sender Details - Left -->
                            <div class="w-full lg:w-2/3 pr-0 lg:pr-12 space-y-4 mb-4 lg:mb-0 order-2 lg:order-1">
                                <h2 class="text-2xl font-bold text-blue-600 leading-tight" x-text="currentBrand.name || 'Company Name'">
                                </h2>
                                <div class="text-sm text-gray-700 space-y-2 leading-relaxed">
                                    <div class="font-medium" x-text="currentBrand.address || '123 Business Street'"></div>
                                    <div>
                                        <span x-text="currentBrand.city || 'City'"></span><span x-show="currentBrand.city">, </span><span x-text="currentBrand.state || 'State'"></span> <span x-text="currentBrand.postal_code || '12345'"></span>
                                    </div>
                                    <div class="pt-2 space-y-1">
                                        <div>
                                            <span class="inline-block w-16 text-gray-500 text-xs uppercase tracking-wide">Phone:</span>
                                            <span class="font-medium" x-text="currentBrand.phone || '+60 12-345 6789'"></span>
                                        </div>
                                        <div>
                                            <span class="inline-block w-16 text-gray-500 text-xs uppercase tracking-wide">Email:</span>
                                            <span class="font-medium" x-text="currentBrand.email || 'info@company.com'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Company Logo - Right -->
                            <div class="flex flex-col items-center lg:items-end w-full lg:w-1/3 order-1 lg:order-2" x-show="optionalSections.show_company_logo">
                                <!-- Logo Section -->
                                <div class="relative group mb-4">
                                    <img :src="selectedLogoUrl" alt="Company Logo" class="h-20 cursor-pointer" @click="showLogoSelector = true">
                                    <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded" @click="showLogoSelector = true">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Logo Action Buttons -->
                                <div class="flex space-x-2 mb-4">
                                    <button type="button" @click="showLogoSelector = true" class="px-3 py-1 text-xs font-medium text-amber-700 bg-amber-100 border border-amber-200 rounded-full hover:bg-amber-200 transition-colors">
                                        Choose Logo
                                    </button>
                                    <a href="{{ route('logo-bank.index') }}" target="_blank" class="px-3 py-1 text-xs font-medium text-blue-700 bg-blue-100 border border-blue-200 rounded-full hover:bg-blue-200 transition-colors">
                                        Manage Logos
                                    </a>
                                </div>

                                <!-- Company Brand Selector -->
                                <div class="w-full">
                                    <label class="block text-xs font-medium text-gray-700 mb-2 text-center lg:text-right">Company Brand</label>
                                    <select x-model="selectedBrandId" class="block w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Use Default</option>
                                        @foreach($companyBrands as $brand)
                                            <option value="{{ $brand->id }}" {{ $brand->is_default ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gap between rows -->
                    <div class="h-2 bg-gray-50"></div>

                    <!-- Row 3: Customer Billing Details and Invoice Details -->
                    <div class="px-4 md:px-6 lg:px-8 py-6 bg-white border border-gray-200 rounded-lg mx-2 md:mx-4 lg:mx-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Customer Billing Details - Left -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-6">Bill To</h3>
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
                                                    <div x-show="customer.company_name" class="text-xs text-gray-600" x-text="customer.company_name"></div>
                                                    <div class="text-xs text-gray-500" x-text="customer.email || customer.phone"></div>
                                                    <div class="flex items-center mt-1">
                                                        <span x-text="customer.customer_segment || 'Customer'" class="inline-flex px-2 py-0.5 text-xs font-medium bg-purple-100 text-purple-800 rounded"></span>
                                                        <span x-show="customer.is_new_customer" class="ml-2 inline-flex px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded">New</span>
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
                                    <div x-show="selectedCustomer.company_name" x-text="selectedCustomer.company_name" class="text-gray-600"></div>
                                    <div x-show="selectedCustomer.address" x-text="selectedCustomer.address" class="text-gray-600"></div>
                                    <div x-show="selectedCustomer.city || selectedCustomer.state || selectedCustomer.postal_code" class="text-gray-600">
                                        <span x-show="selectedCustomer.city" x-text="selectedCustomer.city"></span><span x-show="selectedCustomer.city && (selectedCustomer.state || selectedCustomer.postal_code)">, </span><span x-show="selectedCustomer.state" x-text="selectedCustomer.state"></span>
                                        <span x-show="selectedCustomer.postal_code"> <span x-text="selectedCustomer.postal_code"></span></span>
                                    </div>
                                    <div x-show="selectedCustomer.phone || selectedCustomer.email" class="text-gray-600">
                                        <span x-show="selectedCustomer.phone" x-text="selectedCustomer.phone"></span>
                                        <span x-show="selectedCustomer.phone && selectedCustomer.email"> • </span>
                                        <span x-show="selectedCustomer.email" x-text="selectedCustomer.email"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Ship To - Middle -->
                            <div x-show="optionalSections.show_shipping">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-semibold text-gray-900">Ship To</h3>
                                    <label class="flex items-center text-sm text-gray-600">
                                        <input type="checkbox" x-model="shippingSameAsBilling" @change="toggleShippingSameAsBilling"
                                               class="mr-2 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        Same as billing
                                    </label>
                                </div>
                                <div x-show="!shippingSameAsBilling" class="space-y-3">
                                    <input type="text" x-model="shippingInfo.name" placeholder="Name"
                                           class="w-full text-sm border-0 border-b border-gray-300 bg-transparent py-2 focus:ring-0 focus:border-blue-500">
                                    <input type="text" x-model="shippingInfo.address" placeholder="Address"
                                           class="w-full text-sm border-0 border-b border-gray-300 bg-transparent py-2 focus:ring-0 focus:border-blue-500">
                                    <div class="grid grid-cols-1 gap-2">
                                        <input type="text" x-model="shippingInfo.city" placeholder="City"
                                               class="text-sm border-0 border-b border-gray-300 bg-transparent py-2 focus:ring-0 focus:border-blue-500">
                                        <input type="text" x-model="shippingInfo.state" placeholder="State"
                                               class="text-sm border-0 border-b border-gray-300 bg-transparent py-2 focus:ring-0 focus:border-blue-500">
                                        <input type="text" x-model="shippingInfo.postal_code" placeholder="Postal Code"
                                               class="text-sm border-0 border-b border-gray-300 bg-transparent py-2 focus:ring-0 focus:border-blue-500">
                                    </div>
                                </div>
                                <div x-show="shippingSameAsBilling" class="space-y-2 text-sm text-gray-700">
                                    <div class="font-medium" x-text="selectedCustomer.name"></div>
                                    <div x-show="selectedCustomer.company_name" x-text="selectedCustomer.company_name"></div>
                                    <div x-show="selectedCustomer.address" x-text="selectedCustomer.address"></div>
                                    <div x-show="selectedCustomer.city || selectedCustomer.state || selectedCustomer.postal_code">
                                        <span x-show="selectedCustomer.city" x-text="selectedCustomer.city"></span><span x-show="selectedCustomer.city && (selectedCustomer.state || selectedCustomer.postal_code)">, </span><span x-show="selectedCustomer.state" x-text="selectedCustomer.state"></span>
                                        <span x-show="selectedCustomer.postal_code"> <span x-text="selectedCustomer.postal_code"></span></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Details - Right -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-6">Invoice Details</h3>
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600">Invoice #:</span>
                                        <span class="text-sm font-mono font-semibold" x-text="invoiceNumber">INV-2025-000001</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600">Invoice Date:</span>
                                        <input type="text" x-model="invoiceDateDisplay" @input="updateInvoiceDate"
                                               placeholder="DD/MM/YYYY" maxlength="10"
                                               class="text-sm font-mono font-semibold border-0 border-b border-gray-300 bg-transparent text-right p-0 focus:ring-0 focus:border-blue-500 w-28">
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600">Due Date:</span>
                                        <input type="text" x-model="dueDateDisplay" @input="updateDueDate"
                                               placeholder="DD/MM/YYYY" maxlength="10"
                                               class="text-sm font-mono font-semibold border-0 border-b border-gray-300 bg-transparent text-right p-0 focus:ring-0 focus:border-blue-500 w-28">
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600">PO Number:</span>
                                        <input type="text" placeholder="Optional"
                                               class="text-sm font-mono font-semibold border-0 border-b border-gray-300 bg-transparent text-right p-0 focus:ring-0 focus:border-blue-500 w-28">
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600">Terms:</span>
                                        <select class="text-sm font-medium border-0 border-b border-gray-300 bg-transparent text-right p-0 focus:ring-0 focus:border-blue-500">
                                            <option>Net 30</option>
                                            <option>Net 15</option>
                                            <option>Due on Receipt</option>
                                            <option>Net 60</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gap between rows -->
                    <div class="h-2 bg-gray-50"></div>

                    <!-- Service Sections -->
                    <div class="px-4 md:px-6 lg:px-8 py-6 bg-white border border-gray-200 rounded-lg mx-2 md:mx-4 lg:mx-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Service Items</h3>
                            <div class="flex gap-2">
                                <button @click="openTemplateModal" type="button"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition duration-150">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Select Service Template
                                </button>
                                <button @click="toggleManualMode" type="button"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition duration-150">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    <span x-text="manualMode ? 'Template Mode' : 'Manual Mode'"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Service Sections - Desktop -->
                        <div class="hidden md:block space-y-6">
                            <!-- Sections Loop -->
                            <template x-for="(section, sectionIndex) in sections" :key="section.id">
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <!-- Section Header -->
                                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4 border-b border-blue-200">
                                        <div class="flex justify-between items-start gap-4">
                                            <!-- Drag Handle -->
                                            <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600 flex-shrink-0 pt-1"
                                                 title="Drag to reorder sections">
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                                </svg>
                                            </div>

                                            <div class="flex-1">
                                                <input type="text" x-model="section.name"
                                                       placeholder="Section Name (e.g., Phase 1: Installation)"
                                                       class="w-full text-lg font-semibold border-0 bg-transparent text-gray-900 placeholder-gray-400 focus:ring-0 p-0">
                                                <textarea x-model="section.description"
                                                          placeholder="Section description (optional)"
                                                          rows="1"
                                                          class="mt-2 w-full text-sm border-0 bg-transparent text-gray-600 placeholder-gray-400 focus:ring-0 p-0 resize-none"></textarea>
                                            </div>

                                            <button @click="removeSection(sectionIndex)" type="button"
                                                    x-show="sections.length > 1"
                                                    class="text-red-400 hover:text-red-600 flex-shrink-0">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Section Items Table -->
                                    <table class="w-full">
                                        <thead class="bg-gray-50 border-b border-gray-200">
                                            <tr>
                                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center w-12">SI</th>
                                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-left">Details</th>
                                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center w-24">Unit</th>
                                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center w-24">Quantity</th>
                                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-right w-32">Rate (RM)</th>
                                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-right w-40">Amount (RM)</th>
                                                <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase text-center w-16">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <template x-for="(item, itemIndex) in section.items" :key="item.id">
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-6 py-3 text-center text-sm text-gray-600" x-text="itemIndex + 1"></td>
                                                    <!-- Details -->
                                                    <td class="px-6 py-3">
                                                        <input type="text" x-model="item.description"
                                                               placeholder="Item description..."
                                                               class="w-full border-0 bg-transparent text-sm focus:ring-0 p-0">
                                                    </td>
                                                    <!-- Unit -->
                                                    <td class="px-6 py-3">
                                                        <input type="text" x-model="item.unit"
                                                               placeholder="Nos"
                                                               class="w-full border-0 bg-transparent text-sm text-center focus:ring-0 p-0">
                                                    </td>
                                                    <!-- Quantity -->
                                                    <td class="px-6 py-3">
                                                        <input type="number" x-model="item.quantity"
                                                               @input="recalculateItemAmount(sectionIndex, itemIndex)"
                                                               class="w-full border-0 bg-transparent text-sm text-center focus:ring-0 p-0"
                                                               min="0.01" step="0.01">
                                                    </td>
                                                    <!-- Rate -->
                                                    <td class="px-6 py-3">
                                                        <input type="number" x-model="item.unit_price"
                                                               @input="recalculateItemAmount(sectionIndex, itemIndex)"
                                                               class="w-full border-0 bg-transparent text-sm text-right focus:ring-0 p-0"
                                                               min="0" step="0.01">
                                                    </td>
                                                    <!-- Amount (Editable with Override Indicator) -->
                                                    <td class="px-6 py-3">
                                                        <div class="flex items-center justify-end gap-2">
                                                            <input type="number" x-model="item.amount"
                                                                   @input="handleAmountOverride(sectionIndex, itemIndex, $event.target.value)"
                                                                   :class="item.amount_manually_edited ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-transparent'"
                                                                   class="w-24 border-0 text-sm text-right focus:ring-0 p-0"
                                                                   min="0" step="0.01">
                                                            <!-- Reset Override Button -->
                                                            <button type="button"
                                                                    x-show="item.amount_manually_edited"
                                                                    @click="resetAmountOverride(sectionIndex, itemIndex)"
                                                                    class="text-amber-600 hover:text-amber-700"
                                                                    title="Reset to calculated amount">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <!-- Action -->
                                                    <td class="px-6 py-3 text-center">
                                                        <button @click="removeItemFromSection(sectionIndex, itemIndex)" type="button"
                                                                x-show="section.items.length > 1"
                                                                class="text-red-400 hover:text-red-600">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>

                                            <!-- Add Item Row -->
                                            <tr class="bg-gray-50">
                                                <td colspan="7" class="px-6 py-3">
                                                    <button @click="addItemToSection(sectionIndex)" type="button"
                                                            class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-700">
                                                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                        </svg>
                                                        Add Item
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>

                                        <!-- Section Subtotal -->
                                        <tfoot class="bg-blue-50 border-t-2 border-blue-200">
                                            <tr>
                                                <td colspan="5" class="px-6 py-3 text-right text-sm font-semibold text-gray-900">
                                                    Section Subtotal:
                                                </td>
                                                <td class="px-6 py-3 text-right text-sm font-bold text-blue-600">
                                                    RM <span x-text="getSectionSubtotal(section).toFixed(2)">0.00</span>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </template>

                            <!-- Add Section Button -->
                            <div x-show="manualMode || sections.length === 0">
                                <button @click="addSection" type="button"
                                        class="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-blue-600 bg-blue-50 border-2 border-dashed border-blue-300 rounded-lg hover:bg-blue-100 hover:border-blue-400">
                                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add New Section
                                </button>
                            </div>
                        </div>

                        <!-- Service Sections - Mobile -->
                        <div class="md:hidden space-y-6">
                            <!-- Sections Loop -->
                            <template x-for="(section, sectionIndex) in sections" :key="section.id">
                                <div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
                                    <!-- Section Header -->
                                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-blue-200">
                                        <div class="flex justify-between items-start gap-3">
                                            <!-- Drag Handle -->
                                            <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600 flex-shrink-0 pt-1"
                                                 title="Drag to reorder sections">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                                </svg>
                                            </div>

                                            <div class="flex-1">
                                                <input type="text" x-model="section.name"
                                                       placeholder="Section Name"
                                                       class="w-full text-base font-semibold border-0 bg-transparent text-gray-900 placeholder-gray-400 focus:ring-0 p-0">
                                                <textarea x-model="section.description"
                                                          placeholder="Description (optional)"
                                                          rows="1"
                                                          class="mt-1 w-full text-sm border-0 bg-transparent text-gray-600 placeholder-gray-400 focus:ring-0 p-0 resize-none"></textarea>
                                            </div>

                                            <button @click="removeSection(sectionIndex)" type="button"
                                                    x-show="sections.length > 1"
                                                    class="text-red-400 hover:text-red-600 flex-shrink-0">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Section Items -->
                                    <div class="px-4 py-3 space-y-3">
                                        <template x-for="(item, itemIndex) in section.items" :key="item.id">
                                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="text-xs font-medium text-gray-500" x-text="`Item ${itemIndex + 1}`"></span>
                                                    <button @click="removeItemFromSection(sectionIndex, itemIndex)" type="button"
                                                            x-show="section.items.length > 1"
                                                            class="text-red-400 hover:text-red-600">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                <div class="space-y-2">
                                                    <!-- Description -->
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Details</label>
                                                        <input type="text" x-model="item.description"
                                                               placeholder="Item description..."
                                                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                                    </div>

                                                    <!-- Unit, Quantity and Rate -->
                                                    <div class="grid grid-cols-3 gap-2">
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Unit</label>
                                                            <input type="text" x-model="item.unit"
                                                                   placeholder="Nos"
                                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-center focus:ring-blue-500 focus:border-blue-500">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Qty</label>
                                                            <input type="number" x-model="item.quantity"
                                                                   @input="recalculateItemAmount(sectionIndex, itemIndex)"
                                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-center focus:ring-blue-500 focus:border-blue-500"
                                                                   min="0.01" step="0.01">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Rate</label>
                                                            <input type="number" x-model="item.unit_price"
                                                                   @input="recalculateItemAmount(sectionIndex, itemIndex)"
                                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-right focus:ring-blue-500 focus:border-blue-500"
                                                                   min="0" step="0.01">
                                                        </div>
                                                    </div>

                                                    <!-- Amount (Editable with Override Indicator) -->
                                                    <div class="pt-2 border-t border-gray-200">
                                                        <div class="flex items-center justify-between gap-2">
                                                            <label class="text-xs font-medium text-gray-700">Amount (RM):</label>
                                                            <div class="flex items-center gap-2">
                                                                <input type="number" x-model="item.amount"
                                                                       @input="handleAmountOverride(sectionIndex, itemIndex, $event.target.value)"
                                                                       :class="item.amount_manually_edited ? 'bg-amber-50 border-amber-300 text-amber-900' : 'border-gray-300'"
                                                                       class="w-24 border rounded-md px-3 py-2 text-sm text-right focus:ring-blue-500 focus:border-blue-500"
                                                                       min="0" step="0.01">
                                                                <!-- Reset Override Button -->
                                                                <button type="button"
                                                                        x-show="item.amount_manually_edited"
                                                                        @click="resetAmountOverride(sectionIndex, itemIndex)"
                                                                        class="text-amber-600 hover:text-amber-700 flex-shrink-0"
                                                                        title="Reset to calculated amount">
                                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <!-- Override Indicator Text -->
                                                        <div x-show="item.amount_manually_edited" class="mt-1 text-xs text-amber-600 flex items-center gap-1">
                                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                            </svg>
                                                            Manually edited
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Add Item Button -->
                                        <button @click="addItemToSection(sectionIndex)" type="button"
                                                class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100">
                                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            Add Item
                                        </button>

                                        <!-- Section Subtotal -->
                                        <div class="bg-blue-50 rounded-lg px-3 py-2 border border-blue-200">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-semibold text-gray-900">Section Total:</span>
                                                <span class="text-sm font-bold text-blue-600">
                                                    RM <span x-text="getSectionSubtotal(section).toFixed(2)">0.00</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Add Section Button -->
                            <div x-show="manualMode || sections.length === 0">
                                <button @click="addSection" type="button"
                                        class="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-blue-600 bg-blue-50 border-2 border-dashed border-blue-300 rounded-lg hover:bg-blue-100 hover:border-blue-400">
                                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add New Section
                                </button>
                            </div>
                        </div>

                    <!-- Gap between rows -->
                    <div class="h-2 bg-gray-50"></div>

                    <!-- Totals Section -->
                    <div class="px-4 md:px-6 lg:px-8 py-6">
                        <div class="flex flex-col lg:grid lg:grid-cols-2 gap-4 md:gap-6 mt-4">
                            <!-- Left side: Notes/Terms/Payment Instructions -->
                            <div class="space-y-6 order-2 lg:order-1">
                                <!-- Payment Instructions Card -->
                                <div x-show="optionalSections.show_payment_instructions" class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                                    <div class="flex items-center justify-between bg-gray-200 px-5 py-4">
                                        <span class="font-medium text-gray-900">Payment Instructions</span>
                                        <div class="flex items-center space-x-2">
                                            <button @click="loadPaymentInstructionTemplates()"
                                                    class="bg-blue-100 hover:bg-blue-200 border border-blue-300 rounded-full px-3 py-1 text-xs text-blue-700 transition-colors"
                                                    title="Load existing template">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                Load
                                            </button>
                                            <button @click="saveAsDefault('payment_instructions', paymentInstructions)"
                                                    class="bg-yellow-100 hover:bg-yellow-200 border border-yellow-300 rounded-full px-3 py-1 text-xs text-yellow-700 transition-colors"
                                                    title="Set current content as default template">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                </svg>
                                                Set Default
                                            </button>
                                            <span class="text-xs text-gray-600">(Optional)</span>
                                        </div>
                                    </div>
                                    <div class="px-5 py-4">
                                        <textarea x-model="paymentInstructions" placeholder="Add payment instructions..."
                                                  class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                                  rows="4"></textarea>
                                    </div>
                                </div>

                                <!-- Terms Card -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                                    <div class="flex items-center justify-between bg-gray-200 px-5 py-4">
                                        <span class="font-medium text-gray-900">Terms & Conditions</span>
                                        <div class="flex items-center space-x-2">
                                            <button @click="loadTermsTemplates()"
                                                    class="bg-amber-100 hover:bg-amber-200 border border-amber-300 rounded-full px-3 py-1 text-xs text-amber-700 transition-colors"
                                                    title="Load existing template">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                Load
                                            </button>
                                            <button @click="saveAsDefault('terms', terms)"
                                                    class="bg-yellow-100 hover:bg-yellow-200 border border-yellow-300 rounded-full px-3 py-1 text-xs text-yellow-700 transition-colors"
                                                    title="Set current content as default template">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                </svg>
                                                Set Default
                                            </button>
                                        </div>
                                    </div>
                                    <div class="px-5 py-4">
                                        <textarea x-model="terms" placeholder="Add terms and conditions..."
                                                  class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                                  rows="4"></textarea>
                                    </div>
                                </div>

                                <!-- Notes Card -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                                    <div class="flex items-center justify-between bg-gray-200 px-5 py-4">
                                        <span class="font-medium text-gray-900">Notes</span>
                                        <div class="flex items-center space-x-2">
                                            <button @click="loadNotesTemplates()"
                                                    class="bg-purple-100 hover:bg-purple-200 border border-purple-300 rounded-full px-3 py-1 text-xs text-purple-700 transition-colors"
                                                    title="Load existing template">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                Load
                                            </button>
                                            <button @click="saveAsDefault('notes', notes)"
                                                    class="bg-yellow-100 hover:bg-yellow-200 border border-yellow-300 rounded-full px-3 py-1 text-xs text-yellow-700 transition-colors"
                                                    title="Set current content as default template">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                                </svg>
                                                Set Default
                                            </button>
                                            <span class="text-xs text-gray-600">(Optional)</span>
                                        </div>
                                    </div>
                                    <div class="px-5 py-4">
                                        <textarea x-model="notes" placeholder="Add any additional notes..."
                                                  class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                                                  rows="4"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Right side: Totals Summary -->
                            <div class="order-1 lg:order-2">
                                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 space-y-6">
                                    <!-- Top row -->
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-medium" x-text="formatCurrency(subtotal)">RM 0.00</span>
                                    </div>

                                    <!-- Buttons stack -->
                                    <div class="space-y-2">
                                        <button type="button" @click="openDiscountModal()"
                                                aria-controls="discount-modal" :aria-expanded="modals.discount"
                                                class="w-full bg-slate-600 hover:bg-slate-700 text-white font-medium rounded-lg py-2">
                                            + Discount
                                        </button>
                                        <button type="button" @click="openTaxModal()"
                                                aria-controls="tax-modal" :aria-expanded="modals.tax"
                                                class="w-full bg-slate-600 hover:bg-slate-700 text-white font-medium rounded-lg py-2">
                                            + Tax
                                        </button>
                                        <button type="button" @click="openRoundModal()"
                                                aria-controls="round-modal" :aria-expanded="modals.round"
                                                class="w-full bg-slate-600 hover:bg-slate-700 text-white font-medium rounded-lg py-2">
                                            + Round Off
                                        </button>
                                    </div>

                                    <!-- Summary lines for applied discounts/taxes -->
                                    <div x-show="discountAmount > 0" class="flex justify-between text-sm text-gray-600">
                                        <span>Discount:</span>
                                        <span x-text="formatCurrency(discountAmount)">RM 0.00</span>
                                    </div>

                                    <div x-show="taxAmount > 0" class="flex justify-between text-sm text-gray-600">
                                        <span>Tax:</span>
                                        <span x-text="formatCurrency(taxAmount)">RM 0.00</span>
                                    </div>

                                    <div x-show="roundSettings.amount != 0" class="flex justify-between text-sm text-gray-600">
                                        <span>Round Off:</span>
                                        <span x-text="formatCurrency(roundSettings.amount)">RM 0.00</span>
                                    </div>

                                    <!-- Divider line -->
                                    <div class="border-b border-gray-200"></div>

                                    <!-- Totals block -->
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-lg font-semibold">
                                            <span>Total:</span>
                                            <span x-text="formatCurrency(total)">RM 0.00</span>
                                        </div>
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <span>Paid:</span>
                                            <span x-text="formatCurrency(paidAmount)">RM 0.00</span>
                                        </div>
                                        <div class="flex justify-between text-xl font-bold">
                                            <span>Balance Due:</span>
                                            <span x-text="formatCurrency(balanceDue)">RM 0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Signatures (if enabled) - 3 Column Layout -->
                    <div x-show="optionalSections.show_signatures" class="px-4 md:px-6 lg:px-8 py-6 border-t border-gray-200">
                        <!-- Signature Toggles -->
                        <div class="flex items-center justify-end gap-4 mb-4 pb-3 border-b border-gray-200">
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                                <input type="checkbox"
                                       x-model="optionalSections.show_company_signature"
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span>Company Signature</span>
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900">
                                <input type="checkbox"
                                       x-model="optionalSections.show_customer_signature"
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span>Customer Signature</span>
                            </label>
                        </div>

                        <!-- Always 3 Columns (33% each) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">

                            <!-- Sales Rep Signature (Always shown when signatures enabled) -->
                            <div class="relative group">
                                <!-- Edit Button -->
                                <button type="button"
                                        @click="editingSignature.user = !editingSignature.user"
                                        class="absolute top-0 right-0 p-1 text-gray-400 hover:text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                    <svg x-show="!editingSignature.user" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                    <svg x-show="editingSignature.user" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>

                                <!-- View Mode -->
                                <div x-show="!editingSignature.user">
                                    <template x-if="userSignature.image_path">
                                        <div class="flex flex-col items-center">
                                            <img :src="getSignatureImageUrl(userSignature.image_path)"
                                                 alt="Sales Rep Signature"
                                                 class="h-12 mb-2">
                                            <div class="border-t border-gray-400 w-full mt-1"></div>
                                            <div class="mt-2 text-sm text-gray-900 text-center font-medium" x-text="userSignature.title || 'Sales Representative'"></div>
                                            <div class="mt-1 text-sm text-gray-600 text-center" x-text="userSignature.name || representativeName">{{ auth()->user()->name }}</div>
                                        </div>
                                    </template>
                                    <template x-if="!userSignature.image_path">
                                        <div class="h-16 border-t border-gray-400 mt-4">
                                            <div class="mt-2 text-sm text-gray-900 text-center font-medium" x-text="userSignature.title || 'Sales Representative'"></div>
                                            <div class="mt-1 text-sm text-gray-600 text-center" x-text="userSignature.name || representativeName">{{ auth()->user()->name }}</div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Edit Mode -->
                                <div x-show="editingSignature.user" class="space-y-3 p-3 bg-blue-50 rounded-lg border border-blue-200" style="display: none;">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Job Title</label>
                                        <input type="text"
                                               x-model="userSignature.title"
                                               placeholder="Sales Representative"
                                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                                        <input type="text"
                                               x-model="userSignature.name"
                                               placeholder="{{ auth()->user()->name }}"
                                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                                @click="openSignaturePad('user')"
                                                class="flex-1 px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                            Draw Signature
                                        </button>
                                        <a href="{{ route('profile.signature') }}" target="_blank"
                                           class="flex-1 px-3 py-2 bg-white border border-gray-300 text-gray-700 text-xs font-medium rounded hover:bg-gray-50 transition-colors text-center">
                                            Upload Image
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Company Signature (Optional - 33% width) -->
                            <div class="relative group"
                                 :class="!optionalSections.show_company_signature ? 'opacity-40 pointer-events-none' : ''">
                                <!-- Disabled Overlay -->
                                <div x-show="!optionalSections.show_company_signature"
                                     class="absolute inset-0 bg-gray-100 bg-opacity-50 rounded-lg flex items-center justify-center z-20"
                                     style="display: none;">
                                    <span class="text-xs text-gray-500 font-medium">Disabled</span>
                                </div>

                                <!-- Edit Button -->
                                <button type="button"
                                        @click="editingSignature.company = !editingSignature.company"
                                        x-show="optionalSections.show_company_signature"
                                        class="absolute top-0 right-0 p-1 text-gray-400 hover:text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                    <svg x-show="!editingSignature.company" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                    <svg x-show="editingSignature.company" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>

                                <!-- View Mode -->
                                <div x-show="!editingSignature.company">
                                    <template x-if="companySignature.image_path">
                                        <div class="flex flex-col items-center">
                                            <img :src="getSignatureImageUrl(companySignature.image_path)"
                                                 alt="Company Signature"
                                                 class="h-12 mb-2">
                                            <div class="border-t border-gray-400 w-full mt-1"></div>
                                            <div class="mt-2 text-sm text-gray-900 text-center font-medium" x-text="companySignature.title || 'Authorized Signatory'"></div>
                                            <div class="mt-1 text-sm text-gray-600 text-center" x-text="companySignature.name || 'Company Representative'"></div>
                                        </div>
                                    </template>
                                    <template x-if="!companySignature.image_path">
                                        <div class="h-16 border-t border-gray-400 mt-4">
                                            <div class="mt-2 text-sm text-gray-900 text-center font-medium" x-text="companySignature.title || 'Authorized Signatory'"></div>
                                            <div class="mt-1 text-sm text-gray-600 text-center" x-text="companySignature.name || 'Company Representative'"></div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Edit Mode -->
                                <div x-show="editingSignature.company" class="space-y-3 p-3 bg-blue-50 rounded-lg border border-blue-200" style="display: none;">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Job Title</label>
                                        <input type="text"
                                               x-model="companySignature.title"
                                               placeholder="Authorized Signatory"
                                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                                        <input type="text"
                                               x-model="companySignature.name"
                                               placeholder="Company Representative"
                                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                                @click="openSignaturePad('company')"
                                                class="flex-1 px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-colors">
                                            <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                            Draw Signature
                                        </button>
                                        <a href="{{ route('invoice-settings.index') }}" target="_blank"
                                           class="flex-1 px-3 py-2 bg-white border border-gray-300 text-gray-700 text-xs font-medium rounded hover:bg-gray-50 transition-colors text-center">
                                            Upload Image
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Signature (Optional - 33% width) -->
                            <div class="relative"
                                 :class="!optionalSections.show_customer_signature ? 'opacity-40 pointer-events-none' : ''">
                                <!-- Disabled Overlay -->
                                <div x-show="!optionalSections.show_customer_signature"
                                     class="absolute inset-0 bg-gray-100 bg-opacity-50 rounded-lg flex items-center justify-center z-20"
                                     style="display: none;">
                                    <span class="text-xs text-gray-500 font-medium">Disabled</span>
                                </div>

                                <div class="h-16 border-t border-gray-400 mt-4">
                                    <div class="mt-2 text-sm text-gray-600 text-center">Customer Acceptance</div>
                                    <div class="mt-1 text-sm text-gray-500 text-center" x-text="selectedCustomer.name || 'Customer'"></div>
                                    <div class="mt-1 text-xs text-gray-400 text-center">Date: _______________</div>
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

            <div class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
                <form @submit.prevent="createCustomer">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Create New Customer</h3>
                        <p class="mt-1 text-sm text-gray-500">Add a new customer to your database</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name *</label>
                                <input type="text" x-model="newCustomer.name" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Company Name</label>
                                <input type="text" x-model="newCustomer.company_name"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone *</label>
                                <input type="text" x-model="newCustomer.phone" required
                                       @input="formatPhoneNumber"
                                       placeholder="e.g., 012-345-6789"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" x-model="newCustomer.email"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <textarea x-model="newCustomer.address" rows="2"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">City</label>
                                    <input type="text" x-model="newCustomer.city"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">State</label>
                                    <input type="text" x-model="newCustomer.state"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Postal Code</label>
                                    <input type="text" x-model="newCustomer.postal_code"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Business Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Customer Segment</label>
                                <select x-model="newCustomer.customer_segment_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="">Select a segment</option>
                                    @foreach($customerSegments as $segment)
                                        <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="text-sm text-gray-600 bg-blue-50 border border-blue-200 rounded-lg p-3">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 text-blue-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span><strong>New customer status:</strong> Automatically determined based on purchase history</span>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea x-model="newCustomer.notes" rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3 pt-6 border-t border-gray-200">
                        <button type="button" @click="showNewCustomerModal = false"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Create Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Signature Pad Modal -->
    <div x-show="signaturePad.show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape="closeSignaturePad"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeSignaturePad"></div>

            <div class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Draw Your Signature</h3>
                    <p class="mt-1 text-sm text-gray-500">Use your mouse or touchscreen to draw your signature below</p>
                </div>

                <!-- Canvas Container -->
                <div class="border-2 border-gray-300 rounded-lg bg-white mb-4">
                    <canvas id="signatureCanvas"
                            class="w-full cursor-crosshair"
                            width="700"
                            height="200"
                            style="touch-action: none;">
                    </canvas>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <button type="button"
                            @click="clearSignaturePad"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Clear
                    </button>

                    <div class="flex items-center gap-3">
                        <button type="button"
                                @click="closeSignaturePad"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-300 hover:bg-gray-400 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="button"
                                @click="saveSignature"
                                class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Signature
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Discount Modal -->
    <div id="discount-modal" x-show="modals.discount" x-cloak
         @keydown.escape.prevent="closeAllModals"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur flex items-center justify-center min-h-screen px-4 z-50"
         role="dialog" aria-modal="true" aria-labelledby="discount-modal-title" aria-describedby="discount-modal-description">
        <div @click.self="closeAllModals" class="fixed inset-0" aria-hidden="true"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-6 relative z-10">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 id="discount-modal-title" class="text-lg font-semibold text-gray-900">Edit Discounts</h2>
                    <p id="discount-modal-description" class="text-sm text-gray-500 mt-1">Configure discount settings for this invoice</p>
                </div>
                <button @click="closeAllModals"
                        class="text-slate-400 hover:text-slate-600 text-sm font-medium uppercase"
                        aria-label="Close discount modal">
                    close ×
                </button>
            </div>

            <!-- Content -->
            <div class="space-y-4">
                <!-- Discount Type Selection -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Discount Type</label>
                    <select x-model="discountSettings.type" @change="updateDiscountType"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="percentage">Percentage (%)</option>
                        <option value="amount">Fixed Amount (RM)</option>
                    </select>
                </div>

                <!-- Percentage Input -->
                <div x-show="discountSettings.type === 'percentage'" class="space-y-2">
                    <label class="block text-xs font-medium text-gray-600">Percentage</label>
                    <div class="relative">
                        <input type="number" x-model="discountSettings.percentage"
                               class="w-full border border-gray-300 rounded px-3 py-2 pr-8 text-sm"
                               placeholder="0" min="0" max="100" step="0.01">
                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">%</span>
                    </div>
                </div>

                <!-- Amount Input -->
                <div x-show="discountSettings.type === 'amount'" class="space-y-2">
                    <label class="block text-xs font-medium text-gray-600">Amount</label>
                    <div class="relative">
                        <input type="number" x-model="discountSettings.amount"
                               class="w-full border border-gray-300 rounded px-3 py-2 pl-8 text-sm"
                               placeholder="0.00" min="0" step="0.01">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">RM</span>
                    </div>
                </div>

                <!-- Reason (Optional) -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Reason (Optional)</label>
                    <input type="text" x-model="discountSettings.reason"
                           class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                           placeholder="e.g., Volume discount, Early payment">
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button @click="closeAllModals"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                    Cancel
                </button>
                <button @click="applyDiscountSettings"
                        class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">
                    Apply
                </button>
            </div>
        </div>
    </div>

    <!-- Tax Modal -->
    <div id="tax-modal" x-show="modals.tax" x-cloak
         @keydown.escape.prevent="closeAllModals"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur flex items-center justify-center min-h-screen px-4 z-50"
         role="dialog" aria-modal="true" aria-labelledby="tax-modal-title" aria-describedby="tax-modal-description">
        <div @click.self="closeAllModals" class="fixed inset-0" aria-hidden="true"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-6 relative z-10">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 id="tax-modal-title" class="text-lg font-semibold text-gray-900">Edit Taxes</h2>
                    <p id="tax-modal-description" class="text-sm text-gray-500 mt-1">Configure tax settings for this invoice</p>
                </div>
                <button @click="closeAllModals"
                        class="text-slate-400 hover:text-slate-600 text-sm font-medium uppercase"
                        aria-label="Close tax modal">
                    close ×
                </button>
            </div>

            <!-- Content -->
            <div class="space-y-4">
                <!-- Tax Type Selection -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Tax Type</label>
                    <select x-model="taxSettings.type" @change="updateTaxType"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="sst">SST (6%)</option>
                        <option value="gst">GST (10%)</option>
                        <option value="vat">VAT (5%)</option>
                        <option value="custom">Custom Rate</option>
                    </select>
                </div>

                <!-- Custom Tax Input -->
                <div x-show="taxSettings.type === 'custom'" class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tax Percentage</label>
                        <div class="relative">
                            <input type="number" x-model="taxSettings.percentage"
                                   class="w-full border border-gray-300 rounded px-3 py-2 pr-8 text-sm"
                                   placeholder="0" min="0" max="100" step="0.01">
                            <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">%</span>
                        </div>
                    </div>
                </div>

                <!-- Tax Information -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800" x-text="taxSettings.label || 'Custom Tax'"></p>
                    <p class="text-xs text-blue-600 mt-1">Tax will be applied to the subtotal after discount.</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button @click="closeAllModals"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                    Cancel
                </button>
                <button @click="applyTaxSettings"
                        class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">
                    Apply
                </button>
            </div>
        </div>
    </div>

    <!-- Round Off Modal -->
    <div id="round-modal" x-show="modals.round" x-cloak
         @keydown.escape.prevent="closeAllModals"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur flex items-center justify-center min-h-screen px-4 z-50"
         role="dialog" aria-modal="true" aria-labelledby="round-modal-title" aria-describedby="round-modal-description">
        <div @click.self="closeAllModals" class="fixed inset-0" aria-hidden="true"></div>
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-6 relative z-10">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 id="round-modal-title" class="text-lg font-semibold text-gray-900">Round Off</h2>
                    <p id="round-modal-description" class="text-sm text-gray-500 mt-1">Configure rounding settings for the invoice total</p>
                </div>
                <button @click="closeAllModals"
                        class="text-slate-400 hover:text-slate-600 text-sm font-medium uppercase"
                        aria-label="Close round off modal">
                    close ×
                </button>
            </div>

            <!-- Content -->
            <div class="space-y-4">
                <!-- Enable Toggle -->
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-900">Enable Rounding</label>
                    <button @click="roundSettings.enabled = !roundSettings.enabled; updateRoundingPreview()"
                            :class="roundSettings.enabled ? 'bg-emerald-600' : 'bg-gray-300'"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        <span :class="roundSettings.enabled ? 'translate-x-6' : 'translate-x-1'"
                              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"></span>
                    </button>
                </div>

                <!-- Rounding Settings -->
                <div x-show="roundSettings.enabled" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Rounding Method</label>
                        <select x-model="roundSettings.method" @change="updateRoundingPreview"
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="nearest">Round to Nearest</option>
                            <option value="up">Round Up</option>
                            <option value="down">Round Down</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Precision</label>
                        <select x-model="roundSettings.precision" @change="updateRoundingPreview"
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="1">Whole Number (RM 1)</option>
                            <option value="0.50">50 Cents (RM 0.50)</option>
                            <option value="0.10">10 Cents (RM 0.10)</option>
                            <option value="0.05">5 Cents (RM 0.05)</option>
                            <option value="0.01">1 Cent (RM 0.01)</option>
                        </select>
                    </div>

                    <!-- Preview -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <div class="text-xs font-medium text-gray-600 mb-1">Preview</div>
                        <div class="flex justify-between text-sm">
                            <span>Current Total:</span>
                            <span x-text="formatCurrency(total)">RM 0.00</span>
                        </div>
                        <div class="flex justify-between text-sm font-medium">
                            <span>Rounded Total:</span>
                            <span x-text="formatCurrency(total + (roundSettings.amount || 0))">RM 0.00</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-600">
                            <span>Adjustment:</span>
                            <span x-text="formatCurrency(roundSettings.amount || 0)">RM 0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button @click="closeAllModals"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                    Cancel
                </button>
                <button @click="applyRoundSettings"
                        class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">
                    Apply
                </button>
            </div>
        </div>
    </div>

    <!-- Enhanced Service Template Selection Modal with Tabs -->
    <div x-show="showTemplateModal && !templateModal.type"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center min-h-screen px-4 z-50"
         style="display: none;">
        <div @click.away="showTemplateModal = false"
             class="bg-white rounded-lg shadow-xl w-full max-w-5xl max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 border-b">
                <div>
                    <h2 class="text-xl font-semibold text-white">Select Service Template</h2>
                    <p class="text-blue-100 text-sm mt-1">Choose a full template or pick individual sections</p>
                </div>
                <button @click="showTemplateModal = false"
                        class="text-white hover:text-blue-100 text-2xl font-semibold">
                    &times;
                </button>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 px-6 pt-4">
                <nav class="-mb-px flex space-x-8">
                    <button @click="templateTab = 'full'"
                            :class="templateTab === 'full' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Full Templates
                    </button>
                    <button @click="templateTab = 'sections'"
                            :class="templateTab === 'sections' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="py-3 px-1 border-b-2 font-medium text-sm transition-colors">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Individual Sections
                    </button>
                </nav>
            </div>

            <!-- Content -->
            <div class="p-6 max-h-[calc(90vh-240px)] overflow-y-auto">
                <!-- Loading State -->
                <div x-show="loadingTemplates" class="flex items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
                    <span class="ml-3 text-gray-600 font-medium">Loading service templates...</span>
                </div>

                <!-- Full Templates Tab -->
                <div x-show="templateTab === 'full'">
                    <!-- Templates Grid -->
                    <div x-show="!loadingTemplates && serviceTemplates.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="template in serviceTemplates" :key="template.id">
                        <div class="border-2 border-gray-200 rounded-lg p-5 hover:border-blue-500 hover:shadow-md cursor-pointer transition-all duration-200"
                             @click="selectServiceTemplate(template)">
                            <!-- Template Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-lg text-gray-900" x-text="template.name"></h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded"
                                              x-text="template.category"></span>
                                        <span x-show="template.is_active" class="text-xs text-green-600">• Active</span>
                                    </div>
                                </div>
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>

                            <!-- Template Description -->
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2" x-text="template.description || 'No description provided'"></p>

                            <!-- Template Stats -->
                            <div class="grid grid-cols-3 gap-3 pt-3 border-t border-gray-200">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-blue-600" x-text="template.sections_count || template.sections?.length || 0"></div>
                                    <div class="text-xs text-gray-500">Sections</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-green-600" x-text="template.items_count || 0"></div>
                                    <div class="text-xs text-gray-500">Items</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-purple-600">RM <span x-text="(template.base_price || 0).toFixed(0)"></span></div>
                                    <div class="text-xs text-gray-500">Est. Price</div>
                                </div>
                            </div>

                            <!-- Sections Preview -->
                            <div x-show="template.sections && template.sections.length > 0" class="mt-4 pt-3 border-t border-gray-100">
                                <div class="text-xs font-medium text-gray-700 mb-2">Sections:</div>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="(section, idx) in template.sections.slice(0, 3)" :key="section.id">
                                        <span class="inline-block px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded">
                                            <span x-text="section.name"></span>
                                        </span>
                                    </template>
                                    <span x-show="template.sections.length > 3" class="inline-block px-2 py-0.5 text-xs text-gray-500">
                                        +<span x-text="template.sections.length - 3"></span> more
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                    <!-- Empty State -->
                    <div x-show="!loadingTemplates && serviceTemplates.length === 0"
                         class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <svg class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Service Templates Found</h3>
                        <p class="text-gray-600 mb-6">Create your first service template to streamline invoice creation.</p>
                        <a href="/service-templates/create"
                           class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create Service Template
                        </a>
                    </div>
                </div>
                <!-- End Full Templates Tab -->

                <!-- Individual Sections Tab -->
                <div x-show="templateTab === 'sections'" class="space-y-4">
                    <!-- Section Filter -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Template</label>
                        <select x-model="sectionFilterTemplate" @change="loadAllSections()"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Templates</option>
                            <template x-for="template in serviceTemplates" :key="template.id">
                                <option :value="template.id" x-text="template.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Sections List with Checkboxes -->
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        <template x-for="section in filteredSections" :key="section.id">
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox"
                                           :value="section.id"
                                           x-model="selectedSectionIds"
                                           class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">

                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-medium text-gray-900" x-text="section.name"></h4>
                                            <span class="text-xs text-gray-500" x-text="section.template_name"></span>
                                        </div>

                                        <p x-show="section.description" class="mt-1 text-xs text-gray-600" x-text="section.description"></p>

                                        <!-- Item Preview -->
                                        <div class="mt-2 flex items-center text-xs text-gray-500">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            <span x-text="section.items_count + ' items'"></span>
                                            <span class="mx-2">•</span>
                                            <span x-text="'Est. RM ' + (section.estimated_total || 0).toFixed(2)"></span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </template>

                        <!-- Empty State for Sections -->
                        <div x-show="filteredSections.length === 0 && !loadingTemplates" class="text-center py-8">
                            <div class="text-gray-400 mb-3">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-600">No sections available. Create service templates with sections first.</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                        <div class="text-sm text-gray-600">
                            <span x-text="selectedSectionIds.length"></span>
                            <span x-text="selectedSectionIds.length === 1 ? 'section' : 'sections'"></span> selected
                        </div>
                        <div class="flex gap-2">
                            <button @click="showTemplateModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                                Cancel
                            </button>
                            <button @click="loadSelectedSections"
                                    :disabled="selectedSectionIds.length === 0"
                                    :class="selectedSectionIds.length > 0 ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                                    class="px-4 py-2 text-sm font-medium rounded-md transition">
                                Add Selected Sections
                            </button>
                        </div>
                    </div>
                </div>
                <!-- End Individual Sections Tab -->
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between bg-gray-50 px-6 py-4 border-t">
                <a href="/service-templates"
                   class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Manage Service Templates
                </a>
                <button @click="showTemplateModal = false"
                        class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Template Selection Modal -->
    <div x-show="showTemplateModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center min-h-screen px-4 z-50"
         style="display: none;">
        <div @click.away="showTemplateModal = false"
             class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[80vh] overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between bg-gray-50 px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900" x-text="templateModal.title">Select Template</h2>
                <button @click="showTemplateModal = false"
                        class="text-gray-500 hover:text-gray-700 text-xl font-semibold">
                    &times;
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 max-h-96 overflow-y-auto">
                <!-- Loading State -->
                <div x-show="templateModal.loading" class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-2 text-gray-600">Loading templates...</span>
                </div>

                <!-- Templates List -->
                <div x-show="!templateModal.loading && templateModal.templates.length > 0" class="space-y-3">
                    <template x-for="template in templateModal.templates" :key="template.id">
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer"
                             @click="selectTemplate(template)">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-medium text-gray-900" x-text="template.name"></h3>
                                <span x-show="template.is_default"
                                      class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Default</span>
                            </div>
                            <p class="text-sm text-gray-600 line-clamp-3" x-text="template.content"></p>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="!templateModal.loading && templateModal.templates.length === 0"
                     class="text-center py-8">
                    <div class="text-gray-400 mb-2">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No templates found</h3>
                    <p class="text-gray-600 mb-4">Create your first template to get started.</p>
                    <a :href="'/invoice-note-templates/create?type=' + templateModal.type"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        Create Template
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between bg-gray-50 px-6 py-4 border-t">
                <a :href="'/invoice-note-templates?type=' + templateModal.type"
                   class="text-sm text-blue-600 hover:text-blue-800">
                    Manage Templates
                </a>
                <button @click="showTemplateModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Logo Selector Modal -->
    <div x-show="showLogoSelector"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center min-h-screen px-4 z-50"
         style="display: none;">
        <div @click.away="showLogoSelector = false"
             class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[80vh] overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between bg-gray-50 px-6 py-4 border-b">
                <h2 class="text-lg font-semibold text-gray-900">Select Company Logo</h2>
                <button @click="showLogoSelector = false"
                        class="text-gray-500 hover:text-gray-700 text-xl font-semibold">
                    &times;
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 max-h-96 overflow-y-auto">
                <!-- Loading State -->
                <div x-show="logoBank.length === 0" class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <span class="ml-2 text-gray-600">Loading logos...</span>
                </div>

                <!-- Logo Grid -->
                <div x-show="logoBank.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <template x-for="logo in logoBank" :key="logo.id">
                        <div @click="selectLogo(logo.id, logo.url)"
                             :class="selectedLogoId === logo.id ? 'ring-2 ring-blue-600 bg-blue-50' : 'hover:bg-gray-50'"
                             class="border border-gray-200 rounded-lg p-4 cursor-pointer transition-all relative">
                            <!-- Selected Indicator -->
                            <div x-show="selectedLogoId === logo.id"
                                 class="absolute top-2 right-2 bg-blue-600 text-white rounded-full p-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>

                            <!-- Default Badge -->
                            <div x-show="logo.is_default"
                                 class="absolute top-2 left-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Default
                                </span>
                            </div>

                            <!-- Logo Image -->
                            <div class="aspect-w-16 aspect-h-9 bg-gray-100 flex items-center justify-center mb-3 rounded">
                                <img :src="logo.url"
                                     :alt="logo.name"
                                     class="max-h-20 object-contain">
                            </div>

                            <!-- Logo Name -->
                            <h3 class="text-sm font-medium text-gray-900 text-center truncate" x-text="logo.name"></h3>
                            <p x-show="logo.notes" class="text-xs text-gray-500 text-center mt-1 line-clamp-2" x-text="logo.notes"></p>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="logoBank.length === 0"
                     class="text-center py-8"
                     style="display: none;">
                    <div class="text-gray-400 mb-2">
                        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No logos available</h3>
                    <p class="text-gray-600 mb-4">Upload logos in the Logo Bank to get started.</p>
                    <a href="{{ route('logo-bank.index') }}"
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        Go to Logo Bank
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between bg-gray-50 px-6 py-4 border-t">
                <a href="{{ route('logo-bank.index') }}"
                   target="_blank"
                   class="text-sm text-blue-600 hover:text-blue-800">
                    Manage Logo Bank
                </a>
                <button @click="showLogoSelector = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div x-show="modals.preview"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="modals.preview = false">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
             @click="modals.preview = false"></div>

        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Invoice Preview</h3>
                        <p class="text-sm text-gray-500 mt-1">Save invoice to generate PDF version</p>
                    </div>
                    <button @click="modals.preview = false"
                            class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Invoice Preview Content -->
                <div class="p-6">
                    <style>
                        .preview-invoice * { box-sizing: border-box; }
                        .preview-invoice { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000; background: #ffffff; line-height: 1.5; max-width: 800px; margin: 0 auto; padding: 20px; }
                        .preview-title { text-align: center; font-size: 26px; letter-spacing: 2px; margin-bottom: 30px; font-weight: 600; }
                        .preview-separator { border: none; border-top: 2px solid #0b57d0; margin: 30px 0 20px; }
                        .preview-section-label { font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
                        .preview-card { border: 1px solid #d0d5dd; border-radius: 4px; padding: 10px; background: #fafafa; }
                        .preview-company-name { font-size: 14px; font-weight: 700; margin-bottom: 4px; color: #0b57d0; }
                        .preview-company-line { color: #4b5563; font-size: 12px; line-height: 1.4; }
                        .preview-items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        .preview-items-table thead th { background: #0b57d0; color: #ffffff; padding: 8px; font-weight: 600; text-align: left; border: 1px solid #0b57d0; }
                        .preview-items-table tbody td { padding: 6px 8px; border: 1px solid #d0d5dd; }
                        .preview-totals-table { width: 100%; font-size: 12px; }
                        .preview-totals-table td { padding: 3px 0; }
                        .preview-totals-table td:first-child { text-align: left; color: #4b5563; padding-right: 12px; }
                        .preview-totals-table td:last-child { text-align: right; width: 110px; font-weight: 500; }
                        .preview-total-row td { font-weight: 600; padding-top: 4px; border-top: 1px solid #d0d5dd; }
                        .preview-balance-row td { font-weight: 700; color: #dc2626; }
                        .preview-footer { margin-top: 30px; text-align: center; font-size: 10px; color: #4b5563; border-top: 1px solid #d0d5dd; padding-top: 15px; }
                    </style>

                    <div class="preview-invoice">
                        <div class="bg-amber-50 border-l-4 border-amber-400 p-3 mb-4"><p class="text-sm text-amber-700">⚠️ <strong>PREVIEW MODE</strong> - Not saved. Save to generate PDF.</p></div>

                        <h1 class="preview-title">INVOICE</h1>

                        <table style="width: 100%;"><tr><td style="width: 70%; vertical-align: top;">
                            <div class="preview-company-name" x-text="currentBrand.name || 'Company Name'"></div>
                            <div class="preview-company-line" x-text="currentBrand.address || '123 Business Street'"></div>
                            <div class="preview-company-line"><span x-text="currentBrand.postal_code || '12345'"></span> <span x-text="currentBrand.city || 'City'"></span></div>
                            <div class="preview-company-line" x-text="currentBrand.state || 'State'"></div>
                            <div class="preview-company-line">Email: <span x-text="currentBrand.email || 'info@company.com'"></span></div>
                            <div class="preview-company-line">Mobile: <span x-text="currentBrand.phone || '+60 12-345 6789'"></span></div>
                        </td><td style="width: 30%; vertical-align: top; text-align: right;">
                            <img :src="selectedLogoUrl" alt="Company Logo" style="max-width: 120px; max-height: 60px; object-fit: contain;">
                        </td></tr></table>

                        <hr class="preview-separator">

                        <table style="width: 100%;"><tr>
                            <td style="width: 50%; vertical-align: top; padding-right: 20px;">
                                <div class="preview-section-label">Bill To</div>
                                <div class="preview-card">
                                    <div x-text="selectedCustomer.name || 'Customer Name'" style="font-weight: 500;"></div>
                                    <div x-show="selectedCustomer.company_name" x-text="selectedCustomer.company_name"></div>
                                    <div x-show="selectedCustomer.address" x-text="selectedCustomer.address"></div>
                                    <div><span x-show="selectedCustomer.postal_code" x-text="selectedCustomer.postal_code"></span><span x-show="selectedCustomer.city" x-text="' ' + selectedCustomer.city"></span></div>
                                    <div x-show="selectedCustomer.state" x-text="selectedCustomer.state"></div>
                                    <div x-show="selectedCustomer.email" x-text="'Email: ' + selectedCustomer.email"></div>
                                    <div x-show="selectedCustomer.phone" x-text="'Phone: ' + selectedCustomer.phone"></div>
                                </div>
                            </td>
                            <td style="width: 50%; vertical-align: top;">
                                <div class="preview-section-label">Invoice Info</div>
                                <div class="preview-card"><table style="width: 100%;">
                                    <tr><td style="font-weight: 600; color: #4b5563; padding: 2px 0;">Invoice No :</td><td style="padding: 2px 0;" x-text="invoiceNumber"></td></tr>
                                    <tr><td style="font-weight: 600; color: #4b5563; padding: 2px 0;">Invoice Date :</td><td style="padding: 2px 0;" x-text="invoiceDateDisplay"></td></tr>
                                    <tr><td style="font-weight: 600; color: #4b5563; padding: 2px 0;">Due Date :</td><td style="padding: 2px 0;" x-text="dueDateDisplay"></td></tr>
                                </table></div>
                            </td>
                        </tr></table>

                        <table class="preview-items-table"><thead><tr>
                            <th style="width: 8%; text-align: center;">Sl</th>
                            <th style="width: auto; text-align: left;">Description</th>
                            <th style="width: 10%; text-align: center;">Quantity</th>
                            <th style="width: 18%; text-align: right;">Rate</th>
                            <th style="width: 18%; text-align: right;">Amount</th>
                        </tr></thead><tbody>
                            <template x-for="(item, index) in lineItems.filter(i => i.description.trim() !== '')" :key="index"><tr>
                                <td style="text-align: center;" x-text="index + 1"></td>
                                <td x-text="item.description"></td>
                                <td style="text-align: center;" x-text="item.quantity"></td>
                                <td style="text-align: right;" x-text="'RM ' + parseFloat(item.unit_price).toFixed(2)"></td>
                                <td style="text-align: right;" x-text="'RM ' + (item.quantity * item.unit_price).toFixed(2)"></td>
                            </tr></template>
                        </tbody></table>

                        <table style="width: 100%; margin-top: 20px;"><tr>
                            <td style="width: 50%; vertical-align: top; padding-right: 20px;" x-show="optionalSections.show_payment_instructions && paymentInstructions">
                                <div class="preview-section-label">Payment Instructions</div>
                                <div style="border: 1px solid #d0d5dd; padding: 8px 10px; border-radius: 4px; background: #fafafa; font-size: 11px; line-height: 1.3; white-space: pre-line;" x-text="paymentInstructions"></div>
                            </td>
                            <td :style="optionalSections.show_payment_instructions && paymentInstructions ? 'width: 50%;' : 'width: 100%;'" style="vertical-align: top;">
                                <table class="preview-totals-table">
                                    <tr><td>Subtotal</td><td x-text="'RM ' + subtotal.toFixed(2)"></td></tr>
                                    <tr x-show="discountAmount > 0"><td>Discount</td><td x-text="'-RM ' + discountAmount.toFixed(2)"></td></tr>
                                    <tr x-show="taxAmount > 0"><td>Tax</td><td x-text="'RM ' + taxAmount.toFixed(2)"></td></tr>
                                    <tr class="preview-total-row"><td>Total</td><td x-text="'RM ' + total.toFixed(2)"></td></tr>
                                    <tr><td>Paid</td><td x-text="'RM ' + paidAmount.toFixed(2)"></td></tr>
                                    <tr class="preview-balance-row"><td>Balance Due</td><td x-text="'RM ' + (total - paidAmount).toFixed(2)"></td></tr>
                                </table>
                            </td>
                        </tr></table>

                        <div x-show="notes && notes.trim() !== ''" style="margin-top: 20px;">
                            <div class="preview-section-label">Notes</div>
                            <div style="font-size: 11px; line-height: 1.4; white-space: pre-line;" x-text="notes"></div>
                        </div>

                        <div x-show="terms && terms.trim() !== ''" style="margin-top: 20px;">
                            <div class="preview-section-label">Terms & Conditions</div>
                            <div style="font-size: 11px; line-height: 1.4; white-space: pre-line;" x-text="terms"></div>
                        </div>

                        <table x-show="optionalSections.show_signatures" style="width: 100%; margin-top: 40px; font-size: 12px;"><tr>
                            <td style="width: 33.33%; text-align: center; vertical-align: top; padding-top: 40px;">
                                <div style="border-top: 1px solid #d0d5dd; padding-top: 4px; width: 75%; margin: 0 auto; font-weight: 600; color: #4b5563;">Sales Representative</div>
                                <div style="margin-top: 2px;">{{ auth()->user()->name ?? 'Sales Rep' }}</div>
                            </td>
                            <td x-show="optionalSections.show_company_signature" style="width: 33.33%; text-align: center; vertical-align: top; padding-top: 40px;">
                                <div style="border-top: 1px solid #d0d5dd; padding-top: 4px; width: 75%; margin: 0 auto; font-weight: 600; color: #4b5563;">Authorized Signatory</div>
                                <div style="margin-top: 2px;">Company Representative</div>
                            </td>
                            <td x-show="optionalSections.show_customer_signature" style="width: 33.33%; text-align: center; vertical-align: top; padding-top: 40px;">
                                <div style="border-top: 1px solid #d0d5dd; padding-top: 4px; width: 75%; margin: 0 auto; font-weight: 600; color: #4b5563;">Customer Acceptance</div>
                                <div style="margin-top: 2px;" x-text="selectedCustomer.name || 'Customer'"></div>
                            </td>
                        </tr></table>

                        <div class="preview-footer">{{ auth()->user()->company->name ?? 'Company' }} • Invoice <span x-text="invoiceNumber"></span> • Preview Generated</div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="sticky bottom-0 bg-gray-50 border-t border-gray-200 px-6 py-4 flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        <svg class="inline w-4 h-4 text-amber-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        This is a preview only. Save invoice to generate PDF.
                    </p>
                    <div class="flex space-x-3">
                        <button @click="modals.preview = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Close
                        </button>
                        <button @click="modals.preview = false; saveInvoice()"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Save Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>

<script>
function invoiceBuilder() {
    return {
        // UI State
        showCustomerDropdown: false,
        showNewCustomerModal: false,
        showTemplateModal: false,
                // Customer Management
        customerSearch: '',
        customerResults: [],
        selectedCustomer: {},
        customerSegmentName: '',

        // Service Template Integration
        showTemplateModal: false,
        serviceTemplates: [],
        selectedTemplate: null,
        loadingTemplates: false,
        templateTab: 'full',  // 'full' or 'sections'
        allSections: [],  // Flattened list of all sections from all templates
        selectedSectionIds: [],  // Array of selected section IDs for multi-select
        sectionFilterTemplate: '',  // Filter sections by template name

        // Section Management
        sectionIdCounter: 1,
        itemIdCounter: 1,

        // New Customer Form
        newCustomer: {
            name: '',
            company_name: '',
            phone: '',
            email: '',
            address: '',
            city: '',
            state: '',
            postal_code: '',
            customer_segment_id: '',
            notes: ''
        },

        // Invoice Data
        currentInvoiceId: null, // Track the current draft invoice to avoid duplicates
        invoiceNumber: 'INV-2025-000001',
        invoiceDate: new Date().toISOString().split('T')[0],
        invoiceDateDisplay: '',
        dueDate: '',
        dueDateDisplay: '',
        selectedBrandId: '{{ $companyBrands->where("is_default", true)->first()->id ?? "" }}',

        // Company Brands Data
        companyBrands: @json($companyBrands),
        currentBrand: {
            name: '{{ $companyBrands->where("is_default", true)->first()->name ?? auth()->user()->company->name }}',
            address: '{{ $companyBrands->where("is_default", true)->first()->address ?? auth()->user()->company->address }}',
            city: '{{ $companyBrands->where("is_default", true)->first()->city ?? auth()->user()->company->city }}',
            state: '{{ $companyBrands->where("is_default", true)->first()->state ?? auth()->user()->company->state }}',
            postal_code: '{{ $companyBrands->where("is_default", true)->first()->postal_code ?? auth()->user()->company->postal_code }}',
            phone: '{{ $companyBrands->where("is_default", true)->first()->phone ?? auth()->user()->company->phone }}',
            email: '{{ $companyBrands->where("is_default", true)->first()->email ?? auth()->user()->company->email }}',
            logo_path: '{{ $companyBrands->where("is_default", true)->first()->logo_path ?? "" }}'
        },

        // Optional Sections
        optionalSections: {
            show_shipping: true,
            show_payment_instructions: true,
            show_signatures: true,
            show_company_logo: true,
            show_company_signature: false,  // Optional company authorized signatory (default OFF)
            show_customer_signature: false  // Optional customer acceptance (default OFF)
        },

        // Table Columns Configuration
        columns: [
            { key: 'sl', label: 'SI', visible: true, order: 1 },
            { key: 'description', label: 'Description', visible: true, order: 2 },
            { key: 'quantity', label: 'Quantity', visible: true, order: 3 },
            { key: 'rate', label: 'Rate', visible: true, order: 4 },
            { key: 'amount', label: 'Amount', visible: true, order: 5 }
        ],

        // Shipping Information
        shippingInfo: {
            name: '',
            address: '',
            city: '',
            state: '',
            postal_code: ''
        },
        shippingSameAsBilling: true,

        // Service Sections (replaces line items for service invoices)
        sections: [],

        // Manual section creation mode
        manualMode: false,

        // Financial Calculations
        subtotal: 0,
        discountPercentage: 0,
        discountAmount: 0,
        taxPercentage: 0,
        taxAmount: 0,
        total: 0,
        paidAmount: 0,

        // UI State for Totals Section
        showDiscountInput: false,
        showTaxInput: false,
        showRoundOffInput: false,

        // Modal State
        modals: {
            discount: false,
            tax: false,
            round: false,
            preview: false
        },

        // Modal Settings
        discountSettings: {
            type: 'percentage', // 'percentage' or 'amount'
            percentage: 0,
            amount: 0,
            reason: ''
        },

        taxSettings: {
            type: 'sst', // 'sst', 'gst', 'vat', 'custom'
            percentage: 6, // Default SST rate
            customRate: 0,
            label: 'SST (6%)',
            customLabel: 'Tax'
        },

        roundSettings: {
            enabled: false,
            method: 'nearest', // 'up', 'down', 'nearest'
            precision: 0.05, // Round to nearest 5 cents
            amount: 0
        },

        // Content
        notes: @json($defaultTemplates['notes']->content ?? 'Thank you for your business!'),
        terms: @json($defaultTemplates['terms']->content ?? 'Payment is due within 30 days. Late payments may incur additional charges.'),
        paymentInstructions: @json($defaultTemplates['payment_instructions']->content ?? 'Please make payments to:\n\nCompany: {{ auth()->user()->company->name ?? "Your Company Name" }}\nBank: Maybank\nAccount: 1234567890\n\nPlease include invoice number in payment reference.'),

        // Logo Management
        logoBank: [],
        selectedLogoId: {{ auth()->user()->company->defaultLogo()?->id ?? 'null' }},
        selectedLogoUrl: '{{ auth()->user()->company->defaultLogo() ? route("logo-bank.serve", auth()->user()->company->defaultLogo()->id) . "?v=" . auth()->user()->company->defaultLogo()->updated_at->timestamp : "" }}',
        showLogoSelector: false,

        // Notes Templates
        notesTemplates: [
            { id: 1, name: 'Standard Thank You', content: 'Thank you for your business! We appreciate your continued trust in our services.' },
            { id: 2, name: 'Payment Reminder', content: 'Please ensure payment is made by the due date to avoid any late fees.' },
            { id: 3, name: 'Warranty Info', content: 'This invoice includes warranty coverage as per our standard terms and conditions.' },
            { id: 4, name: 'Custom Service', content: 'Services provided as per custom specifications discussed.' }
        ],

        // Template Modal Data
        templateModal: {
            title: 'Select Template',
            type: '',
            templates: [],
            loading: false
        },

        // Representative Information
        representativeName: '{{ auth()->user()->name }}',
        representativeTitle: 'Sales Representative',

        // User Signature (Sales Rep)
        userSignature: {
            name: '{{ auth()->user()->signature_name ?? auth()->user()->name }}',
            title: '{{ auth()->user()->signature_title ?? "Sales Representative" }}',
            image_path: '{{ auth()->user()->signature_path ?? "" }}'
        },

        // Company Signature (Optional Authorized Signatory)
        companySignature: {
            name: '',
            title: '',
            image_path: ''
        },

        // Signature Edit State
        editingSignature: {
            user: false,
            company: false
        },

        // Signature Pad State
        signaturePad: {
            show: false,
            type: '', // 'user' or 'company'
            canvas: null,
            ctx: null,
            isDrawing: false,
            lastX: 0,
            lastY: 0
        },

        init() {
            // Set default dates in DD/MM/YYYY format
            const today = new Date();
            this.invoiceDateDisplay = this.formatDateDDMMYYYY(today);

            const dueDate = new Date();
            dueDate.setDate(dueDate.getDate() + 30);
            this.dueDate = dueDate.toISOString().split('T')[0];
            this.dueDateDisplay = this.formatDateDDMMYYYY(dueDate);

            // Calculate initial totals
            this.calculateTotals();

            // Load invoice settings
            this.loadInvoiceSettings();

            // Load saved notes templates
            this.loadSavedTemplates();

            // Load logo bank
            this.loadLogoBank();

            // Watch for brand changes
            this.$watch('selectedBrandId', (newBrandId) => {
                this.updateBrandDetails(newBrandId);
            });
        },

        // Brand Management Methods
        updateBrandDetails(brandId) {
            if (!brandId) {
                // Use default brand or company details
                const defaultBrand = this.companyBrands.find(b => b.is_default);
                if (defaultBrand) {
                    this.currentBrand = {
                        name: defaultBrand.name || '',
                        address: defaultBrand.address || '',
                        city: defaultBrand.city || '',
                        state: defaultBrand.state || '',
                        postal_code: defaultBrand.postal_code || '',
                        phone: defaultBrand.phone || '',
                        email: defaultBrand.email || '',
                        logo_path: defaultBrand.logo_path || ''
                    };

                    // Update logo if brand has one
                    if (defaultBrand.logo_path) {
                        this.selectedLogoUrl = '/storage/' + defaultBrand.logo_path;
                    }
                }
                return;
            }

            // Find the selected brand
            const brand = this.companyBrands.find(b => b.id == brandId);
            if (brand) {
                this.currentBrand = {
                    name: brand.name || '',
                    address: brand.address || '',
                    city: brand.city || '',
                    state: brand.state || '',
                    postal_code: brand.postal_code || '',
                    phone: brand.phone || '',
                    email: brand.email || '',
                    logo_path: brand.logo_path || ''
                };

                // Update logo if brand has one
                if (brand.logo_path) {
                    this.selectedLogoUrl = '/storage/' + brand.logo_path;
                }
            }
        },

        // Modal Methods
        openDiscountModal() {
            this.closeAllModals();
            // Seed current values
            this.discountSettings.percentage = this.discountPercentage || 0;
            this.discountSettings.amount = this.discountAmount || 0;
            this.discountSettings.type = this.discountPercentage > 0 ? 'percentage' : 'amount';
            this.modals.discount = true;
            document.body.style.overflow = 'hidden';
            // Focus management
            this.$nextTick(() => {
                const firstInput = document.querySelector('#discount-modal input, #discount-modal select, #discount-modal button');
                if (firstInput) firstInput.focus();
            });
        },

        openTaxModal() {
            this.closeAllModals();
            // Seed current values
            this.taxSettings.percentage = this.taxPercentage || 6;
            if (this.taxSettings.percentage === 6) {
                this.taxSettings.type = 'sst';
                this.taxSettings.label = 'SST (6%)';
            } else if (this.taxSettings.percentage === 10) {
                this.taxSettings.type = 'gst';
                this.taxSettings.label = 'GST (10%)';
            } else {
                this.taxSettings.type = 'custom';
                this.taxSettings.customRate = this.taxSettings.percentage;
            }
            this.modals.tax = true;
            document.body.style.overflow = 'hidden';
            // Focus management
            this.$nextTick(() => {
                const firstInput = document.querySelector('#tax-modal input, #tax-modal select, #tax-modal button');
                if (firstInput) firstInput.focus();
            });
        },

        openRoundModal() {
            this.closeAllModals();
            // Seed current values
            this.roundSettings.enabled = Math.abs(this.total - Math.round(this.total * 20) / 20) < 0.01;
            this.modals.round = true;
            document.body.style.overflow = 'hidden';
            // Focus management
            this.$nextTick(() => {
                const firstInput = document.querySelector('#round-modal input, #round-modal select, #round-modal button');
                if (firstInput) firstInput.focus();
            });
        },

        closeAllModals() {
            this.modals.discount = false;
            this.modals.tax = false;
            this.modals.round = false;
            document.body.style.overflow = '';
            // Return focus to trigger button
            this.$nextTick(() => {
                const lastFocusedButton = document.activeElement;
                if (lastFocusedButton && lastFocusedButton.tagName === 'BUTTON') {
                    lastFocusedButton.blur();
                }
            });
        },

        // Signature Pad Methods
        openSignaturePad(type) {
            this.signaturePad.type = type;
            this.signaturePad.show = true;
            document.body.style.overflow = 'hidden';

            // Initialize canvas after modal is shown
            this.$nextTick(() => {
                this.initSignatureCanvas();
            });
        },

        closeSignaturePad() {
            this.signaturePad.show = false;
            document.body.style.overflow = '';
            this.signaturePad.type = '';

            // Remove event listeners
            if (this.signaturePad.canvas) {
                this.signaturePad.canvas.removeEventListener('mousedown', this.startDrawing);
                this.signaturePad.canvas.removeEventListener('mousemove', this.draw);
                this.signaturePad.canvas.removeEventListener('mouseup', this.stopDrawing);
                this.signaturePad.canvas.removeEventListener('mouseout', this.stopDrawing);
                this.signaturePad.canvas.removeEventListener('touchstart', this.startDrawing);
                this.signaturePad.canvas.removeEventListener('touchmove', this.draw);
                this.signaturePad.canvas.removeEventListener('touchend', this.stopDrawing);
            }
        },

        initSignatureCanvas() {
            this.signaturePad.canvas = document.getElementById('signatureCanvas');
            if (!this.signaturePad.canvas) return;

            this.signaturePad.ctx = this.signaturePad.canvas.getContext('2d');

            // Set canvas background to white
            this.signaturePad.ctx.fillStyle = 'white';
            this.signaturePad.ctx.fillRect(0, 0, this.signaturePad.canvas.width, this.signaturePad.canvas.height);

            // Set drawing style
            this.signaturePad.ctx.strokeStyle = '#000';
            this.signaturePad.ctx.lineWidth = 4;
            this.signaturePad.ctx.lineCap = 'round';
            this.signaturePad.ctx.lineJoin = 'round';

            // Add event listeners
            this.signaturePad.canvas.addEventListener('mousedown', (e) => this.startDrawing(e));
            this.signaturePad.canvas.addEventListener('mousemove', (e) => this.draw(e));
            this.signaturePad.canvas.addEventListener('mouseup', () => this.stopDrawing());
            this.signaturePad.canvas.addEventListener('mouseout', () => this.stopDrawing());

            // Touch events for mobile
            this.signaturePad.canvas.addEventListener('touchstart', (e) => this.startDrawing(e), { passive: false });
            this.signaturePad.canvas.addEventListener('touchmove', (e) => this.draw(e), { passive: false });
            this.signaturePad.canvas.addEventListener('touchend', () => this.stopDrawing());
        },

        startDrawing(e) {
            e.preventDefault();
            this.signaturePad.isDrawing = true;

            const rect = this.signaturePad.canvas.getBoundingClientRect();
            const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
            const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;

            this.signaturePad.lastX = x;
            this.signaturePad.lastY = y;
        },

        draw(e) {
            if (!this.signaturePad.isDrawing) return;
            e.preventDefault();

            const rect = this.signaturePad.canvas.getBoundingClientRect();
            const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
            const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;

            this.signaturePad.ctx.beginPath();
            this.signaturePad.ctx.moveTo(this.signaturePad.lastX, this.signaturePad.lastY);
            this.signaturePad.ctx.lineTo(x, y);
            this.signaturePad.ctx.stroke();

            this.signaturePad.lastX = x;
            this.signaturePad.lastY = y;
        },

        stopDrawing() {
            this.signaturePad.isDrawing = false;
        },

        clearSignaturePad() {
            if (!this.signaturePad.ctx || !this.signaturePad.canvas) return;

            // Clear canvas and set white background
            this.signaturePad.ctx.fillStyle = 'white';
            this.signaturePad.ctx.fillRect(0, 0, this.signaturePad.canvas.width, this.signaturePad.canvas.height);
        },

        async saveSignature() {
            if (!this.signaturePad.canvas) return;

            // Convert canvas to data URL
            const dataURL = this.signaturePad.canvas.toDataURL('image/png');

            // Save to server for user signature
            if (this.signaturePad.type === 'user') {
                try {
                    const response = await fetch('{{ route("profile.signature.update") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            signature_data: dataURL,
                            signature_name: this.userSignature.name,
                            signature_title: this.userSignature.title
                        })
                    });

                    const data = await response.json();

                    console.log('Signature save response:', data);

                    if (data.success) {
                        // Update with server path instead of data URL
                        // Add cache buster to force image reload
                        const newPath = data.signature.image_path + '?t=' + Date.now();
                        console.log('New signature path:', newPath);
                        console.log('Full URL will be:', this.getSignatureImageUrl(newPath));

                        this.userSignature.image_path = newPath;
                        this.userSignature.name = data.signature.name;
                        this.userSignature.title = data.signature.title;

                        // Show success message
                        this.showNotification('Signature saved successfully', 'success');
                    } else {
                        console.error('Save failed:', data);
                        this.showNotification('Error: ' + (data.message || 'Unknown error'), 'error');
                    }
                } catch (error) {
                    console.error('Error saving signature:', error);
                    this.showNotification('Error saving signature: ' + error.message, 'error');
                }
            } else if (this.signaturePad.type === 'company') {
                // For company signature, keep as data URL for now
                this.companySignature.image_path = dataURL;
            }

            // Close modal
            this.closeSignaturePad();
        },

        // Modal Apply Methods
        applyDiscountSettings() {
            if (this.discountSettings.type === 'percentage') {
                this.discountPercentage = parseFloat(this.discountSettings.percentage) || 0;
                this.discountAmount = (this.subtotal * this.discountPercentage) / 100;
            } else {
                this.discountAmount = parseFloat(this.discountSettings.amount) || 0;
                this.discountPercentage = this.subtotal > 0 ? (this.discountAmount / this.subtotal) * 100 : 0;
            }
            this.calculateTotals();
            this.closeAllModals();
        },

        applyTaxSettings() {
            if (this.taxSettings.type === 'sst') {
                this.taxPercentage = 6;
            } else if (this.taxSettings.type === 'gst') {
                this.taxPercentage = 10;
            } else if (this.taxSettings.type === 'vat') {
                this.taxPercentage = 5;
            } else {
                this.taxPercentage = parseFloat(this.taxSettings.customRate) || 0;
            }
            this.calculateTotals();
            this.closeAllModals();
        },

        applyRoundSettings() {
            if (this.roundSettings.enabled) {
                const precision = parseFloat(this.roundSettings.precision) || 0.05;
                let roundedTotal = this.total;

                if (this.roundSettings.method === 'up') {
                    roundedTotal = Math.ceil(this.total / precision) * precision;
                } else if (this.roundSettings.method === 'down') {
                    roundedTotal = Math.floor(this.total / precision) * precision;
                } else {
                    roundedTotal = Math.round(this.total / precision) * precision;
                }

                this.roundSettings.amount = roundedTotal - this.total;
                this.total = roundedTotal;
            } else {
                this.roundSettings.amount = 0;
                this.calculateTotals(); // Recalculate without rounding
            }
            this.closeAllModals();
        },

        updateDiscountType() {
            // Reset values when type changes
            if (this.discountSettings.type === 'percentage') {
                this.discountSettings.amount = 0;
            } else {
                this.discountSettings.percentage = 0;
            }
        },

        updateTaxType() {
            this.updateTaxLabel();
        },

        updateTaxLabel() {
            if (this.taxSettings.type === 'sst') {
                this.taxSettings.label = 'SST (6%)';
                this.taxSettings.percentage = 6;
            } else if (this.taxSettings.type === 'gst') {
                this.taxSettings.label = 'GST (10%)';
                this.taxSettings.percentage = 10;
            } else if (this.taxSettings.type === 'vat') {
                this.taxSettings.label = 'VAT (5%)';
                this.taxSettings.percentage = 5;
            } else {
                this.taxSettings.label = this.taxSettings.customLabel || 'Tax';
                this.taxSettings.percentage = this.taxSettings.customRate;
            }
        },

        updateRoundingPreview() {
            if (this.roundSettings.enabled) {
                const precision = parseFloat(this.roundSettings.precision) || 0.05;
                let roundedTotal = this.total;

                if (this.roundSettings.method === 'up') {
                    roundedTotal = Math.ceil(this.total / precision) * precision;
                } else if (this.roundSettings.method === 'down') {
                    roundedTotal = Math.floor(this.total / precision) * precision;
                } else {
                    roundedTotal = Math.round(this.total / precision) * precision;
                }

                this.roundSettings.amount = roundedTotal - this.total;
            } else {
                this.roundSettings.amount = 0;
            }
        },

        // Date Formatting Methods
        formatDateDDMMYYYY(date) {
            const d = new Date(date);
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        },

        parseDDMMYYYY(dateString) {
            const parts = dateString.replace(/[^\d]/g, '');
            if (parts.length >= 8) {
                const day = parts.substr(0, 2);
                const month = parts.substr(2, 2);
                const year = parts.substr(4, 4);
                return new Date(year, month - 1, day);
            }
            return null;
        },

        updateInvoiceDate() {
            // Auto-format as user types
            let value = this.invoiceDateDisplay.replace(/[^\d]/g, '');
            if (value.length >= 2) {
                value = value.substr(0, 2) + '/' + value.substr(2);
            }
            if (value.length >= 5) {
                value = value.substr(0, 5) + '/' + value.substr(5, 4);
            }
            this.invoiceDateDisplay = value.substr(0, 10);

            // Update internal date if valid
            const parsed = this.parseDDMMYYYY(this.invoiceDateDisplay);
            if (parsed) {
                this.invoiceDate = parsed.toISOString().split('T')[0];
            }
        },

        updateDueDate() {
            // Auto-format as user types
            let value = this.dueDateDisplay.replace(/[^\d]/g, '');
            if (value.length >= 2) {
                value = value.substr(0, 2) + '/' + value.substr(2);
            }
            if (value.length >= 5) {
                value = value.substr(0, 5) + '/' + value.substr(5, 4);
            }
            this.dueDateDisplay = value.substr(0, 10);

            // Update internal date if valid
            const parsed = this.parseDDMMYYYY(this.dueDateDisplay);
            if (parsed) {
                this.dueDate = parsed.toISOString().split('T')[0];
            }
        },

        // Customer Search - Unified search across customers, leads, and quotations
        searchCustomers() {
            if (this.customerSearch.length < 2) {
                this.customerResults = [];
                return;
            }

            fetch(`/quotations/search-customers-leads?q=${encodeURIComponent(this.customerSearch)}`)
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

            // Capture customer segment name for pricing
            this.customerSegmentName = customer.customer_segment || '';

            // Set shipping same as billing by default
            this.shippingSameAsBilling = true;

            // Pre-fill shipping address from customer data
            this.updateShippingAddress();
        },

        // Toggle shipping same as billing
        toggleShippingSameAsBilling() {
            if (this.shippingSameAsBilling) {
                this.updateShippingAddress();
            }
        },

        // Update shipping address from selected customer
        updateShippingAddress() {
            if (this.shippingSameAsBilling && this.selectedCustomer.name) {
                this.shippingInfo = {
                    name: this.selectedCustomer.name,
                    address: this.selectedCustomer.address || '',
                    city: this.selectedCustomer.city || '',
                    state: this.selectedCustomer.state || '',
                    postal_code: this.selectedCustomer.postal_code || ''
                };
            }
        },

        // Phone Number Formatting
        formatPhoneNumber() {
            let phone = this.newCustomer.phone.replace(/\D/g, ''); // Remove all non-digits

            // Handle Malaysian phone formats
            if (phone.length > 0) {
                if (phone.startsWith('60')) {
                    // International format: +60X-XXX-XXXX
                    phone = phone.substring(2); // Remove country code for formatting
                    if (phone.length >= 2) {
                        phone = phone.substring(0, 2) + '-' + phone.substring(2);
                    }
                    if (phone.length >= 6) {
                        phone = phone.substring(0, 6) + '-' + phone.substring(6, 10);
                    }
                    this.newCustomer.phone = '+60' + phone;
                } else if (phone.startsWith('0')) {
                    // Local format: 01X-XXX-XXXX
                    if (phone.length >= 3) {
                        phone = phone.substring(0, 3) + '-' + phone.substring(3);
                    }
                    if (phone.length >= 7) {
                        phone = phone.substring(0, 7) + '-' + phone.substring(7, 11);
                    }
                    this.newCustomer.phone = phone;
                } else {
                    // Assume local number, add 0 prefix
                    if (phone.length >= 2) {
                        phone = '0' + phone.substring(0, 2) + '-' + phone.substring(2);
                    }
                    if (phone.length >= 6) {
                        phone = phone.substring(0, 6) + '-' + phone.substring(6, 10);
                    }
                    this.newCustomer.phone = phone;
                }
            }
        },

        validatePhoneNumber(phone) {
            // Malaysian phone number patterns
            const patterns = [
                /^(\+?60|0)[1-9]\d{1}-\d{3}-\d{4}$/, // Standard format with dashes
                /^(\+?60|0)[1-9]\d{7,8}$/ // Without dashes
            ];

            return patterns.some(pattern => pattern.test(phone.replace(/\s/g, '')));
        },

        // Create New Customer
        createCustomer() {
            // Validate required fields
            if (!this.newCustomer.name || !this.newCustomer.name.trim()) {
                this.$dispatch('notify', {
                    type: 'error',
                    message: 'Please enter a customer name'
                });
                return;
            }

            if (!this.newCustomer.phone || !this.newCustomer.phone.trim()) {
                this.$dispatch('notify', {
                    type: 'error',
                    message: 'Please enter a phone number'
                });
                return;
            }

            // Validate phone number format
            if (!this.validatePhoneNumber(this.newCustomer.phone)) {
                this.$dispatch('notify', {
                    type: 'error',
                    message: 'Please enter a valid Malaysian phone number (e.g., 012-345-6789 or +601-234-5678)'
                });
                return;
            }

            fetch('/customers', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(this.newCustomer)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Failed to create customer');
                    });
                }
                return response.json();
            })
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
                // Handle validation errors specifically
                if (error.message.includes('phone')) {
                    this.$dispatch('notify', { type: 'error', message: 'This phone number is already registered with another customer in your company.' });
                } else {
                    this.$dispatch('notify', { type: 'error', message: error.message || 'Failed to create customer' });
                }
            });
        },

        resetNewCustomerForm() {
            this.newCustomer = {
                name: '',
                company_name: '',
                phone: '',
                email: '',
                address: '',
                city: '',
                state: '',
                postal_code: '',
                customer_segment_id: '',
                notes: ''
            };
        },

        // Pricing Book Search with debounce
        searchPricingItems(index) {
            const item = this.lineItems[index];
            if (item.description.length < 2) {
                this.pricingResults[index] = [];
                this.showPricingDropdown[index] = false;
                return;
            }

            // Clear previous timeout
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            // Set new timeout for debounced search
            this.searchTimeout = setTimeout(() => {
                fetch(`/api/pricing-items/search?q=${encodeURIComponent(item.description)}`)
                    .then(response => response.json())
                    .then(data => {
                        this.pricingResults[index] = data.items || [];
                        this.showPricingDropdown[index] = true;
                    })
                    .catch(error => {
                        console.error('Pricing search error:', error);
                        this.pricingResults[index] = [];
                        this.showPricingDropdown[index] = false;
                    });
            }, 300); // 300ms debounce delay
        },

        selectPricingItem(index, pricingItem) {
            console.log('Selecting pricing item:', pricingItem);
            console.log('Customer segment name:', this.customerSegmentName);
            console.log('Segment pricing available:', pricingItem.segment_pricing);

            // Auto-apply customer's segment price if available, otherwise use base price
            let selectedPrice = parseFloat(pricingItem.unit_price_raw || pricingItem.unit_price);
            let selectedSegment = 'Standard';

            if (this.customerSegmentName && pricingItem.segment_pricing && pricingItem.segment_pricing[this.customerSegmentName]) {
                selectedPrice = parseFloat(pricingItem.segment_pricing[this.customerSegmentName]);
                selectedSegment = this.customerSegmentName;
                console.log('Using segment price:', selectedPrice, 'for segment:', selectedSegment);
            } else {
                console.log('Using standard price:', selectedPrice);
            }

            // Update line item immediately
            this.lineItems[index].description = pricingItem.name;
            this.lineItems[index].unit_price = selectedPrice;
            this.lineItems[index].pricing_item_id = pricingItem.id;
            this.lineItems[index].item_code = pricingItem.item_code;
            this.lineItems[index].selected_segment = selectedSegment;

            // Store full segment pricing data for inline switching
            this.lineItems[index].segment_pricing = pricingItem.segment_pricing || {};
            this.lineItems[index].base_price = parseFloat(pricingItem.unit_price_raw || pricingItem.unit_price);

            console.log('Line item updated:', this.lineItems[index]);

            // Hide dropdown and recalculate
            this.showPricingDropdown[index] = false;
            this.calculateTotals();
        },

        changeSegmentPricing(index, segmentName) {
            const item = this.lineItems[index];

            if (segmentName === 'Standard') {
                // Use base price
                item.unit_price = item.base_price;
                item.selected_segment = 'Standard';
            } else if (segmentName === 'Custom') {
                // Keep current price but mark as custom
                item.selected_segment = 'Custom';
                // Don't change price - user will manually edit
            } else if (item.segment_pricing && item.segment_pricing[segmentName]) {
                // Use segment price
                item.unit_price = parseFloat(item.segment_pricing[segmentName]);
                item.selected_segment = segmentName;
            }

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
        // Service Template Selection
        openTemplateModal() {
            this.showTemplateModal = true;
            this.templateTab = 'full';
            this.selectedSectionIds = [];
            this.sectionFilterTemplate = '';
            this.loadServiceTemplates();
            this.loadAllSections();
        },

        async loadServiceTemplates() {
            this.loadingTemplates = true;
            try {
                const response = await fetch('/api/service-templates', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                const data = await response.json();
                this.serviceTemplates = data.templates || [];
            } catch (error) {
                console.error('Error loading service templates:', error);
                this.$dispatch('notify', { type: 'error', message: 'Failed to load service templates' });
            } finally {
                this.loadingTemplates = false;
            }
        },

        selectServiceTemplate(template) {
            this.selectedTemplate = template;
            this.sections = [];

            // Populate sections from template
            template.sections.forEach(section => {
                const newSection = {
                    id: this.sectionIdCounter++,
                    name: section.name,
                    description: section.description,
                    items: []
                };

                // Populate items within section
                section.items.forEach(item => {
                    newSection.items.push({
                        id: this.itemIdCounter++,
                        description: item.description,
                        quantity: item.default_quantity || 1,
                        unit_price: item.unit_price || 0
                    });
                });

                this.sections.push(newSection);
            });

            this.showTemplateModal = false;
            this.manualMode = false;
            this.calculateTotals();
            this.$dispatch('notify', { type: 'success', message: 'Service template loaded successfully!' });
        },

        // Manual Section Management
        toggleManualMode() {
            this.manualMode = !this.manualMode;
            if (this.manualMode && this.sections.length === 0) {
                this.addSection();
            }
        },

        addSection() {
            this.sections.push({
                id: this.sectionIdCounter++,
                name: '',
                description: '',
                items: []
            });
        },

        removeSection(index) {
            if (this.sections.length > 1) {
                this.sections.splice(index, 1);
                this.calculateTotals();
            }
        },

        addItemToSection(sectionIndex) {
            this.sections[sectionIndex].items.push({
                id: this.itemIdCounter++,
                description: '',
                quantity: 1,
                unit_price: 0
            });
        },

        removeItemFromSection(sectionIndex, itemIndex) {
            if (this.sections[sectionIndex].items.length > 1) {
                this.sections[sectionIndex].items.splice(itemIndex, 1);
                this.calculateTotals();
            }
        },

        getSectionSubtotal(section) {
            return section.items.reduce((total, item) => {
                return total + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0));
            }, 0);
        },

        // Template Section Loading Methods
        async loadAllSections() {
            try {
                const response = await fetch('/api/service-template-sections');
                const data = await response.json();
                this.allSections = data || [];
            } catch (error) {
                console.error('Error loading sections:', error);
                alert('Failed to load template sections. Please try again.');
            }
        },

        loadSelectedSections(template) {
            if (!template || !template.sections) return;

            template.sections.forEach(section => {
                const newSection = {
                    id: this.sectionIdCounter++,
                    name: section.name || '',
                    description: section.description || '',
                    items: (section.items || []).map(item => ({
                        id: this.itemIdCounter++,
                        description: item.description || '',
                        details: item.details || '',
                        unit: item.unit || '',
                        quantity: parseFloat(item.default_quantity) || 1,
                        unit_price: parseFloat(item.default_unit_price) || 0,
                        amount_override: item.amount_override ? parseFloat(item.amount_override) : null,
                        amount_manually_edited: item.amount_manually_edited || false
                    }))
                };
                this.sections.push(newSection);
            });

            this.calculateTotals();
        },

        addSectionFromTemplate(selectedSectionIds) {
            if (!selectedSectionIds || selectedSectionIds.length === 0) {
                alert('Please select at least one section.');
                return;
            }

            selectedSectionIds.forEach(sectionId => {
                const section = this.allSections.find(s => s.id === sectionId);
                if (!section) return;

                const newSection = {
                    id: this.sectionIdCounter++,
                    name: section.name || '',
                    description: section.description || '',
                    items: (section.items || []).map(item => ({
                        id: this.itemIdCounter++,
                        description: item.description || '',
                        details: item.details || '',
                        unit: item.unit || '',
                        quantity: parseFloat(item.default_quantity) || 1,
                        unit_price: parseFloat(item.default_unit_price) || 0,
                        amount_override: item.amount_override ? parseFloat(item.amount_override) : null,
                        amount_manually_edited: item.amount_manually_edited || false
                    }))
                };
                this.sections.push(newSection);
            });

            this.calculateTotals();
            this.showTemplateModal = false;
            this.selectedSectionIds = [];
            this.sectionFilterTemplate = '';
        },

        // Amount Calculation Methods
        recalculateItemAmount(item) {
            const quantity = parseFloat(item.quantity) || 0;
            const unitPrice = parseFloat(item.unit_price) || 0;
            const calculatedAmount = quantity * unitPrice;

            // Only recalculate if not manually overridden
            if (!item.amount_override && !item.amount_manually_edited) {
                this.calculateTotals();
            }

            return calculatedAmount;
        },

        handleAmountOverride(item, newAmount) {
            const amount = parseFloat(newAmount);
            if (isNaN(amount) || amount < 0) {
                alert('Please enter a valid positive amount.');
                return;
            }

            item.amount_override = amount;
            item.amount_manually_edited = true;
            this.calculateTotals();
        },

        resetAmountOverride(item) {
            item.amount_override = null;
            item.amount_manually_edited = false;
            this.calculateTotals();
        },

        // Duplication Methods
        duplicateItem(sectionIndex, itemIndex) {
            const originalItem = this.sections[sectionIndex].items[itemIndex];
            const duplicatedItem = {
                id: this.itemIdCounter++,
                description: originalItem.description + ' (Copy)',
                details: originalItem.details || '',
                unit: originalItem.unit || '',
                quantity: parseFloat(originalItem.quantity) || 1,
                unit_price: parseFloat(originalItem.unit_price) || 0,
                amount_override: originalItem.amount_override ? parseFloat(originalItem.amount_override) : null,
                amount_manually_edited: originalItem.amount_manually_edited || false
            };

            this.sections[sectionIndex].items.splice(itemIndex + 1, 0, duplicatedItem);
            this.calculateTotals();
        },

        duplicateSection(sectionIndex) {
            const originalSection = this.sections[sectionIndex];
            const duplicatedSection = {
                id: this.sectionIdCounter++,
                name: originalSection.name + ' (Copy)',
                description: originalSection.description || '',
                items: (originalSection.items || []).map(item => ({
                    id: this.itemIdCounter++,
                    description: item.description || '',
                    details: item.details || '',
                    unit: item.unit || '',
                    quantity: parseFloat(item.quantity) || 1,
                    unit_price: parseFloat(item.unit_price) || 0,
                    amount_override: item.amount_override ? parseFloat(item.amount_override) : null,
                    amount_manually_edited: item.amount_manually_edited || false
                }))
            };

            this.sections.splice(sectionIndex + 1, 0, duplicatedSection);
            this.calculateTotals();
        },

        // Financial Calculations (Section-based)
        calculateTotals() {
            // Calculate subtotal from all sections
            this.subtotal = this.sections.reduce((total, section) => {
                return total + this.getSectionSubtotal(section);
            }, 0);

            // Keep existing discount calculation
            if (this.discountPercentage > 0) {
                this.discountAmount = (this.subtotal * parseFloat(this.discountPercentage || 0)) / 100;
            }

            const afterDiscount = this.subtotal - (parseFloat(this.discountAmount) || 0);
            this.taxAmount = (afterDiscount * parseFloat(this.taxPercentage || 0)) / 100;
            this.total = afterDiscount + this.taxAmount;
        },

        calculateTotalsFromPercentage() {
            this.subtotal = this.sections.reduce((total, section) => {
                return total + this.getSectionSubtotal(section);
            }, 0);

            this.discountAmount = (this.subtotal * parseFloat(this.discountPercentage || 0)) / 100;
            const afterDiscount = this.subtotal - this.discountAmount;
            this.taxAmount = (afterDiscount * parseFloat(this.taxPercentage || 0)) / 100;
            this.total = afterDiscount + this.taxAmount;
        },

        calculateTotalsFromAmount() {
            this.subtotal = this.sections.reduce((total, section) => {
                return total + this.getSectionSubtotal(section);
            }, 0);

            // Calculate percentage from amount
            this.discountPercentage = this.subtotal > 0 ? ((parseFloat(this.discountAmount) || 0) / this.subtotal) * 100 : 0;
            const afterDiscount = this.subtotal - (parseFloat(this.discountAmount) || 0);
            this.taxAmount = (afterDiscount * parseFloat(this.taxPercentage || 0)) / 100;
            this.total = afterDiscount + this.taxAmount;
        },

        // Currency Formatting
        formatCurrency(amount) {
            return 'RM ' + parseFloat(amount || 0).toFixed(2);
        },

        // Show Notification
        showNotification(message, type = 'success') {
            // Create a simple alert for now - can be enhanced with a proper notification system
            if (type === 'success') {
                alert(message);
            } else if (type === 'error') {
                alert('Error: ' + message);
            }
        },

        // Get Signature Image URL (handles both data URLs and storage paths with cache buster)
        getSignatureImageUrl(path) {
            if (!path) return '';
            if (path.startsWith('data:')) {
                return path; // Base64 data URL
            }
            // Storage path - extract filename and cache buster separately
            const parts = path.split('?');
            const filename = parts[0];
            const cacheBuster = parts[1] || '';
            return `/storage/${filename}${cacheBuster ? '?' + cacheBuster : ''}`;
        },

        // Computed Properties
        get balanceDue() {
            return this.total - this.paidAmount;
        },

        get visibleColumns() {
            return this.columns
                .filter(col => col.visible)
                .sort((a, b) => a.order - b.order);
        },

        get filteredSections() {
            if (!this.sectionFilterTemplate) {
                return this.allSections;
            }
            return this.allSections.filter(section =>
                section.template_name.toLowerCase().includes(this.sectionFilterTemplate.toLowerCase())
            );
        },

        // Load Invoice Settings
        loadInvoiceSettings() {
            fetch('/invoice-settings/api')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.settings) {
                        // Apply default settings
                        this.optionalSections = { ...this.optionalSections, ...data.settings.sections };
                        this.notes = data.settings.default_notes || this.notes;
                        this.terms = data.settings.default_terms || this.terms;

                        // Load company signature settings (optional authorized signatory)
                        if (data.settings.company_signature) {
                            this.companySignature = {
                                name: data.settings.company_signature.name || '',
                                title: data.settings.company_signature.title || '',
                                image_path: data.settings.company_signature.image_path || ''
                            };
                        }

                        // Note: User signature is already loaded from auth()->user() in data initialization
                        // Company signature toggles (show_company_signature, show_customer_signature) are loaded via sections
                    }
                })
                .catch(error => {
                    console.error('Failed to load invoice settings:', error);
                });

            // Load columns configuration
            fetch('/invoice-settings/columns')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.columns) {
                        this.columns = data.columns;
                    }
                })
                .catch(error => {
                    console.error('Failed to load columns configuration:', error);
                });
        },

        // Actions
        previewPDF() {
            // Validation
            if (!this.selectedCustomer.id) {
                this.$dispatch('notify', { type: 'error', message: 'Please select a customer first' });
                return;
            }

            if (this.lineItems.filter(item => item.description.trim() !== '').length === 0) {
                this.$dispatch('notify', { type: 'error', message: 'Please add at least one line item' });
                return;
            }

            // If already saved as draft, open PDF preview in new tab
            if (this.currentInvoiceId) {
                window.open(`/invoices/${this.currentInvoiceId}/preview`, '_blank');
                this.$dispatch('notify', { type: 'success', message: 'Opening PDF preview...' });
                return;
            }

            // Show HTML preview modal (no database save)
            this.modals.preview = true;
            this.$dispatch('notify', { type: 'info', message: 'Showing preview. Save invoice to generate PDF.' });
        },

        saveInvoice() {
            const invoiceData = this.getInvoiceData();

            fetch('/api/invoices', {
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
                    this.$dispatch('notify', { type: 'success', message: 'Invoice saved successfully! Redirecting...' });
                    // Redirect to invoice view where PDF preview is available
                    setTimeout(() => {
                        window.location.href = `/invoices/${data.invoice.id}`;
                    }, 1500);
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
                // Lead linkage (if customer is from lead)
                lead_id: this.selectedCustomer.lead_id || null,

                // Customer information from selected customer
                customer_name: this.selectedCustomer.name || '',
                customer_company: this.selectedCustomer.company_name || '',
                customer_phone: this.selectedCustomer.phone || '',
                customer_email: this.selectedCustomer.email || '',
                customer_address: this.selectedCustomer.address || this.selectedCustomer.full_address || '',
                customer_city: this.selectedCustomer.city || '',
                customer_state: this.selectedCustomer.state || '',
                customer_postal_code: this.selectedCustomer.postal_code || '',
                customer_segment_id: this.selectedCustomer.customer_segment_id || null,
                company_logo_id: this.selectedLogoId || null,
                company_brand_id: this.selectedBrandId || null,

                // Invoice details
                title: `Invoice for ${this.selectedCustomer.name || 'Customer'}`,
                invoice_date: this.invoiceDate,
                due_date: this.dueDate,
                subtotal: this.subtotal,
                discount_percentage: this.discountPercentage,
                discount_amount: this.discountAmount,
                tax_percentage: this.taxPercentage,
                tax_amount: this.taxAmount,
                total: this.total,
                amount_due: this.total, // Initially equal to total
                notes: this.notes,
                terms_conditions: this.terms,
                payment_instructions: this.paymentInstructions,

                // Shipping information
                shipping_info: this.shippingSameAsBilling ? {
                    same_as_billing: true,
                    name: this.selectedCustomer.name || '',
                    address: this.selectedCustomer.address || '',
                    city: this.selectedCustomer.city || '',
                    state: this.selectedCustomer.state || '',
                    postal_code: this.selectedCustomer.postal_code || ''
                } : {
                    same_as_billing: false,
                    name: this.shippingInfo.name,
                    address: this.shippingInfo.address,
                    city: this.shippingInfo.city,
                    state: this.shippingInfo.state,
                    postal_code: this.shippingInfo.postal_code
                },

                // Line items
                items: this.lineItems.filter(item => item.description.trim() !== '').map(item => ({
                    description: item.description,
                    quantity: parseFloat(item.quantity) || 1,
                    unit_price: parseFloat(item.unit_price) || 0,
                    item_code: item.item_code || '',
                    specifications: item.specifications || '',
                    notes: item.notes || '',
                    source_type: item.pricing_item_id ? 'pricing_item' : 'manual',
                    source_id: item.pricing_item_id || null
                }))
            };
        },

        applyTemplate() {
            // TODO: Implement template application
            this.$dispatch('notify', { type: 'info', message: 'Template feature coming soon!' });
        },

        // Template Management Methods
        async loadNotesTemplates() {
            await this.loadTemplates('notes', 'Select Notes Template');
        },

        async loadTermsTemplates() {
            await this.loadTemplates('terms', 'Select Terms & Conditions Template');
        },

        async loadPaymentInstructionTemplates() {
            await this.loadTemplates('payment_instructions', 'Select Payment Instructions Template');
        },

        async loadTemplates(type, title) {
            this.templateModal.type = type;
            this.templateModal.title = title;
            this.templateModal.loading = true;
            this.templateModal.templates = [];
            this.showTemplateModal = true;

            try {
                const response = await fetch('/api/invoice-note-templates/by-type?type=' + type, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.templateModal.templates = data.templates;

                    // Auto-select default template if available and current field is empty
                    if (data.default && this.isFieldEmpty(type)) {
                        this.selectTemplate(data.default);
                        this.showTemplateModal = false;
                    }
                } else {
                    throw new Error('Failed to load templates');
                }
            } catch (error) {
                console.error('Error loading templates:', error);
                this.$dispatch('notify', {
                    type: 'error',
                    message: 'Failed to load templates. Please try again.'
                });
            } finally {
                this.templateModal.loading = false;
            }
        },

        selectTemplate(template) {
            switch (this.templateModal.type) {
                case 'notes':
                    this.notes = template.content;
                    break;
                case 'terms':
                    this.terms = template.content;
                    break;
                case 'payment_instructions':
                    this.paymentInstructions = template.content;
                    break;
            }
            this.showTemplateModal = false;
            this.$dispatch('notify', {
                type: 'success',
                message: 'Template applied successfully!'
            });
        },

        isFieldEmpty(type) {
            switch (type) {
                case 'notes':
                    return !this.notes || this.notes.trim() === '' || this.notes === 'Thank you for your business!';
                case 'terms':
                    return !this.terms || this.terms.trim() === '' || this.terms === 'Payment is due within 30 days. Late payments may incur additional charges.';
                case 'payment_instructions':
                    return !this.paymentInstructions || this.paymentInstructions.trim() === '';
                default:
                    return true;
            }
        },

        // Logo Bank Methods
        async loadLogoBank() {
            try {
                const response = await fetch('{{ route("logo-bank.list") }}', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.logoBank = data.logos;

                    // If no logo is currently selected and there's a default, select it
                    if (!this.selectedLogoId && data.logos.length > 0) {
                        const defaultLogo = data.logos.find(logo => logo.is_default);
                        if (defaultLogo) {
                            this.selectedLogoId = defaultLogo.id;
                            this.selectedLogoUrl = defaultLogo.url;
                        }
                    }
                } else {
                    throw new Error('Failed to load logo bank');
                }
            } catch (error) {
                console.error('Error loading logo bank:', error);
                this.$dispatch('notify', {
                    type: 'error',
                    message: 'Failed to load logo bank. Please try again.'
                });
            }
        },

        selectLogo(logoId, logoUrl) {
            this.selectedLogoId = logoId;
            this.selectedLogoUrl = logoUrl;
            this.showLogoSelector = false;
            this.$dispatch('notify', {
                type: 'success',
                message: 'Logo selected successfully!'
            });
        },

        // Save current content as template
        async saveAsTemplate(type, content) {
            if (!content || content.trim() === '') {
                this.$dispatch('notify', {
                    type: 'error',
                    message: 'Please enter some content before saving as template.'
                });
                return;
            }

            // Ask for template name
            const name = prompt('Enter a name for this template (leave empty for auto-generated name):');

            // User cancelled
            if (name === null) {
                return;
            }

            try {
                const response = await fetch('{{ route("invoice-note-templates.quick-save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        type: type,
                        content: content,
                        name: name || null,
                        set_as_default: false
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.$dispatch('notify', {
                        type: 'success',
                        message: data.message || 'Template saved successfully!'
                    });
                } else {
                    throw new Error(data.message || 'Failed to save template');
                }
            } catch (error) {
                console.error('Error saving template:', error);
                this.$dispatch('notify', {
                    type: 'error',
                    message: error.message || 'Failed to save template. Please try again.'
                });
            }
        },

        async saveAsDefault(type, content) {
            if (!content || content.trim() === '') {
                this.$dispatch('notify', {
                    type: 'error',
                    message: 'Please enter some content before setting as default.'
                });
                return;
            }

            // Ask for template name
            const name = prompt('Enter a name for this default template (leave empty for auto-generated name):');

            // User cancelled
            if (name === null) {
                return;
            }

            try {
                const response = await fetch('{{ route("invoice-note-templates.quick-save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        type: type,
                        content: content,
                        name: name || null,
                        set_as_default: true
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Update the current data to reflect the new default
                    if (type === 'notes') {
                        this.notes = content;
                    } else if (type === 'terms') {
                        this.terms = content;
                    } else if (type === 'payment_instructions') {
                        this.paymentInstructions = content;
                    }

                    this.$dispatch('notify', {
                        type: 'success',
                        message: 'Set as default successfully! This will be auto-loaded for all new invoices.'
                    });
                } else {
                    throw new Error(data.message || 'Failed to set as default');
                }
            } catch (error) {
                console.error('Error setting default template:', error);
                this.$dispatch('notify', {
                    type: 'error',
                    message: error.message || 'Failed to set as default. Please try again.'
                });
            }
        }
    };
}
</script>
@endsection
