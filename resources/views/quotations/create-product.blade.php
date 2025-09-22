@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="productQuotationBuilder()" x-merge="productSearchMethods" x-merge="sidebarMethods">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Create Product Quotation</h1>
                <p class="text-sm text-gray-600">Build professional product quotations with intelligent pricing and customer management</p>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button"
                        @click="saveAsDraft()"
                        :disabled="!canSave()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                    Save as Draft
                </button>
                <button type="button"
                        @click="sendQuotation()"
                        :disabled="!canSend()"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                    Create & Send Quotation
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
                                   x-model="quotation.customer_name"
                                   @input="searchClients($event.target.value)"
                                   placeholder="Enter client name or search..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>

                            <!-- Client Suggestions -->
                            <div x-show="clientSuggestions.length > 0"
                                 class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <template x-for="client in clientSuggestions" :key="client.id">
                                    <div @click="selectClient(client)"
                                         class="px-3 py-2 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                                        <div class="font-medium text-gray-900" x-text="client.name"></div>
                                        <div class="text-sm text-gray-500" x-text="client.phone + ' • ' + client.source"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input type="tel"
                                   x-model="quotation.customer_phone"
                                   placeholder="+60 12-345 6789"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email"
                                   x-model="quotation.customer_email"
                                   placeholder="client@example.com"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer Segment</label>
                            <select x-model="quotation.customer_segment_id"
                                    @change="updateSegmentPricing()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select Segment</option>
                                @foreach($customerSegments as $segment)
                                    <option value="{{ $segment->id }}">{{ $segment->name }} ({{ $segment->discount_percentage }}% discount)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <textarea x-model="quotation.customer_address"
                                      rows="3"
                                      placeholder="Client's full address..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Requirements</label>
                            <textarea x-model="quotation.requirements"
                                      rows="3"
                                      placeholder="Client's specific requirements and details..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Products & Items -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Products & Items</h3>
                        <div class="flex items-center space-x-3">
                            <button type="button"
                                    @click="openProductSearch()"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Browse Products
                            </button>
                            <button type="button"
                                    @click="addManualItem()"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Custom Item
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div x-show="quotation.items.length === 0" class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No items added</h3>
                        <p class="mt-1 text-sm text-gray-500">Browse products or add custom items to build your quotation.</p>
                    </div>

                    <!-- Items Table -->
                    <div x-show="quotation.items.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(item, index) in quotation.items" :key="item.id">
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   x-model="item.description"
                                                   placeholder="Product description"
                                                   class="w-full border-none p-0 focus:ring-0 focus:outline-none"
                                                   @input="calculateTotals()">
                                            <div x-show="item.tier_info" class="text-xs text-green-600 mt-1" x-text="item.tier_info"></div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text"
                                                   x-model="item.item_code"
                                                   placeholder="SKU/Code"
                                                   class="w-full border-none p-0 focus:ring-0 focus:outline-none text-sm text-gray-600">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="number"
                                                   x-model.number="item.quantity"
                                                   min="1"
                                                   step="0.01"
                                                   class="w-20 border-none p-0 focus:ring-0 focus:outline-none"
                                                   @input="updateItemTierPricing(item); calculateTotals()">
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <span class="text-sm text-gray-500 mr-1">RM</span>
                                                <input type="number"
                                                       x-model.number="item.unit_price"
                                                       min="0"
                                                       step="0.01"
                                                       class="w-24 border-none p-0 focus:ring-0 focus:outline-none"
                                                       @input="calculateTotals()">
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-medium">RM <span x-text="(item.quantity * item.unit_price).toFixed(2)">0.00</span></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <button type="button"
                                                    @click="removeItem(index)"
                                                    class="text-red-600 hover:text-red-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Summary</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount (%)</label>
                            <input type="number"
                                   x-model.number="quotation.discount_percentage"
                                   min="0"
                                   max="100"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   @input="calculateTotals()">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tax (%)</label>
                            <input type="number"
                                   x-model.number="quotation.tax_percentage"
                                   min="0"
                                   max="100"
                                   step="0.01"
                                   placeholder="0.00"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   @input="calculateTotals()">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Valid Until</label>
                            <input type="date"
                                   x-model="quotation.valid_until"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Totals Display -->
                    <div class="mt-6 space-y-2 pt-4 border-t border-gray-200">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Subtotal:</span>
                            <span class="text-sm font-medium">RM <span x-text="quotation.subtotal.toFixed(2)">0.00</span></span>
                        </div>
                        <div x-show="quotation.discount_amount > 0" class="flex justify-between">
                            <span class="text-sm text-gray-600">Discount (<span x-text="quotation.discount_percentage">0</span>%):</span>
                            <span class="text-sm font-medium text-green-600">-RM <span x-text="quotation.discount_amount.toFixed(2)">0.00</span></span>
                        </div>
                        <div x-show="quotation.customer_segment_id && getSegmentDiscountAmount() > 0" class="flex justify-between">
                            <span class="text-sm text-gray-600">Segment Discount:</span>
                            <span class="text-sm font-medium text-green-600">-RM <span x-text="getSegmentDiscountAmount().toFixed(2)">0.00</span></span>
                        </div>
                        <div x-show="quotation.tax_amount > 0" class="flex justify-between">
                            <span class="text-sm text-gray-600">Tax (<span x-text="quotation.tax_percentage">0</span>%):</span>
                            <span class="text-sm font-medium">RM <span x-text="quotation.tax_amount.toFixed(2)">0.00</span></span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200">
                            <span class="text-lg font-semibold text-gray-900">Total:</span>
                            <span class="text-lg font-bold text-blue-600">RM <span x-text="quotation.total.toFixed(2)">0.00</span></span>
                        </div>
                    </div>
                </div>

                <!-- Notes & Terms -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea x-model="quotation.notes"
                                      rows="4"
                                      placeholder="Additional notes for the quotation..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Terms & Conditions</label>
                            <textarea x-model="quotation.terms_conditions"
                                      rows="4"
                                      placeholder="Quotation terms and conditions..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar (30%) -->
        <div class="w-80 bg-white border-l border-gray-200 overflow-y-auto">
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
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">

        <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showClientSearch = false"></div>

            <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Recent Clients</h3>

                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @foreach($recentClients->take(10) as $client)
                                <div class="p-3 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer"
                                     @click="selectClient({{ json_encode($client) }}); showClientSearch = false">
                                    <div class="font-medium text-gray-900">{{ $client['name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $client['phone'] }} • {{ ucfirst($client['source']) }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button"
                            @click="showClientSearch = false"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Product Quotation Builder Alpine.js Component
document.addEventListener('alpine:init', () => {
    Alpine.data('productQuotationBuilder', () => ({
        // Core quotation data
        quotation: {
            type: 'product',
            customer_name: '',
            customer_phone: '',
            customer_email: '',
            customer_address: '',
            customer_segment_id: '',
            requirements: '',
            items: [],
            subtotal: 0,
            discount_percentage: 0,
            discount_amount: 0,
            tax_percentage: 0,
            tax_amount: 0,
            total: 0,
            notes: '',
            terms_conditions: '',
            valid_until: '',
            team_id: '',
            assigned_to: ''
        },

        // UI state
        showClientSearch: false,
        clientSuggestions: [],
        searchTimeout: null,
        itemIdCounter: 1,

        // Initialize from lead if provided
        init() {
            @if($lead)
                this.populateFromLead(@json($lead));
            @endif

            // Set default valid until date to 30 days from now
            if (!this.quotation.valid_until) {
                const validDate = new Date();
                validDate.setDate(validDate.getDate() + 30);
                this.quotation.valid_until = validDate.toISOString().split('T')[0];
            }

            this.calculateTotals();
        },

        // Populate from lead data
        populateFromLead(lead) {
            this.quotation.customer_name = lead.customer_name || '';
            this.quotation.customer_phone = lead.phone || '';
            this.quotation.customer_email = lead.email || '';
            this.quotation.customer_address = lead.address || '';
            this.quotation.requirements = lead.requirements || '';
            this.quotation.team_id = lead.team_id || '';
            this.quotation.assigned_to = lead.assigned_to || '';
        },

        // Client search functionality
        searchClients(query) {
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout);
            }

            this.searchTimeout = setTimeout(() => {
                if (query.length >= 2) {
                    fetch(`/api/clients/search?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            this.clientSuggestions = data.clients || [];
                        })
                        .catch(error => {
                            console.error('Client search error:', error);
                            this.clientSuggestions = [];
                        });
                } else {
                    this.clientSuggestions = [];
                }
            }, 300);
        },

        selectClient(client) {
            this.quotation.customer_name = client.name;
            this.quotation.customer_phone = client.phone;
            this.quotation.customer_email = client.email || '';
            this.quotation.customer_address = client.address || '';
            this.clientSuggestions = [];
        },

        // Item management
        addManualItem() {
            this.quotation.items.push({
                id: this.itemIdCounter++,
                description: '',
                quantity: 1,
                unit_price: 0,
                item_code: '',
                source_type: 'manual',
                source_id: null
            });
        },

        addProductToQuotation(product) {
            // Get the correct price based on customer segment
            const price = this.getSegmentPrice(product);

            this.quotation.items.push({
                id: this.itemIdCounter++,
                description: product.name,
                quantity: 1,
                unit_price: price,
                item_code: product.item_code || '',
                source_type: 'pricing_item',
                source_id: product.id,
                tier_info: this.getTierInfo(product, 1)
            });

            this.calculateTotals();
            this.showNotification('Product added to quotation', 'success');
        },

        removeItem(index) {
            this.quotation.items.splice(index, 1);
            this.calculateTotals();
        },

        // Pricing logic
        getSegmentPrice(product) {
            if (!this.quotation.customer_segment_id || !product.segment_pricing) {
                return parseFloat(product.unit_price);
            }

            const segmentPrice = product.segment_pricing.find(sp =>
                sp.customer_segment_id == this.quotation.customer_segment_id
            );

            return segmentPrice ? parseFloat(segmentPrice.unit_price) : parseFloat(product.unit_price);
        },

        getTierInfo(product, quantity) {
            if (!product.tier_pricing || product.tier_pricing.length === 0) {
                return null;
            }

            const applicableTier = product.tier_pricing
                .filter(tier => quantity >= tier.min_quantity)
                .sort((a, b) => b.min_quantity - a.min_quantity)[0];

            if (applicableTier && applicableTier.unit_price < product.unit_price) {
                const savings = (product.unit_price - applicableTier.unit_price) * quantity;
                return `Tier pricing: Save RM ${savings.toFixed(2)}`;
            }

            return null;
        },

        updateItemTierPricing(item) {
            if (item.source_type === 'pricing_item' && item.source_id) {
                // Fetch updated tier pricing for this item
                fetch(`/api/pricing-items/${item.source_id}/tier-pricing?quantity=${item.quantity}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.tier_price && data.tier_price < item.unit_price) {
                            item.unit_price = data.tier_price;
                            item.tier_info = data.tier_info;
                        } else {
                            item.tier_info = null;
                        }
                        this.calculateTotals();
                    })
                    .catch(error => {
                        console.error('Tier pricing error:', error);
                    });
            }
        },

        getSegmentDiscountAmount() {
            if (!this.quotation.customer_segment_id) return 0;

            const segment = @json($customerSegments).find(s => s.id == this.quotation.customer_segment_id);
            if (!segment) return 0;

            return (this.quotation.subtotal * segment.discount_percentage) / 100;
        },

        // Calculate totals
        calculateTotals() {
            // Calculate subtotal
            this.quotation.subtotal = this.quotation.items.reduce((total, item) => {
                return total + (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
            }, 0);

            // Calculate additional discount
            this.quotation.discount_amount = (this.quotation.subtotal * (this.quotation.discount_percentage || 0)) / 100;

            // Calculate segment discount
            const segmentDiscount = this.getSegmentDiscountAmount();

            // Calculate tax (after all discounts)
            const taxableAmount = this.quotation.subtotal - this.quotation.discount_amount - segmentDiscount;
            this.quotation.tax_amount = (taxableAmount * (this.quotation.tax_percentage || 0)) / 100;

            // Calculate total
            this.quotation.total = this.quotation.subtotal - this.quotation.discount_amount - segmentDiscount + this.quotation.tax_amount;
        },

        updateSegmentPricing() {
            // Update pricing for all items based on customer segment
            this.quotation.items.forEach(item => {
                if (item.source_type === 'pricing_item' && item.source_id) {
                    // Re-fetch item with segment pricing
                    fetch(`/api/pricing-items/${item.source_id}/segment-pricing?segment_id=${this.quotation.customer_segment_id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.segment_price) {
                                item.unit_price = data.segment_price;
                            }
                            this.calculateTotals();
                        })
                        .catch(error => {
                            console.error('Segment pricing error:', error);
                        });
                }
            });

            this.calculateTotals();
        },

        // Validation
        canSave() {
            return this.quotation.customer_name.trim() !== '' &&
                   this.quotation.customer_phone.trim() !== '' &&
                   this.quotation.items.length > 0;
        },

        canSend() {
            return this.canSave() &&
                   this.quotation.customer_email.trim() !== '';
        },

        // Save actions
        saveAsDraft() {
            this.saveQuotation('DRAFT');
        },

        sendQuotation() {
            this.saveQuotation('SENT');
        },

        saveQuotation(status) {
            const formData = {
                ...this.quotation,
                status: status,
                @if($lead)
                lead_id: {{ $lead->id }},
                @endif
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };

            fetch('{{ route("quotations.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': formData._token
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect || '/quotations/' + data.quotation.id;
                } else {
                    alert('Error: ' + (data.message || 'Failed to save quotation'));
                }
            })
            .catch(error => {
                console.error('Save error:', error);
                alert('Failed to save quotation. Please try again.');
            });
        },

        showNotification(message, type = 'info') {
            // Simple notification - can be enhanced with a proper notification system
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    }));
});
</script>
@endsection