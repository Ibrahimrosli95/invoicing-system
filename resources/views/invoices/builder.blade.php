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
                                <h2 class="text-2xl font-bold text-blue-600 leading-tight">
                                    {{ auth()->user()->company->name ?? 'Company Name' }}
                                </h2>
                                <div class="text-sm text-gray-700 space-y-2 leading-relaxed">
                                    <div class="font-medium">{{ auth()->user()->company->address ?? '123 Business Street' }}</div>
                                    <div>{{ auth()->user()->company->city ?? 'City' }}, {{ auth()->user()->company->state ?? 'State' }} {{ auth()->user()->company->postal_code ?? '12345' }}</div>
                                    <div class="pt-2 space-y-1">
                                        <div>
                                            <span class="inline-block w-16 text-gray-500 text-xs uppercase tracking-wide">Phone:</span>
                                            <span class="font-medium">{{ auth()->user()->company->phone ?? '+60 12-345 6789' }}</span>
                                        </div>
                                        <div>
                                            <span class="inline-block w-16 text-gray-500 text-xs uppercase tracking-wide">Email:</span>
                                            <span class="font-medium">{{ auth()->user()->company->email ?? 'info@company.com' }}</span>
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
                                <div class="flex space-x-2">
                                    <button type="button" @click="showLogoSelector = true" class="px-3 py-1 text-xs font-medium text-amber-700 bg-amber-100 border border-amber-200 rounded-full hover:bg-amber-200 transition-colors">
                                        Choose Logo
                                    </button>
                                    <a href="{{ route('logo-bank.index') }}" target="_blank" class="px-3 py-1 text-xs font-medium text-blue-700 bg-blue-100 border border-blue-200 rounded-full hover:bg-blue-200 transition-colors">
                                        Manage Logos
                                    </a>
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

                    <!-- Line Items Section -->
                    <div class="px-4 md:px-6 lg:px-8 py-6 bg-white border border-gray-200 rounded-lg mx-2 md:mx-4 lg:mx-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Invoice Items</h3>

                        <!-- Line Items Table - Desktop -->
                        <div class="hidden md:block">
                        <div class="overflow-hidden rounded-xl border border-gray-200">
                            <table class="w-full table-fixed">
                            <colgroup>
                                <col style="width: 6%;">
                                <col style="width: 48%;">
                                <col style="width: 12%;">
                                <col style="width: 14%;">
                                <col style="width: 14%;">
                                <col style="width: 6%;">
                            </colgroup>
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center" style="width: 6%;">SI</th>
                                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left" style="width: 48%;">Description</th>
                                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center" style="width: 12%;">Qty</th>
                                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-right" style="width: 14%;">Rate (RM)</th>
                                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-right" style="width: 14%;">Amount (RM)</th>
                                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center" style="width: 6%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                <template x-for="(item, index) in lineItems" :key="index">
                                    <tr>
                                        <td class="px-6 py-4 text-center text-sm font-medium text-gray-600" style="width: 6%;" x-text="index + 1"></td>
                                        <td class="px-6 py-4 relative" style="width: 48%;">
                                            <div class="relative">
                                                <input type="text"
                                                       x-model="item.description"
                                                       @input="searchPricingItems(index)"
                                                       @focus="showPricingDropdown[index] = true"
                                                       placeholder="Search pricing book or enter custom description..."
                                                       class="w-full border-0 bg-transparent py-2 text-sm focus:ring-0 min-h-[40px]">

                                                <!-- Pricing Items Dropdown -->
                                                <div x-show="showPricingDropdown[index] && pricingResults[index] && pricingResults[index].length > 0"
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="transform opacity-0 scale-95"
                                                     x-transition:enter-end="transform opacity-100 scale-100"
                                                     @click.outside="showPricingDropdown[index] = false"
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
                                        <td class="px-6 py-4 text-right" style="width: 12%;">
                                            <input type="number" x-model="item.quantity" @input="calculateTotals"
                                                   class="w-full border-0 bg-transparent py-2 text-sm text-right focus:ring-0 min-h-[40px] border-b border-gray-200 focus:border-blue-500" min="1" step="1" style="min-width: 50px;">
                                        </td>
                                        <td class="px-6 py-4 text-right" style="width: 14%;">
                                            <div class="flex flex-col space-y-1">
                                                <!-- Price Input -->
                                                <input type="number" x-model="item.unit_price" @input="calculateTotals"
                                                       class="w-full border-0 bg-transparent py-2 text-sm text-right focus:ring-0 min-h-[40px] border-b border-gray-200 focus:border-blue-500" min="0" step="0.01" style="min-width: 80px;">

                                                <!-- Segment Pricing Selector -->
                                                <div x-show="item.segment_pricing && Object.keys(item.segment_pricing).length > 0"
                                                     class="relative">
                                                    <label class="block font-normal text-gray-400 mb-0.5 uppercase tracking-wide" style="font-size: 7px !important;">Pricing Tier</label>
                                                    <select x-model="item.selected_segment"
                                                            @change="changeSegmentPricing(index, item.selected_segment)"
                                                            class="w-full text-xs py-1 pr-6 border-0 bg-gray-50 text-gray-600 rounded focus:ring-1 focus:ring-blue-500 cursor-pointer">
                                                        <!-- Standard Price -->
                                                        <option value="Standard">Standard</option>

                                                        <!-- Dynamic Segment Prices -->
                                                        <template x-for="(price, segmentName) in item.segment_pricing" :key="segmentName">
                                                            <option :value="segmentName" x-text="segmentName"></option>
                                                        </template>

                                                        <!-- Custom Price Option -->
                                                        <option value="Custom">Custom</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium" style="width: 14%;">
                                            RM <span x-text="(item.quantity * item.unit_price).toFixed(2)">0.00</span>
                                        </td>
                                        <td class="px-6 py-4 text-center" style="width: 6%;">
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
                                    <td colspan="6" class="px-6 py-4">
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
                    </div>

                        <!-- Line Items Cards - Mobile -->
                        <div class="md:hidden space-y-4">
                        <template x-for="(item, index) in lineItems" :key="index">
                            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-medium text-gray-900" x-text="`Item ${index + 1}`"></h4>
                                        <button @click="removeLineItem(index)" type="button"
                                                class="text-red-400 hover:text-red-600">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="px-4 py-3 space-y-3">
                                    <!-- Description -->
                                    <div class="relative">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                                        <input type="text" x-model="item.description"
                                               @input="searchPricingItems(index)"
                                               @focus="showPricingDropdown[index] = true"
                                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="Search pricing book or enter custom description...">

                                        <!-- Pricing Items Dropdown -->
                                        <div x-show="showPricingDropdown[index] && pricingResults[index] && pricingResults[index].length > 0"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             @click.outside="showPricingDropdown[index] = false"
                                             class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
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

                                    <!-- Quantity and Rate Row -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Qty</label>
                                            <input type="number" x-model="item.quantity" @input="calculateTotals"
                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-right focus:ring-blue-500 focus:border-blue-500"
                                                   min="1" step="1">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Rate (RM)</label>
                                            <input type="number" x-model="item.unit_price" @input="calculateTotals"
                                                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-right focus:ring-blue-500 focus:border-blue-500"
                                                   min="0" step="0.01">

                                            <!-- Segment Pricing Selector -->
                                            <div x-show="item.segment_pricing && Object.keys(item.segment_pricing).length > 0"
                                                 class="mt-2">
                                                <label class="block font-normal text-gray-400 mb-1 uppercase tracking-wide" style="font-size: 8px !important;">Pricing Tier</label>
                                                <select x-model="item.selected_segment"
                                                        @change="changeSegmentPricing(index, item.selected_segment)"
                                                        class="w-full text-sm py-1.5 px-2 border border-gray-300 bg-white text-gray-700 rounded focus:ring-1 focus:ring-blue-500 cursor-pointer">
                                                    <!-- Standard Price -->
                                                    <option value="Standard">Standard</option>

                                                    <!-- Dynamic Segment Prices -->
                                                    <template x-for="(price, segmentName) in item.segment_pricing" :key="segmentName">
                                                        <option :value="segmentName" x-text="segmentName"></option>
                                                    </template>

                                                    <!-- Custom Price Option -->
                                                    <option value="Custom">Custom</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Total -->
                                    <div class="pt-2 border-t border-gray-100">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-700">Total:</span>
                                            <span class="text-sm font-semibold text-gray-900">
                                                RM <span x-text="(item.quantity * item.unit_price).toFixed(2)">0.00</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Add Item Button - Mobile -->
                        <button @click="addLineItem" type="button"
                                class="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Line Item
                        </button>
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
                                            <button @click="saveAsTemplate('payment_instructions', paymentInstructions)"
                                                    class="bg-green-100 hover:bg-green-200 border border-green-300 rounded-full px-3 py-1 text-xs text-green-700 transition-colors"
                                                    title="Save current content as template">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                                </svg>
                                                Save
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
                                            <button @click="saveAsTemplate('terms', terms)"
                                                    class="bg-green-100 hover:bg-green-200 border border-green-300 rounded-full px-3 py-1 text-xs text-green-700 transition-colors"
                                                    title="Save current content as template">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                                </svg>
                                                Save
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
                                            <button @click="saveAsTemplate('notes', notes)"
                                                    class="bg-green-100 hover:bg-green-200 border border-green-300 rounded-full px-3 py-1 text-xs text-green-700 transition-colors"
                                                    title="Save current content as template">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                                </svg>
                                                Save
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

        // Pricing Book Integration
        showPricingDropdown: {},
        pricingResults: {},
        searchTimeout: null,

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

        // Line Items
        lineItems: [
            { description: '', quantity: 1, unit_price: 0, pricing_item_id: null, item_code: '' }
        ],

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
            round: false
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

            // Keep existing discount calculation
            if (this.discountPercentage > 0) {
                this.discountAmount = (this.subtotal * parseFloat(this.discountPercentage || 0)) / 100;
            }

            const afterDiscount = this.subtotal - (parseFloat(this.discountAmount) || 0);
            this.taxAmount = (afterDiscount * parseFloat(this.taxPercentage || 0)) / 100;
            this.total = afterDiscount + this.taxAmount;
        },

        calculateTotalsFromPercentage() {
            this.subtotal = this.lineItems.reduce((sum, item) => {
                return sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0));
            }, 0);

            this.discountAmount = (this.subtotal * parseFloat(this.discountPercentage || 0)) / 100;
            const afterDiscount = this.subtotal - this.discountAmount;
            this.taxAmount = (afterDiscount * parseFloat(this.taxPercentage || 0)) / 100;
            this.total = afterDiscount + this.taxAmount;
        },

        calculateTotalsFromAmount() {
            this.subtotal = this.lineItems.reduce((sum, item) => {
                return sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0));
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
            if (!this.selectedCustomer.id) {
                this.$dispatch('notify', { type: 'error', message: 'Please select a customer first' });
                return;
            }

            if (this.lineItems.filter(item => item.description.trim() !== '').length === 0) {
                this.$dispatch('notify', { type: 'error', message: 'Please add at least one line item' });
                return;
            }

            // If we already have a draft invoice, just open the preview
            if (this.currentInvoiceId) {
                window.open(`/invoices/${this.currentInvoiceId}/preview`, '_blank');
                this.$dispatch('notify', { type: 'success', message: 'Opening PDF preview...' });
                return;
            }

            // Save as draft first, then open preview
            const invoiceData = this.getInvoiceData();
            invoiceData.status = 'DRAFT'; // Ensure it's marked as draft

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
                    // Store the invoice ID to avoid creating duplicates
                    this.currentInvoiceId = data.invoice.id;

                    // Open PDF preview in new tab
                    window.open(`/invoices/${data.invoice.id}/preview`, '_blank');
                    this.$dispatch('notify', { type: 'success', message: 'Opening PDF preview...' });
                } else {
                    this.$dispatch('notify', { type: 'error', message: data.message || 'Failed to create preview' });
                }
            })
            .catch(error => {
                console.error('Preview error:', error);
                this.$dispatch('notify', { type: 'error', message: 'Failed to create preview' });
            });
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
        }
    };
}
</script>
@endsection
