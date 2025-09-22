@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="serviceQuotationBuilder()" x-merge="sidebarMethods">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Create Service Quotation</h1>
                <p class="text-sm text-gray-600">Build professional service quotations with detailed sections and comprehensive project scope</p>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Project Requirements</label>
                            <textarea x-model="quotation.requirements"
                                      rows="3"
                                      placeholder="Detailed project requirements and client specifications..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Service Sections -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Service Sections</h3>
                        <div class="flex items-center space-x-3">
                            <button type="button"
                                    @click="loadServiceTemplate()"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-600 bg-green-50 border border-green-200 rounded-md hover:bg-green-100">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Load Template
                            </button>
                            <button type="button"
                                    @click="addSection()"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Section
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div x-show="quotation.sections.length === 0" class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No sections added</h3>
                        <p class="mt-1 text-sm text-gray-500">Add service sections to build your quotation or load from a template.</p>
                    </div>

                    <div class="space-y-6">
                        <template x-for="(section, sectionIndex) in quotation.sections" :key="section.id">
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <!-- Section Header -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex-1">
                                        <input type="text"
                                               x-model="section.name"
                                               placeholder="Section name (e.g., Waterproofing Works)"
                                               class="text-lg font-medium border-none p-0 focus:ring-0 focus:outline-none w-full bg-transparent"
                                               style="background: transparent;">
                                        <textarea x-model="section.description"
                                                  rows="2"
                                                  placeholder="Section description and scope of work..."
                                                  class="mt-1 w-full text-sm text-gray-600 border-none p-0 focus:ring-0 focus:outline-none resize-none bg-transparent"
                                                  style="background: transparent;"></textarea>
                                    </div>
                                    <div class="flex items-center space-x-2 ml-4">
                                        <button type="button"
                                                @click="addItemToSection(sectionIndex)"
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-md">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
                                        <button type="button"
                                                @click="removeSection(sectionIndex)"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-md">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Section Items -->
                                <div x-show="section.items.length === 0" class="text-center py-6 border border-dashed border-gray-300 rounded bg-white">
                                    <p class="text-sm text-gray-500">No items in this section. Add service items to complete this section.</p>
                                </div>

                                <div class="space-y-3">
                                    <template x-for="(item, itemIndex) in section.items" :key="item.id">
                                        <div class="bg-white rounded p-3 grid grid-cols-12 gap-3 items-start">
                                            <div class="col-span-5">
                                                <input type="text"
                                                       x-model="item.description"
                                                       placeholder="Service item description"
                                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500"
                                                       @input="calculateTotals()">
                                            </div>
                                            <div class="col-span-2">
                                                <input type="number"
                                                       x-model.number="item.quantity"
                                                       min="1"
                                                       step="0.01"
                                                       placeholder="Qty"
                                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500"
                                                       @input="calculateTotals()">
                                            </div>
                                            <div class="col-span-2">
                                                <input type="number"
                                                       x-model.number="item.unit_price"
                                                       min="0"
                                                       step="0.01"
                                                       placeholder="0.00"
                                                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500"
                                                       @input="calculateTotals()">
                                            </div>
                                            <div class="col-span-2">
                                                <div class="px-3 py-2 text-sm text-right font-medium">
                                                    RM <span x-text="(item.quantity * item.unit_price).toFixed(2)">0.00</span>
                                                </div>
                                            </div>
                                            <div class="col-span-1">
                                                <button type="button"
                                                        @click="removeItemFromSection(sectionIndex, itemIndex)"
                                                        class="p-2 text-red-600 hover:bg-red-50 rounded">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Section Subtotal -->
                                <div x-show="section.items.length > 0" class="mt-4 pt-3 border-t border-gray-300 bg-white rounded p-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-700">Section Subtotal:</span>
                                        <span class="text-sm font-semibold text-blue-600">RM <span x-text="getSectionSubtotal(section).toFixed(2)">0.00</span></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Project Timeline & Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Project Information</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Project Duration</label>
                            <input type="text"
                                   x-model="quotation.project_duration"
                                   placeholder="e.g., 2-3 weeks"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Warranty Period</label>
                            <input type="text"
                                   x-model="quotation.warranty_period"
                                   placeholder="e.g., 12 months"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Valid Until</label>
                            <input type="date"
                                   x-model="quotation.valid_until"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Summary</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                      placeholder="Service terms and conditions..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar (30%) -->
        <div class="w-80 bg-white border-l border-gray-200 overflow-y-auto">
            @include('invoice-builder.sidebar', ['type' => 'service'])
        </div>
    </div>

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
// Service Quotation Builder Alpine.js Component
document.addEventListener('alpine:init', () => {
    Alpine.data('serviceQuotationBuilder', () => ({
        // Core quotation data
        quotation: {
            type: 'service',
            customer_name: '',
            customer_phone: '',
            customer_email: '',
            customer_address: '',
            customer_segment_id: '',
            requirements: '',
            sections: [],
            project_duration: '',
            warranty_period: '',
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
        sectionIdCounter: 1,
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

        // Section management
        addSection() {
            this.quotation.sections.push({
                id: this.sectionIdCounter++,
                name: '',
                description: '',
                items: []
            });
        },

        removeSection(index) {
            if (confirm('Are you sure you want to remove this section and all its items?')) {
                this.quotation.sections.splice(index, 1);
                this.calculateTotals();
            }
        },

        addItemToSection(sectionIndex) {
            this.quotation.sections[sectionIndex].items.push({
                id: this.itemIdCounter++,
                description: '',
                quantity: 1,
                unit_price: 0
            });
        },

        removeItemFromSection(sectionIndex, itemIndex) {
            this.quotation.sections[sectionIndex].items.splice(itemIndex, 1);
            this.calculateTotals();
        },

        getSectionSubtotal(section) {
            return section.items.reduce((total, item) => {
                return total + (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0);
            }, 0);
        },

        // Service template functionality
        loadServiceTemplate() {
            // Show template selection modal or dropdown
            alert('Service template loading will be implemented with template selector.');
        },

        applyServiceTemplate(templateId) {
            // Load service template and add sections/items
            fetch(`/api/service-templates/${templateId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.template && data.template.sections) {
                        // Clear existing sections if user confirms
                        if (this.quotation.sections.length > 0) {
                            if (!confirm('This will replace existing sections. Continue?')) {
                                return;
                            }
                            this.quotation.sections = [];
                        }

                        // Add template sections
                        data.template.sections.forEach(section => {
                            const newSection = {
                                id: this.sectionIdCounter++,
                                name: section.name || '',
                                description: section.description || '',
                                items: []
                            };

                            section.items.forEach(item => {
                                newSection.items.push({
                                    id: this.itemIdCounter++,
                                    description: item.description || '',
                                    quantity: item.default_quantity || 1,
                                    unit_price: item.unit_price || 0,
                                    source_type: 'service_template_item',
                                    source_id: item.id
                                });
                            });

                            this.quotation.sections.push(newSection);
                        });

                        this.calculateTotals();
                        this.showNotification('Service template applied successfully', 'success');
                    }
                })
                .catch(error => {
                    console.error('Template load error:', error);
                    this.showNotification('Failed to load service template', 'error');
                });
        },

        // Pricing calculations
        getSegmentDiscountAmount() {
            if (!this.quotation.customer_segment_id) return 0;

            const segment = @json($customerSegments).find(s => s.id == this.quotation.customer_segment_id);
            if (!segment) return 0;

            return (this.quotation.subtotal * segment.discount_percentage) / 100;
        },

        // Calculate totals
        calculateTotals() {
            // Calculate subtotal from all sections
            this.quotation.subtotal = this.quotation.sections.reduce((total, section) => {
                return total + this.getSectionSubtotal(section);
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
            this.calculateTotals();
        },

        // Validation
        canSave() {
            return this.quotation.customer_name.trim() !== '' &&
                   this.quotation.customer_phone.trim() !== '' &&
                   this.quotation.sections.length > 0 &&
                   this.quotation.sections.some(section => section.items.length > 0);
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