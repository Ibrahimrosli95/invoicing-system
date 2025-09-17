<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Invoice
                @if($quotation)
                    <span class="text-sm font-normal text-gray-600">from Quotation {{ $quotation->number }}</span>
                @endif
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($quotation)
                        <!-- Quotation Summary -->
                        <div class="bg-blue-50 rounded-lg p-4 mb-6">
                            <h3 class="text-lg font-medium text-blue-900 mb-2">Creating invoice from Quotation {{ $quotation->number }}</h3>
                            <div class="text-sm text-blue-800">
                                <p>Customer: {{ $quotation->customer_name }}</p>
                                <p>Total Amount: RM {{ number_format($quotation->total_amount, 2) }}</p>
                                <p>All quotation details will be copied to this invoice.</p>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('invoices.store') }}" x-data="invoiceForm()">
                        @csrf

                        @if($quotation)
                            <input type="hidden" name="quotation_id" value="{{ $quotation->id }}">
                        @endif

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-6">
                                <!-- Customer Information -->
                                <div class="border-b border-gray-200 pb-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="md:col-span-2">
                                            <x-input-label for="customer_name" :value="__('Customer Name')" />
                                            <x-text-input id="customer_name" 
                                                          name="customer_name" 
                                                          type="text" 
                                                          class="mt-1 block w-full" 
                                                          :value="old('customer_name', $quotation->customer_name ?? '')"
                                                          required />
                                            <x-input-error class="mt-2" :messages="$errors->get('customer_name')" />
                                        </div>

                                        <div>
                                            <x-input-label for="customer_phone" :value="__('Phone Number')" />
                                            <x-text-input id="customer_phone" 
                                                          name="customer_phone" 
                                                          type="text" 
                                                          class="mt-1 block w-full" 
                                                          :value="old('customer_phone', $quotation->customer_phone ?? '')"
                                                          required />
                                            <x-input-error class="mt-2" :messages="$errors->get('customer_phone')" />
                                        </div>

                                        <div>
                                            <x-input-label for="customer_email" :value="__('Email Address')" />
                                            <x-text-input id="customer_email" 
                                                          name="customer_email" 
                                                          type="email" 
                                                          class="mt-1 block w-full" 
                                                          :value="old('customer_email', $quotation->customer_email ?? '')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('customer_email')" />
                                        </div>

                                        <div class="md:col-span-2">
                                            <x-input-label for="customer_address" :value="__('Address')" />
                                            <textarea id="customer_address" 
                                                      name="customer_address" 
                                                      rows="3"
                                                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('customer_address', $quotation->customer_address ?? '') }}</textarea>
                                            <x-input-error class="mt-2" :messages="$errors->get('customer_address')" />
                                        </div>

                                        <div>
                                            <x-input-label for="customer_city" :value="__('City')" />
                                            <x-text-input id="customer_city" 
                                                          name="customer_city" 
                                                          type="text" 
                                                          class="mt-1 block w-full" 
                                                          :value="old('customer_city', $quotation->customer_city ?? '')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('customer_city')" />
                                        </div>

                                        <div>
                                            <x-input-label for="customer_state" :value="__('State')" />
                                            <x-text-input id="customer_state" 
                                                          name="customer_state" 
                                                          type="text" 
                                                          class="mt-1 block w-full" 
                                                          :value="old('customer_state', $quotation->customer_state ?? '')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('customer_state')" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Invoice Details -->
                                <div class="border-b border-gray-200 pb-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Details</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="due_date" :value="__('Due Date')" />
                                            <x-text-input id="due_date" 
                                                          name="due_date" 
                                                          type="date" 
                                                          class="mt-1 block w-full" 
                                                          :value="old('due_date', now()->addDays(30)->format('Y-m-d'))"
                                                          required />
                                            <x-input-error class="mt-2" :messages="$errors->get('due_date')" />
                                        </div>

                                        <div>
                                            <x-input-label for="payment_terms_days" :value="__('Payment Terms (Days)')" />
                                            <x-text-input id="payment_terms_days" 
                                                          name="payment_terms_days" 
                                                          type="number" 
                                                          min="0"
                                                          max="365"
                                                          class="mt-1 block w-full" 
                                                          :value="old('payment_terms_days', '30')"
                                                          required />
                                            <x-input-error class="mt-2" :messages="$errors->get('payment_terms_days')" />
                                        </div>

                                        @if($teams->count() > 0)
                                        <div>
                                            <x-input-label for="team_id" :value="__('Team')" />
                                            <select id="team_id" 
                                                    name="team_id" 
                                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                <option value="">Select Team</option>
                                                @foreach($teams as $team)
                                                    <option value="{{ $team->id }}" 
                                                            {{ old('team_id', $quotation->team_id ?? '') == $team->id ? 'selected' : '' }}>
                                                        {{ $team->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <x-input-error class="mt-2" :messages="$errors->get('team_id')" />
                                        </div>
                                        @endif

                                        @if($assignees->count() > 0)
                                        <div>
                                            <x-input-label for="assigned_to" :value="__('Assigned To')" />
                                            <select id="assigned_to" 
                                                    name="assigned_to" 
                                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                <option value="">Select Assignee</option>
                                                @foreach($assignees as $assignee)
                                                    <option value="{{ $assignee->id }}" 
                                                            {{ old('assigned_to', $quotation->assigned_to ?? '') == $assignee->id ? 'selected' : '' }}>
                                                        {{ $assignee->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <x-input-error class="mt-2" :messages="$errors->get('assigned_to')" />
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-6">
                                <!-- Financial Settings -->
                                <div class="border-b border-gray-200 pb-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Settings</h3>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="tax_percentage" :value="__('Tax %')" />
                                            <x-text-input id="tax_percentage" 
                                                          name="tax_percentage" 
                                                          type="number" 
                                                          step="0.01"
                                                          min="0"
                                                          max="100"
                                                          class="mt-1 block w-full" 
                                                          :value="old('tax_percentage', $quotation->tax_percentage ?? '6')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('tax_percentage')" />
                                        </div>

                                        <div>
                                            <x-input-label for="discount_percentage" :value="__('Discount %')" />
                                            <x-text-input id="discount_percentage" 
                                                          name="discount_percentage" 
                                                          type="number" 
                                                          step="0.01"
                                                          min="0"
                                                          max="100"
                                                          class="mt-1 block w-full" 
                                                          :value="old('discount_percentage', $quotation->discount_percentage ?? '0')" />
                                            <x-input-error class="mt-2" :messages="$errors->get('discount_percentage')" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Information -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                                    <div class="space-y-4">
                                        <div>
                                            <x-input-label for="description" :value="__('Description')" />
                                            <textarea id="description" 
                                                      name="description" 
                                                      rows="3"
                                                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $quotation->description ?? '') }}</textarea>
                                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                                        </div>

                                        <div>
                                            <x-input-label for="terms_conditions" :value="__('Terms & Conditions')" />
                                            <textarea id="terms_conditions" 
                                                      name="terms_conditions" 
                                                      rows="3"
                                                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('terms_conditions', $quotation->terms_conditions ?? '') }}</textarea>
                                            <x-input-error class="mt-2" :messages="$errors->get('terms_conditions')" />
                                        </div>

                                        <div>
                                            <x-input-label for="notes" :value="__('Internal Notes')" />
                                            <textarea id="notes" 
                                                      name="notes" 
                                                      rows="2"
                                                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('notes', $quotation->notes ?? '') }}</textarea>
                                            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="mt-8 border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Items</h3>
                            <div class="space-y-4">
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="grid grid-cols-12 gap-4 items-start bg-gray-50 p-4 rounded-lg">
                                        <div class="col-span-4">
                                            <x-input-label :value="__('Description')" />
                                            <textarea x-model="item.description" 
                                                      :name="`items[${index}][description]`"
                                                      rows="2"
                                                      class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                                      required></textarea>
                                        </div>
                                        <div class="col-span-2">
                                            <x-input-label :value="__('Unit')" />
                                            <x-text-input x-model="item.unit" 
                                                          :name="`items[${index}][unit]`"
                                                          type="text" 
                                                          class="mt-1 block w-full text-sm" 
                                                          required />
                                        </div>
                                        <div class="col-span-2">
                                            <x-input-label :value="__('Quantity')" />
                                            <x-text-input x-model="item.quantity" 
                                                          :name="`items[${index}][quantity]`"
                                                          type="number" 
                                                          step="0.01"
                                                          min="0.01"
                                                          class="mt-1 block w-full text-sm" 
                                                          required />
                                        </div>
                                        <div class="col-span-2">
                                            <x-input-label :value="__('Unit Price')" />
                                            <x-text-input x-model="item.unit_price" 
                                                          :name="`items[${index}][unit_price]`"
                                                          type="number" 
                                                          step="0.01"
                                                          min="0"
                                                          class="mt-1 block w-full text-sm" 
                                                          required />
                                        </div>
                                        <div class="col-span-1 text-center">
                                            <x-input-label :value="__('Total')" />
                                            <div class="mt-1 py-2 text-sm font-medium" 
                                                 x-text="`RM ${((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)).toFixed(2)}`">
                                            </div>
                                        </div>
                                        <div class="col-span-1">
                                            <x-input-label :value="__('')" />
                                            <button type="button" 
                                                    @click="removeItem(index)"
                                                    class="mt-1 bg-red-500 hover:bg-red-700 text-white p-2 rounded text-sm"
                                                    x-show="items.length > 1">
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <button type="button" 
                                        @click="addItem()"
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    Add Item
                                </button>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end mt-8 space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('invoices.index') }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function invoiceForm() {
            return {
                @if($quotation && $quotation->items->count() > 0)
                items: [
                    @foreach($quotation->items as $item)
                    {
                        description: '{{ addslashes($item->description) }}',
                        unit: '{{ $item->unit }}',
                        quantity: {{ $item->quantity }},
                        unit_price: {{ $item->unit_price }},
                        item_code: '{{ $item->item_code ?? '' }}',
                        specifications: '{{ addslashes($item->specifications ?? '') }}',
                        notes: '{{ addslashes($item->notes ?? '') }}'
                    }@if(!$loop->last),@endif
                    @endforeach
                ],
                @else
                items: [
                    {
                        description: '',
                        unit: 'pcs',
                        quantity: 1,
                        unit_price: 0,
                        item_code: '',
                        specifications: '',
                        notes: ''
                    }
                ],
                @endif
                
                addItem() {
                    this.items.push({
                        description: '',
                        unit: 'pcs',
                        quantity: 1,
                        unit_price: 0,
                        item_code: '',
                        specifications: '',
                        notes: ''
                    });
                },
                
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                }
            }
        }
    </script>
</x-app-layout>