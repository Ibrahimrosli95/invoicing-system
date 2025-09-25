@extends('layouts.app')

@section('title', 'Create Quotation')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Quotation') }}
            @if($lead)
                <span class="text-gray-600 text-sm font-normal">from Lead: {{ $lead->name }}</span>
            @endif
        </h2>
        <a href="{{ route('quotations.index') }}"
           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Quotations
        </a>
    </div>
</div>
@endsection

@section('content')

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('quotations.store') }}" x-data="quotationForm()">
                @csrf
                
                @if($lead)
                    <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                @endif

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-8">
                        
                        @if ($errors->any())
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                <strong>Please correct the following errors:</strong>
                                <ul class="mt-2 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Quotation Type -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quotation Type</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="relative">
                                    <input type="radio" name="type" value="product" x-model="type" required
                                           class="sr-only peer">
                                    <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-blue-500 peer-checked:bg-blue-500 mr-3"></div>
                                            <div>
                                                <h4 class="font-semibold text-gray-900">Product Quotation</h4>
                                                <p class="text-sm text-gray-600">Simple item-based pricing</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                <label class="relative">
                                    <input type="radio" name="type" value="service" x-model="type" required
                                           class="sr-only peer">
                                    <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-blue-500 peer-checked:bg-blue-500 mr-3"></div>
                                            <div>
                                                <h4 class="font-semibold text-gray-900">Service Quotation</h4>
                                                <p class="text-sm text-gray-600">Section-based organization</p>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="customer_name" :value="__('Customer Name *')" />
                                    <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full" 
                                                  :value="old('customer_name', $lead ? $lead->name : '')" required autofocus />
                                    <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="customer_phone" :value="__('Phone *')" />
                                    <x-text-input id="customer_phone" name="customer_phone" type="tel" class="mt-1 block w-full" 
                                                  :value="old('customer_phone', $lead ? $lead->phone : '')" required />
                                    <x-input-error :messages="$errors->get('customer_phone')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="customer_email" :value="__('Email')" />
                                    <x-text-input id="customer_email" name="customer_email" type="email" class="mt-1 block w-full" 
                                                  :value="old('customer_email', $lead ? $lead->email : '')" />
                                    <x-input-error :messages="$errors->get('customer_email')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="customer_city" :value="__('City')" />
                                    <x-text-input id="customer_city" name="customer_city" type="text" class="mt-1 block w-full" 
                                                  :value="old('customer_city', $lead ? $lead->city : '')" />
                                    <x-input-error :messages="$errors->get('customer_city')" class="mt-2" />
                                </div>

                                <div class="md:col-span-2">
                                    <x-input-label for="customer_address" :value="__('Address')" />
                                    <textarea id="customer_address" name="customer_address" rows="3"
                                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('customer_address', $lead ? $lead->address : '') }}</textarea>
                                    <x-input-error :messages="$errors->get('customer_address')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="customer_state" :value="__('State')" />
                                    <x-text-input id="customer_state" name="customer_state" type="text" class="mt-1 block w-full" 
                                                  :value="old('customer_state', $lead ? $lead->state : '')" />
                                    <x-input-error :messages="$errors->get('customer_state')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="customer_postal_code" :value="__('Postal Code')" />
                                    <x-text-input id="customer_postal_code" name="customer_postal_code" type="text" class="mt-1 block w-full" 
                                                  :value="old('customer_postal_code', $lead ? $lead->postal_code : '')" />
                                    <x-input-error :messages="$errors->get('customer_postal_code')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Quotation Details -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quotation Details</h3>
                            <div class="space-y-6">
                                <div>
                                    <x-input-label for="title" :value="__('Title *')" />
                                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" 
                                                  :value="old('title', $lead ? $lead->requirements : '')" required 
                                                  placeholder="Brief description of the quotation" />
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="description" :value="__('Description')" />
                                    <textarea id="description" name="description" rows="4"
                                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                              placeholder="Detailed scope of work or project description">{{ old('description') }}</textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <div>
                                        <x-input-label for="customer_segment_id" :value="__('Customer Segment')" />
                                        <select id="customer_segment_id" name="customer_segment_id" x-model="selectedSegment" @change="onSegmentChange()"
                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">Select Segment</option>
                                            @foreach($customerSegments as $segment)
                                                <option value="{{ $segment->id }}" data-discount="{{ $segment->default_discount_percentage }}" data-color="{{ $segment->color }}"
                                                        {{ old('customer_segment_id') == $segment->id ? 'selected' : '' }}>
                                                    {{ $segment->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('customer_segment_id')" class="mt-2" />
                                        <div x-show="selectedSegment" class="mt-2 text-sm text-gray-600">
                                            <div class="flex items-center">
                                                <div class="w-3 h-3 rounded-full mr-2" :style="{ backgroundColor: segmentColor }"></div>
                                                <span x-text="`Default discount: ${segmentDiscount}%`"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <x-input-label for="team_id" :value="__('Team')" />
                                        <select id="team_id" name="team_id"
                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">No Team</option>
                                            @foreach($teams as $team)
                                                <option value="{{ $team->id }}" {{ old('team_id', $lead ? $lead->team_id : '') == $team->id ? 'selected' : '' }}>
                                                    {{ $team->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('team_id')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="assigned_to" :value="__('Assign To')" />
                                        <select id="assigned_to" name="assigned_to"
                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">Unassigned</option>
                                            @foreach($assignees as $assignee)
                                                <option value="{{ $assignee->id }}" {{ old('assigned_to', $lead ? $lead->assigned_to : '') == $assignee->id ? 'selected' : '' }}>
                                                    {{ $assignee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('assigned_to')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="valid_until" :value="__('Valid Until')" />
                                        <x-text-input id="valid_until" name="valid_until" type="date" class="mt-1 block w-full" 
                                                      :value="old('valid_until', now()->addDays(30)->toDateString())" />
                                        <x-input-error :messages="$errors->get('valid_until')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Items</h3>
                                <button type="button" @click="addItem()" 
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    + Add Item
                                </button>
                            </div>

                            <div class="space-y-4">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-4">
                                            <h4 class="font-medium text-gray-900" x-text="`Item ${index + 1}`"></h4>
                                            <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                                    class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                                            <div class="lg:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700">Description *</label>
                                                <input type="text" x-model="item.description" required
                                                       :name="`items[${index}][description]`"
                                                       class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                       placeholder="Item description">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Unit *</label>
                                                <select x-model="item.unit" :name="`items[${index}][unit]`" required
                                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                    <option value="Nos">Nos</option>
                                                    <option value="Unit">Unit</option>
                                                    <option value="M²">M²</option>
                                                    <option value="M">M</option>
                                                    <option value="Kg">Kg</option>
                                                    <option value="Litre">Litre</option>
                                                    <option value="Hour">Hour</option>
                                                    <option value="Day">Day</option>
                                                    <option value="Set">Set</option>
                                                    <option value="Lot">Lot</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Quantity *</label>
                                                <input type="number" x-model.number="item.quantity" @input="calculateItemTotal(index)"
                                                       :name="`items[${index}][quantity]`" required min="1" step="1"
                                                       class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            </div>
                                            <div class="lg:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700">Unit Price (RM) *</label>
                                                <input type="number" x-model.number="item.unit_price" @input="calculateItemTotal(index)"
                                                       :name="`items[${index}][unit_price]`" required min="0" step="0.01"
                                                       class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Total</label>
                                                <div class="mt-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-md text-sm font-medium"
                                                     x-text="'RM ' + (item.quantity * item.unit_price || 0).toFixed(2)"></div>
                                            </div>
                                        </div>

                                        <!-- Pricing Tier Information -->
                                        <div x-show="item.pricing_method === 'tier'" class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md">
                                            <div class="flex items-center justify-between text-sm">
                                                <div class="flex items-center">
                                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                                    <span class="text-green-800 font-medium">Tier Pricing Applied</span>
                                                </div>
                                                <div class="text-green-700 font-medium" x-show="item.savings > 0">
                                                    Savings: RM <span x-text="item.savings.toFixed(2)"></span>
                                                </div>
                                            </div>
                                            <div class="text-xs text-green-600 mt-1" x-show="item.tier_info">
                                                <span x-text="item.tier_info"></span>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Item Code</label>
                                                <input type="text" x-model="item.item_code"
                                                       :name="`items[${index}][item_code]`"
                                                       class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                       placeholder="SKU or item code">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700">Specifications</label>
                                                <textarea x-model="item.specifications" :name="`items[${index}][specifications]`" rows="2"
                                                          class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                          placeholder="Technical specifications"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Financial Settings -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Settings</h3>
                            <div class="space-y-6">
                                <!-- Discount Settings -->
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 mb-3">Discount</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="discount_percentage" :value="__('Discount (%)')" />
                                            <x-text-input id="discount_percentage" name="discount_percentage" type="number"
                                                          class="mt-1 block w-full" :value="old('discount_percentage', 0)"
                                                          min="0" max="100" step="0.01"
                                                          x-data="{ value: {{ old('discount_percentage', 0) }} }"
                                                          x-model="value" />
                                            <x-input-error :messages="$errors->get('discount_percentage')" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-input-label for="discount_amount" :value="__('Discount Amount (RM)')" />
                                            <x-text-input id="discount_amount" name="discount_amount" type="number"
                                                          class="mt-1 block w-full" :value="old('discount_amount', 0)"
                                                          min="0" step="0.01" />
                                            <x-input-error :messages="$errors->get('discount_amount')" class="mt-2" />
                                            <p class="mt-1 text-xs text-gray-500">Either percentage or fixed amount can be used</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tax Settings -->
                                <div>
                                    <h4 class="text-md font-medium text-gray-900 mb-3">Tax</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="tax_percentage" :value="__('Tax (%)')" />
                                            <x-text-input id="tax_percentage" name="tax_percentage" type="number"
                                                          class="mt-1 block w-full" :value="old('tax_percentage', 0)"
                                                          min="0" max="100" step="0.01" />
                                            <x-input-error :messages="$errors->get('tax_percentage')" class="mt-2" />
                                            <p class="mt-1 text-xs text-gray-500">Default: 0% (add if applicable)</p>
                                        </div>
                                        <div>
                                            <!-- Placeholder for tax amount if needed in future -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terms & Notes -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Terms & Notes</h3>
                            <div class="space-y-6">
                                <div>
                                    <x-input-label for="terms_conditions" :value="__('Terms & Conditions')" />
                                    <textarea id="terms_conditions" name="terms_conditions" rows="4"
                                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                              placeholder="Payment terms, delivery conditions, etc.">{{ old('terms_conditions', '1. Payment terms: 30 days net
2. Validity: 30 days from quotation date
3. Delivery: 2-4 weeks upon confirmation
4. All prices are in Malaysian Ringgit (RM)
5. Terms & conditions apply') }}</textarea>
                                    <x-input-error :messages="$errors->get('terms_conditions')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="notes" :value="__('Notes')" />
                                    <textarea id="notes" name="notes" rows="3"
                                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                              placeholder="Additional notes or clarifications">{{ old('notes') }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                            <a href="{{ route('quotations.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Create Quotation') }}
                            </x-primary-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function quotationForm() {
            return {
                type: 'service',
                selectedSegment: '{{ old('customer_segment_id') }}',
                segmentColor: '',
                segmentDiscount: 0,
                items: [
                    {
                        description: '',
                        unit: 'Nos',
                        quantity: 1,
                        unit_price: 0,
                        item_code: '',
                        specifications: ''
                    }
                ],

                init() {
                    // Initialize segment data if one is selected
                    if (this.selectedSegment) {
                        this.onSegmentChange();
                    }
                },

                onSegmentChange() {
                    if (this.selectedSegment) {
                        const segmentSelect = document.getElementById('customer_segment_id');
                        const selectedOption = segmentSelect.options[segmentSelect.selectedIndex];
                        
                        this.segmentDiscount = selectedOption.getAttribute('data-discount') || 0;
                        this.segmentColor = selectedOption.getAttribute('data-color') || '#6B7280';
                        
                        // Update discount percentage field
                        const discountField = document.getElementById('discount_percentage');
                        if (discountField && !discountField.value) {
                            discountField.value = this.segmentDiscount;
                        }
                        
                        // Recalculate pricing for existing items with pricing book items
                        this.updateSegmentPricing();
                    } else {
                        this.segmentColor = '';
                        this.segmentDiscount = 0;
                    }
                },

                async updateSegmentPricing() {
                    // Update pricing for items that have pricing_item_id (from pricing book)
                    for (let index = 0; index < this.items.length; index++) {
                        const item = this.items[index];
                        if (item.pricing_item_id && this.selectedSegment) {
                            await this.fetchSegmentPricing(item.pricing_item_id, index, item.quantity);
                        }
                    }
                },

                async fetchSegmentPricing(itemId, itemIndex, quantity) {
                    if (!this.selectedSegment || !itemId) return;
                    
                    try {
                        const response = await fetch('{{ route('quotations.get-segment-pricing') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                item_id: itemId,
                                segment_id: this.selectedSegment,
                                quantity: quantity
                            })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            if (data.success && data.pricing) {
                                // Update the item with segment pricing
                                this.items[itemIndex].unit_price = data.pricing.unit_price;
                                this.items[itemIndex].pricing_method = data.pricing.pricing_method;
                                this.items[itemIndex].tier_info = data.pricing.tier_range || null;
                                this.items[itemIndex].savings = data.pricing.savings || 0;
                                
                                // Trigger reactivity
                                this.items[itemIndex] = { ...this.items[itemIndex] };
                            }
                        }
                    } catch (error) {
                        console.log('Failed to fetch segment pricing:', error);
                    }
                },

                addItem() {
                    this.items.push({
                        description: '',
                        unit: 'Nos',
                        quantity: 1,
                        unit_price: 0,
                        item_code: '',
                        specifications: '',
                        pricing_item_id: null,
                        pricing_method: null,
                        tier_info: null,
                        savings: 0
                    });
                },

                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },

                async calculateItemTotal(index) {
                    const item = this.items[index];
                    
                    // If item has pricing_item_id and we have a segment selected, fetch segment pricing
                    if (item.pricing_item_id && this.selectedSegment && item.quantity > 0) {
                        await this.fetchSegmentPricing(item.pricing_item_id, index, item.quantity);
                    }
                    
                    // Trigger reactivity by updating the item object
                    this.items[index] = { ...item };
                }
            }
        }
    </script>
@endsection