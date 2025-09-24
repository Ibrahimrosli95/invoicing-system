@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="productInvoiceBuilder()" x-merge="productSearchMethods" x-merge="sidebarMethods">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Create Product Invoice</h1>
                <p class="text-sm text-gray-600">Build professional product invoices with intelligent pricing and customer management</p>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button"
                        @click="saveAsDraft()"
                        :disabled="!canSave()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                    Save as Draft
                </button>
                <button type="button"
                        @click="sendInvoice()"
                        :disabled="!canSend()"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                    Create & Send Invoice
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex h-screen">
        <!-- Canvas Area (70%) -->
        <div class="flex-1 overflow-y-auto p-6" style="width: 70%;">
            <div class="max-w-4xl mx-auto">
                <!-- Client Information Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Client Information</h3>
                        <button type="button"
                                @click="showClientSearch = true"
                                class="text-sm text-blue-600 hover:text-blue-800">
                            Browse Clients
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Client Name *</label>
                            <input type="text"
                                   x-model="invoice.customer_name"
                                   @input="searchClients($event.target.value)"
                                   placeholder="Enter client name or search existing clients"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            <!-- Client suggestions dropdown -->
                            <div x-show="showClientSuggestions && clientSuggestions.length > 0"
                                 x-transition
                                 class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                <template x-for="client in clientSuggestions" :key="client.id">
                                    <div @click="selectClient(client)"
                                         class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                        <div class="font-medium text-gray-900" x-text="client.name"></div>
                                        <div class="text-sm text-gray-500" x-text="client.phone + (client.email ? ' • ' + client.email : '')"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer Segment</label>
                            <select x-model="invoice.customer_segment_id"
                                    @change="updatePricingForSegment()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select customer segment</option>
                                @foreach($customerSegments as $segment)
                                    <option value="{{ $segment->id }}">{{ $segment->name }} ({{ $segment->discount_percentage }}% discount)</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone *</label>
                            <input type="tel"
                                   x-model="invoice.customer_phone"
                                   placeholder="+60123456789"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email"
                                   x-model="invoice.customer_email"
                                   placeholder="client@example.com"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Billing Address</label>
                        <textarea x-model="invoice.customer_address"
                                  rows="3"
                                  placeholder="Enter full billing address"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>

                <!-- Invoice Details Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Details</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Number</label>
                            <input type="text"
                                   x-model="invoice.number"
                                   readonly
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-gray-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Issue Date *</label>
                            <input type="date"
                                   x-model="invoice.issue_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Due Date *</label>
                            <input type="date"
                                   x-model="invoice.due_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Convert from Quotation -->
                    @if($quotation)
                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm text-blue-800">Converting from Quotation {{ $quotation->number }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Product Items Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Invoice Items</h3>
                        <button type="button"
                                @click="showProductSearch = true"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Add Products
                        </button>
                    </div>

                    <!-- Items List -->
                    <div class="space-y-4">
                        <template x-for="(item, index) in invoice.items" :key="index">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="grid grid-cols-12 gap-4 items-start">
                                    <!-- Product Info -->
                                    <div class="col-span-5">
                                        <div class="font-medium text-gray-900" x-text="item.description"></div>
                                        <div class="text-sm text-gray-500" x-text="item.sku ? 'SKU: ' + item.sku : ''"></div>
                                    </div>

                                    <!-- Quantity -->
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                                        <input type="number"
                                               x-model.number="item.quantity"
                                               @input="calculateItemTotal(index)"
                                               min="1"
                                               step="1"
                                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    </div>

                                    <!-- Unit Price -->
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Unit Price (RM)</label>
                                        <input type="number"
                                               x-model.number="item.unit_price"
                                               @input="calculateItemTotal(index)"
                                               min="0"
                                               step="0.01"
                                               class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    </div>

                                    <!-- Total -->
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Total</label>
                                        <div class="px-2 py-1 text-sm bg-gray-50 border border-gray-300 rounded font-medium"
                                             x-text="'RM ' + (item.quantity * item.unit_price).toFixed(2)"></div>
                                    </div>

                                    <!-- Remove Button -->
                                    <div class="col-span-1 flex justify-end">
                                        <button type="button"
                                                @click="removeItem(index)"
                                                class="text-red-600 hover:text-red-800 p-1">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <div x-show="invoice.items.length === 0"
                             class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-200 rounded-lg">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p>No items added yet</p>
                            <p class="text-sm">Click "Add Products" to get started</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Summary</h3>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Discount (%)</label>
                                <input type="number"
                                       x-model.number="invoice.discount_percentage"
                                       @input="calculateTotals()"
                                       min="0"
                                       max="100"
                                       step="0.1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tax Rate (%)</label>
                                <input type="number"
                                       x-model.number="invoice.tax_percentage"
                                       @input="calculateTotals()"
                                       min="0"
                                       max="100"
                                       step="0.1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Totals Display -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-600">Subtotal:</span>
                                <span class="font-medium" x-text="'RM ' + invoice.subtotal.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between items-center mb-2" x-show="invoice.discount_amount > 0">
                                <span class="text-sm text-gray-600">Discount:</span>
                                <span class="text-red-600" x-text="'-RM ' + invoice.discount_amount.toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between items-center mb-2" x-show="invoice.tax_amount > 0">
                                <span class="text-sm text-gray-600">Tax:</span>
                                <span class="font-medium" x-text="'RM ' + invoice.tax_amount.toFixed(2)"></span>
                            </div>
                            <div class="border-t border-gray-200 pt-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-medium text-gray-900">Total:</span>
                                    <span class="text-xl font-bold text-blue-600" x-text="'RM ' + invoice.total.toFixed(2)"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Notes</h3>
                    <textarea x-model="invoice.notes"
                              rows="4"
                              placeholder="Add any additional notes or special instructions for this invoice..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
            </div>
        </div>

        <!-- Sidebar (30%) -->
        <div class="w-96 bg-white border-l border-gray-200 flex flex-col">
            @include('invoice-builder.sidebar', ['type' => 'product'])
        </div>
    </div>

    <!-- Product Search Modal -->
    @include('invoice-builder.product-search-modal')

    <!-- Client Search Modal -->
    <div x-show="showClientSearch"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
            <div class="fixed inset-0 transition-opacity" @click="showClientSearch = false">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-6"
                 @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Browse Clients</h3>
                    <button @click="showClientSearch = false"
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mb-4">
                    <input type="text"
                           x-model="clientSearchQuery"
                           @input="performClientSearch()"
                           placeholder="Search clients by name, phone, or email"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="max-h-96 overflow-y-auto">
                    <template x-for="client in searchedClients" :key="client.id">
                        <div @click="selectClientFromModal(client)"
                             class="p-4 border border-gray-200 rounded-lg mb-2 hover:bg-gray-50 cursor-pointer">
                            <div class="font-medium text-gray-900" x-text="client.name"></div>
                            <div class="text-sm text-gray-500" x-text="client.phone + (client.email ? ' • ' + client.email : '')"></div>
                            <div class="text-xs text-gray-400" x-text="client.address"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function productInvoiceBuilder() {
    return {
        invoice: {
            number: {!! json_encode($nextNumber ?? '') !!},
            customer_name: {!! json_encode($quotation->customer_name ?? '') !!},
            customer_phone: {!! json_encode($quotation->customer_phone ?? '') !!},
            customer_email: {!! json_encode($quotation->customer_email ?? '') !!},
            customer_address: {!! json_encode($quotation->customer_address ?? '') !!},
            customer_segment_id: {!! json_encode($quotation->customer_segment_id ?? '') !!},
            issue_date: {!! json_encode(date('Y-m-d')) !!},
            due_date: {!! json_encode(date('Y-m-d', strtotime('+30 days'))) !!},
            items: {!! json_encode($quotation ? $quotation->items->map(function($item) {
                return [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'sku' => $item->sku ?? ''
                ];
            }) : []) !!},
            subtotal: 0,
            discount_percentage: {{ $quotation->discount_percentage ?? 0 }},
            discount_amount: 0,
            tax_percentage: {{ $quotation->tax_percentage ?? 6 }},
            tax_amount: 0,
            total: 0,
            notes: {!! json_encode($quotation->notes ?? '') !!},
            type: 'product'
        },

        showClientSearch: false,
        showClientSuggestions: false,
        clientSuggestions: [],
        clientSearchQuery: '',
        searchedClients: [],

        showProductSearch: false,
        selectedTemplate: '',

        // Quick search functionality
        quickSearch: {
            query: '',
            results: []
        },

        init() {
            this.calculateTotals();
        },

        // Quick search functionality for sidebar
        performQuickSearch() {
            if (this.quickSearch.query.length < 2) {
                this.quickSearch.results = [];
                return;
            }

            fetch(`/api/pricing-items/search?query=${encodeURIComponent(this.quickSearch.query)}&limit=5`)
                .then(response => response.json())
                .then(data => {
                    this.quickSearch.results = data.products || data.data || [];
                })
                .catch(error => {
                    console.error('Quick search error:', error);
                    this.quickSearch.results = [];
                });
        },

        addProductFromSearch(product) {
            this.addProduct(product);
            this.quickSearch.query = '';
            this.quickSearch.results = [];
        },

        // Method to get product pricing with segment consideration
        getProductPrice(product) {
            if (this.invoice.customer_segment_id && product.segment_pricing) {
                return parseFloat(product.segment_pricing.unit_price || product.unit_price);
            }
            return parseFloat(product.unit_price || product.base_price || product.selling_price || 0);
        },

        // Clear all items functionality
        clearAllItems() {
            if (confirm('Are you sure you want to clear all items?')) {
                this.invoice.items = [];
                this.calculateTotals();
            }
        },

        canSave() {
            return this.invoice.customer_name &&
                   this.invoice.customer_phone &&
                   this.invoice.items.length > 0;
        },

        canSend() {
            return this.canSave() &&
                   this.invoice.customer_email;
        },

        searchClients(query) {
            if (query && query.length >= 2) {
                fetch(`/api/invoices/search-clients?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.clientSuggestions = data.data;
                            this.showClientSuggestions = true;
                        }
                    })
                    .catch(error => {
                        console.error('Error searching clients:', error);
                    });
            } else {
                this.showClientSuggestions = false;
            }
        },

        selectClient(client) {
            this.invoice.customer_name = client.name;
            this.invoice.customer_phone = client.phone;
            this.invoice.customer_email = client.email || '';
            this.invoice.customer_address = client.address || '';
            this.showClientSuggestions = false;
        },

        performClientSearch() {
            if (this.clientSearchQuery.length >= 2) {
                fetch(`/api/invoices/search-clients?query=${encodeURIComponent(this.clientSearchQuery)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.searchedClients = data.data;
                        }
                    })
                    .catch(error => {
                        console.error('Error searching clients:', error);
                    });
            }
        },

        selectClientFromModal(client) {
            this.selectClient(client);
            this.showClientSearch = false;
        },

        updatePricingForSegment() {
            // Recalculate all pricing with segment-specific rates
            this.calculateTotalsWithApi();
        },

        calculateItemTotal(index) {
            const item = this.invoice.items[index];
            item.total = item.quantity * item.unit_price;
            this.calculateTotalsWithApi();
        },

        calculateTotals() {
            // Local calculation for immediate feedback
            this.invoice.subtotal = this.invoice.items.reduce((sum, item) => {
                return sum + (item.quantity * item.unit_price);
            }, 0);

            this.invoice.discount_amount = (this.invoice.subtotal * this.invoice.discount_percentage) / 100;

            const afterDiscount = this.invoice.subtotal - this.invoice.discount_amount;
            this.invoice.tax_amount = (afterDiscount * this.invoice.tax_percentage) / 100;

            this.invoice.total = afterDiscount + this.invoice.tax_amount;
        },

        calculateTotalsWithApi() {
            // Enhanced calculation using the API for segment-specific pricing
            if (this.invoice.items.length === 0) {
                this.calculateTotals();
                return;
            }

            const payload = {
                items: this.invoice.items.map(item => ({
                    pricing_item_id: item.pricing_item_id || null,
                    quantity: item.quantity,
                    unit_price: item.unit_price
                })),
                customer_segment_id: this.invoice.customer_segment_id || null,
                discount_percentage: this.invoice.discount_percentage || 0,
                tax_percentage: this.invoice.tax_percentage || 0,
                _token: '{{ csrf_token() }}'
            };

            fetch('/api/invoices/calculate-pricing', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.invoice.subtotal = data.data.subtotal;
                    this.invoice.discount_amount = data.data.discount_amount;
                    this.invoice.tax_amount = data.data.tax_amount;
                    this.invoice.total = data.data.grand_total;

                    // Update individual item calculations if provided
                    if (data.data.items) {
                        data.data.items.forEach((apiItem, index) => {
                            if (this.invoice.items[index]) {
                                this.invoice.items[index].unit_price = apiItem.unit_price;
                                this.invoice.items[index].total = apiItem.line_total;
                            }
                        });
                    }
                } else {
                    // Fallback to local calculation
                    this.calculateTotals();
                }
            })
            .catch(error => {
                console.error('Error calculating pricing:', error);
                // Fallback to local calculation
                this.calculateTotals();
            });
        },

        removeItem(index) {
            this.invoice.items.splice(index, 1);
            this.calculateTotals();
        },

        addProduct(product) {
            const existingIndex = this.invoice.items.findIndex(item =>
                (item.item_code && item.item_code === product.item_code) ||
                (item.description === product.name && item.unit_price === product.unit_price)
            );

            if (existingIndex >= 0) {
                this.invoice.items[existingIndex].quantity += 1;
                this.calculateItemTotal(existingIndex);
            } else {
                this.invoice.items.push({
                    description: product.name,
                    item_code: product.item_code || '',
                    unit: product.unit || 'pcs',
                    quantity: 1,
                    unit_price: product.unit_price || product.selling_price,
                    pricing_item_id: product.id,
                    specifications: product.specifications || '',
                    source_type: 'pricing_item',
                    source_id: product.id
                });
                this.calculateTotalsWithApi();
            }
        },

        addManualItem() {
            this.invoice.items.push({
                description: '',
                item_code: '',
                unit: 'pcs',
                quantity: 1,
                unit_price: 0,
                specifications: '',
                source_type: 'manual',
                source_id: null
            });
        },

        loadServiceTemplate() {
            if (!this.selectedTemplate) return;

            fetch(`/api/invoices/load-service-template?template_id=${this.selectedTemplate}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data.template) {
                        const template = data.data.template;

                        // Add all template items to invoice
                        template.sections.forEach(section => {
                            section.items.forEach(item => {
                                this.invoice.items.push({
                                    description: item.description,
                                    item_code: item.item_code || '',
                                    unit: item.unit || 'pcs',
                                    quantity: item.quantity || 1,
                                    unit_price: item.unit_price,
                                    specifications: '',
                                    source_type: 'service_template_item',
                                    source_id: item.id
                                });
                            });
                        });

                        this.calculateTotalsWithApi();
                        this.selectedTemplate = '';

                        // Show success message
                        this.showNotification(`Added ${template.name} template items to invoice`, 'success');
                    }
                })
                .catch(error => {
                    console.error('Error loading service template:', error);
                    this.showNotification('Error loading service template', 'error');
                });
        },

        showNotification(message, type = 'success') {
            // Create a simple toast notification
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-4 py-2 rounded-md shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        },

        saveAsDraft() {
            this.submitInvoice('draft');
        },

        sendInvoice() {
            this.submitInvoice('send');
        },

        submitInvoice(action) {
            const url = action === 'send' ? '/invoices' : '/invoices';
            const payload = {
                ...this.invoice,
                action: action,
                _token: '{{ csrf_token() }}'
            };

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `/invoices/${data.invoice.id}`;
                } else {
                    alert('Error saving invoice: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving invoice. Please try again.');
            });
        }
    }
}
</script>
@endsection