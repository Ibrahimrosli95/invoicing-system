@extends('layouts.app')

@section('content')
@php
    // Prepare line items data
    $lineItemsData = $quotation->items->map(function($item) {
        return [
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'pricing_item_id' => $item->pricing_item_id,
            'item_code' => $item->item_code ?? '',
            'specifications' => $item->specifications ?? '',
            'notes' => $item->notes ?? ''
        ];
    })->toArray();
@endphp

<div class="min-h-screen bg-gray-50" x-data="quotationEditor()">
    <!-- Header Bar -->
    <div class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-40">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('quotations.show', $quotation) }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-lg font-semibold text-gray-900">Edit Quotation {{ $quotation->number }}</h1>
                <span class="px-2 py-1 text-xs font-medium {{ $quotation->getStatusBadgeColor() }} rounded">{{ $quotation->status }}</span>
            </div>
            <div class="flex items-center space-x-3 relative z-50">
                <button type="button" @click="previewPDF" class="relative z-50 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                    Preview PDF
                </button>
                <button type="button" @click="updateQuotation" class="relative z-50 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer">
                    Update Quotation
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div>
        <!-- Document Preview Area -->
        <div class="flex-1 min-h-screen">
            <div class="max-w-4xl mx-auto px-4 md:px-6 lg:px-8 py-6">
                <!-- Quotation Document -->
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                    <!-- Row 1: Quotation Title at Top Center -->
                    <div class="px-6 md:px-8 lg:px-12 py-6">
                        <div class="text-center mb-6">
                            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 tracking-wide">QUOTATION</h1>
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

                    <!-- Row 3: Customer Billing Details and Quotation Details -->
                    <div class="px-4 md:px-6 lg:px-8 py-6 bg-white border border-gray-200 rounded-lg mx-2 md:mx-4 lg:mx-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Customer Billing Details - Left -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-6">Bill To</h3>

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

                                <!-- Customer Search (hidden when customer is selected) -->
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
                                                </div>
                                            </template>
                                        </div>
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

                            <!-- Quotation Details - Right -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-6">Quotation Details</h3>
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600">Quotation #:</span>
                                        <span class="text-sm font-mono font-semibold" x-text="quotationNumber">{{ $quotation->number }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600">Quotation Date:</span>
                                        <input type="text" x-model="quotationDateDisplay" @input="updateQuotationDate"
                                               placeholder="DD/MM/YYYY" maxlength="10"
                                               class="text-sm font-mono font-semibold border-0 border-b border-gray-300 bg-transparent text-right p-0 focus:ring-0 focus:border-blue-500 w-28">
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600">Valid Until:</span>
                                        <input type="text" x-model="validUntilDisplay" @input="updateValidUntil"
                                               placeholder="DD/MM/YYYY" maxlength="10"
                                               class="text-sm font-mono font-semibold border-0 border-b border-gray-300 bg-transparent text-right p-0 focus:ring-0 focus:border-blue-500 w-28">
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600">Reference #:</span>
                                        <input type="text" x-model="referenceNumber" placeholder="Optional"
                                               class="text-sm font-mono font-semibold border-0 border-b border-gray-300 bg-transparent text-right p-0 focus:ring-0 focus:border-blue-500 w-28">
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-600">Customer Segment:</span>
                                        <select x-model="customerSegmentId" class="text-sm font-medium border-0 border-b border-gray-300 bg-transparent text-right p-0 focus:ring-0 focus:border-blue-500">
                                            <option value="">None</option>
                                            @foreach($customerSegments as $segment)
                                                <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                                            @endforeach
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
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Quotation Items</h3>

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
                                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">SI</th>
                                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-left">Description</th>
                                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Qty</th>
                                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">Rate (RM)</th>
                                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-right">Amount (RM)</th>
                                        <th class="px-6 py-4 text-xs font-medium text-gray-500 uppercase tracking-wider text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                <template x-for="(item, index) in lineItems" :key="index">
                                    <tr>
                                        <td class="px-6 py-4 text-center text-sm font-medium text-gray-600" x-text="index + 1"></td>
                                        <td class="px-6 py-4 relative">
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
                                        <td class="px-6 py-4 text-right">
                                            <input type="number" x-model="item.quantity" @input="calculateTotals"
                                                   class="w-full border-0 bg-transparent py-2 text-sm text-right focus:ring-0 min-h-[40px] border-b border-gray-200 focus:border-blue-500" min="1" step="1">
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <input type="number" x-model="item.unit_price" @input="calculateTotals"
                                                   class="w-full border-0 bg-transparent py-2 text-sm text-right focus:ring-0 min-h-[40px] border-b border-gray-200 focus:border-blue-500" min="0" step="0.01">
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-sm font-medium text-gray-900" x-text="'RM ' + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)).toFixed(2)"></span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <button @click="removeItem(index)" class="text-red-600 hover:text-red-800" x-show="lineItems.length > 1">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Add Line Item Button -->
                        <div class="mt-4">
                            <button type="button" @click="addLineItem" class="w-full px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Line Item
                            </button>
                        </div>
                        </div>
                    </div>

                    <!-- Gap between rows -->
                    <div class="h-2 bg-gray-50"></div>

                    <!-- Totals Section -->
                    <div class="px-4 md:px-6 lg:px-8 py-6 bg-white border border-gray-200 rounded-lg mx-2 md:mx-4 lg:mx-6">
                        <div class="flex justify-end">
                            <div class="w-full lg:w-1/2 space-y-3">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-semibold" x-text="'RM ' + subtotal.toFixed(2)"></span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Discount:</span>
                                    <input type="number" x-model="discountAmount" @input="calculateTotals"
                                           class="w-32 text-right border-0 border-b border-gray-300 bg-transparent py-1 text-sm focus:ring-0 focus:border-blue-500"
                                           min="0" step="0.01">
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Tax:</span>
                                    <input type="number" x-model="taxAmount" @input="calculateTotals"
                                           class="w-32 text-right border-0 border-b border-gray-300 bg-transparent py-1 text-sm focus:ring-0 focus:border-blue-500"
                                           min="0" step="0.01">
                                </div>
                                <div class="flex justify-between items-center text-lg font-bold border-t pt-3">
                                    <span class="text-gray-900">Total:</span>
                                    <span x-text="'RM ' + total.toFixed(2)"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gap between rows -->
                    <div class="h-2 bg-gray-50"></div>

                    <!-- Notes and Terms Section -->
                    <div class="px-4 md:px-6 lg:px-8 py-6 bg-white border border-gray-200 rounded-lg mx-2 md:mx-4 lg:mx-6">
                        <div class="space-y-6">
                            <div x-show="optionalSections.show_payment_instructions">
                                <label class="block text-sm font-medium text-gray-900 mb-2">Payment Instructions</label>
                                <textarea x-model="paymentInstructions" rows="3"
                                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-2">Notes</label>
                                <textarea x-model="notes" rows="3"
                                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-900 mb-2">Terms & Conditions</label>
                                <textarea x-model="terms" rows="3"
                                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logo Selector Modal (Same as product-builder) -->
    <div x-show="showLogoSelector"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showLogoSelector = false"></div>
            <div class="relative inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Select Company Logo</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <template x-for="logo in logoBank" :key="logo.id">
                        <div @click="selectLogo(logo.id, logo.url)"
                             class="relative p-4 border-2 rounded-lg cursor-pointer hover:border-blue-500 transition-colors"
                             :class="selectedLogoId === logo.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                            <img :src="logo.url" :alt="logo.name" class="h-24 mx-auto object-contain">
                            <p class="mt-2 text-xs text-center text-gray-600" x-text="logo.name"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function quotationEditor() {
    return {
        // UI State
        showCustomerDropdown: false,
        showLogoSelector: false,

        // Customer Management
        customerSearch: '',
        customerResults: [],
        selectedCustomer: @json([
            'name' => $quotation->customer_name,
            'company_name' => $quotation->customer_company,
            'phone' => $quotation->customer_phone,
            'email' => $quotation->customer_email,
            'address' => $quotation->customer_address,
            'city' => $quotation->customer_city,
            'state' => $quotation->customer_state,
            'postal_code' => $quotation->customer_postal_code,
        ]),

        // Pricing Book Integration
        showPricingDropdown: {},
        pricingResults: {},
        searchTimeout: null,

        // Quotation Data
        quotationId: {{ $quotation->id }},
        quotationNumber: '{{ $quotation->number }}',
        quotationDate: '{{ $quotation->quotation_date?->format('Y-m-d') ?? date('Y-m-d') }}',
        quotationDateDisplay: '{{ $quotation->quotation_date?->format('d/m/Y') ?? date('d/m/Y') }}',
        validUntil: '{{ $quotation->valid_until?->format('Y-m-d') ?? '' }}',
        validUntilDisplay: '{{ $quotation->valid_until?->format('d/m/Y') ?? '' }}',
        referenceNumber: '{{ $quotation->reference_number ?? '' }}',
        customerSegmentId: {{ $quotation->customer_segment_id ?? 'null' }},

        // Optional Sections
        optionalSections: {
            show_shipping: true,
            show_payment_instructions: true,
            show_signatures: true,
            show_company_logo: true,
        },

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
        lineItems: @json($lineItemsData),

        // Financial Calculations
        subtotal: {{ $quotation->subtotal ?? 0 }},
        discountAmount: {{ $quotation->discount_amount ?? 0 }},
        taxAmount: {{ $quotation->tax_amount ?? 0 }},
        total: {{ $quotation->total ?? 0 }},

        // Content
        notes: @json($quotation->notes ?? ''),
        terms: @json($quotation->terms_conditions ?? ''),
        paymentInstructions: @json($quotation->payment_instructions ?? ''),

        // Logo Management
        logoBank: [],
        selectedLogoId: {{ auth()->user()->company->defaultLogo()?->id ?? 'null' }},
        selectedLogoUrl: '{{ auth()->user()->company->defaultLogo() ? route("logo-bank.serve", auth()->user()->company->defaultLogo()->id) : "" }}',

        init() {
            this.loadLogoBank();
            this.calculateTotals();
        },

        toggleShippingSameAsBilling() {
            if (this.shippingSameAsBilling) {
                this.shippingInfo = { ...this.selectedCustomer };
            }
        },

        updateQuotationDate() {
            // Format: DD/MM/YYYY -> YYYY-MM-DD
            const parts = this.quotationDateDisplay.split('/');
            if (parts.length === 3) {
                this.quotationDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
        },

        updateValidUntil() {
            // Format: DD/MM/YYYY -> YYYY-MM-DD
            const parts = this.validUntilDisplay.split('/');
            if (parts.length === 3) {
                this.validUntil = `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
        },

        async searchCustomers() {
            if (this.customerSearch.length < 2) {
                this.customerResults = [];
                return;
            }

            try {
                const response = await fetch(`/api/customers/search?q=${encodeURIComponent(this.customerSearch)}`, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.customerResults = data.customers || [];
                }
            } catch (error) {
                console.error('Error searching customers:', error);
            }
        },

        selectCustomer(customer) {
            this.selectedCustomer = customer;
            this.customerSearch = '';
            this.showCustomerDropdown = false;
        },

        async searchPricingItems(index) {
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            const query = this.lineItems[index].description;
            if (query.length < 2) {
                this.pricingResults[index] = [];
                return;
            }

            this.searchTimeout = setTimeout(async () => {
                try {
                    const response = await fetch(`/api/pricing-items/search?q=${encodeURIComponent(query)}`, {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.pricingResults[index] = data.items || [];
                    }
                } catch (error) {
                    console.error('Error searching pricing items:', error);
                }
            }, 300);
        },

        selectPricingItem(index, pricingItem) {
            this.lineItems[index] = {
                description: pricingItem.name,
                quantity: 1,
                unit_price: pricingItem.unit_price,
                pricing_item_id: pricingItem.id,
                item_code: pricingItem.item_code || ''
            };
            this.showPricingDropdown[index] = false;
            this.calculateTotals();
        },

        addLineItem() {
            this.lineItems.push({
                description: '',
                quantity: 1,
                unit_price: 0,
                pricing_item_id: null,
                item_code: ''
            });
        },

        removeItem(index) {
            this.lineItems.splice(index, 1);
            this.calculateTotals();
        },

        calculateTotals() {
            this.subtotal = this.lineItems.reduce((sum, item) => {
                return sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0));
            }, 0);

            this.total = this.subtotal - parseFloat(this.discountAmount || 0) + parseFloat(this.taxAmount || 0);
        },

        async updateQuotation() {
            try {
                const response = await fetch(`/quotations/${this.quotationId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        customer_name: this.selectedCustomer.name,
                        customer_company: this.selectedCustomer.company_name,
                        customer_phone: this.selectedCustomer.phone,
                        customer_email: this.selectedCustomer.email,
                        customer_address: this.selectedCustomer.address,
                        customer_city: this.selectedCustomer.city,
                        customer_state: this.selectedCustomer.state,
                        customer_postal_code: this.selectedCustomer.postal_code,
                        quotation_date: this.quotationDate,
                        valid_until: this.validUntil,
                        reference_number: this.referenceNumber,
                        customer_segment_id: this.customerSegmentId,
                        items: this.lineItems,
                        discount_amount: this.discountAmount,
                        tax_amount: this.taxAmount,
                        notes: this.notes,
                        terms_conditions: this.terms,
                        payment_instructions: this.paymentInstructions,
                        logo_id: this.selectedLogoId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = `/quotations/${this.quotationId}`;
                } else {
                    alert(data.message || 'Failed to update quotation');
                }
            } catch (error) {
                console.error('Error updating quotation:', error);
                alert('Failed to update quotation. Please try again.');
            }
        },

        async previewPDF() {
            window.open(`/quotations/${this.quotationId}/preview`, '_blank');
        },

        async loadLogoBank() {
            try {
                const response = await fetch('/logo-bank/list', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.logoBank = data.logos || [];
                }
            } catch (error) {
                console.error('Error loading logo bank:', error);
            }
        },

        selectLogo(logoId, logoUrl) {
            this.selectedLogoId = logoId;
            this.selectedLogoUrl = logoUrl;
            this.showLogoSelector = false;
        }
    };
}
</script>
@endsection
