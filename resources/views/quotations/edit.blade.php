@extends('layouts.app')

@section('title', 'Edit Quotation: ' . $quotation->number)

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Quotation') }}: {{ $quotation->number }}
            </h2>
            <p class="text-gray-600 mt-1">
                Status: <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $quotation->getStatusBadgeColor() }}">
                    {{ $quotation->status }}
                </span>
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('quotations.show', $quotation) }}"
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                View Quotation
            </a>
            @can('delete', $quotation)
                @if($quotation->canBeEdited())
                        <form method="POST" action="{{ route('quotations.destroy', $quotation) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    onclick="return confirm('Are you sure you want to delete this quotation?')"
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Delete
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>
@endsection

@section('content')

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('quotations.update', $quotation) }}">
                @csrf
                @method('PATCH')

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
                                    <input type="radio" name="type" value="product" {{ old('type', $quotation->type) == 'product' ? 'checked' : '' }} required
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
                                    <input type="radio" name="type" value="service" {{ old('type', $quotation->type) == 'service' ? 'checked' : '' }} required
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
                            <p class="mt-2 text-sm text-gray-500">Note: Changing the quotation type will not affect existing items.</p>
                        </div>

                        <!-- Customer Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="customer_name" :value="__('Customer Name *')" />
                                    <x-text-input id="customer_name" name="customer_name" type="text" class="mt-1 block w-full" 
                                                  :value="old('customer_name', $quotation->customer_name)" required autofocus />
                                    <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="customer_phone" :value="__('Phone *')" />
                                    <x-text-input id="customer_phone" name="customer_phone" type="tel" class="mt-1 block w-full" 
                                                  :value="old('customer_phone', $quotation->customer_phone)" required />
                                    <x-input-error :messages="$errors->get('customer_phone')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="customer_email" :value="__('Email')" />
                                    <x-text-input id="customer_email" name="customer_email" type="email" class="mt-1 block w-full" 
                                                  :value="old('customer_email', $quotation->customer_email)" />
                                    <x-input-error :messages="$errors->get('customer_email')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="customer_city" :value="__('City')" />
                                    <x-text-input id="customer_city" name="customer_city" type="text" class="mt-1 block w-full" 
                                                  :value="old('customer_city', $quotation->customer_city)" />
                                    <x-input-error :messages="$errors->get('customer_city')" class="mt-2" />
                                </div>

                                <div class="md:col-span-2">
                                    <x-input-label for="customer_address" :value="__('Address')" />
                                    <textarea id="customer_address" name="customer_address" rows="3"
                                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('customer_address', $quotation->customer_address) }}</textarea>
                                    <x-input-error :messages="$errors->get('customer_address')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="customer_state" :value="__('State')" />
                                    <x-text-input id="customer_state" name="customer_state" type="text" class="mt-1 block w-full" 
                                                  :value="old('customer_state', $quotation->customer_state)" />
                                    <x-input-error :messages="$errors->get('customer_state')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="customer_postal_code" :value="__('Postal Code')" />
                                    <x-text-input id="customer_postal_code" name="customer_postal_code" type="text" class="mt-1 block w-full" 
                                                  :value="old('customer_postal_code', $quotation->customer_postal_code)" />
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
                                                  :value="old('title', $quotation->title)" required 
                                                  placeholder="Brief description of the quotation" />
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="description" :value="__('Description')" />
                                    <textarea id="description" name="description" rows="4"
                                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                              placeholder="Detailed scope of work or project description">{{ old('description', $quotation->description) }}</textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <div>
                                        <x-input-label for="customer_segment_id" :value="__('Customer Segment')" />
                                        <select id="customer_segment_id" name="customer_segment_id"
                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">Select Segment</option>
                                            @foreach($customerSegments as $segment)
                                                <option value="{{ $segment->id }}" data-discount="{{ $segment->default_discount_percentage }}" data-color="{{ $segment->color }}"
                                                        {{ old('customer_segment_id', $quotation->customer_segment_id) == $segment->id ? 'selected' : '' }}>
                                                    {{ $segment->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('customer_segment_id')" class="mt-2" />
                                        @if($quotation->customerSegment)
                                            <div class="mt-2 text-sm text-gray-600">
                                                <div class="flex items-center">
                                                    <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $quotation->customerSegment->color }}"></div>
                                                    <span>Default discount: {{ $quotation->customerSegment->default_discount_percentage }}%</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        <x-input-label for="team_id" :value="__('Team')" />
                                        <select id="team_id" name="team_id"
                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">No Team</option>
                                            @foreach($teams as $team)
                                                <option value="{{ $team->id }}" {{ old('team_id', $quotation->team_id) == $team->id ? 'selected' : '' }}>
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
                                                <option value="{{ $assignee->id }}" {{ old('assigned_to', $quotation->assigned_to) == $assignee->id ? 'selected' : '' }}>
                                                    {{ $assignee->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('assigned_to')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="valid_until" :value="__('Valid Until')" />
                                        <x-text-input id="valid_until" name="valid_until" type="date" class="mt-1 block w-full" 
                                                      :value="old('valid_until', $quotation->valid_until ? $quotation->valid_until->toDateString() : '')" />
                                        <x-input-error :messages="$errors->get('valid_until')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Items Display (Read-only) -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Items</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-4">
                                    <strong>Note:</strong> Item editing is not available in this version. 
                                    To modify items, please create a new quotation or contact system administrator.
                                </p>
                                
                                @if($quotation->items->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Unit</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                @foreach($quotation->items as $item)
                                                    <tr>
                                                        <td class="px-4 py-2">
                                                            <div class="text-sm text-gray-900">{{ $item->description }}</div>
                                                            @if($item->item_code)
                                                                <div class="text-xs text-gray-500">Code: {{ $item->item_code }}</div>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-2 text-center text-sm text-gray-900">{{ $item->unit }}</td>
                                                        <td class="px-4 py-2 text-right text-sm text-gray-900">{{ number_format($item->quantity, 2) }}</td>
                                                        <td class="px-4 py-2 text-right text-sm text-gray-900">RM {{ number_format($item->unit_price, 2) }}</td>
                                                        <td class="px-4 py-2 text-right text-sm font-medium text-gray-900">RM {{ number_format($item->total_price, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr class="bg-gray-100 font-semibold">
                                                    <td colspan="4" class="px-4 py-2 text-right">Subtotal:</td>
                                                    <td class="px-4 py-2 text-right">RM {{ number_format($quotation->subtotal, 2) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">No items found.</p>
                                @endif
                            </div>
                        </div>

                        <!-- Financial Settings -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Financial Settings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="discount_percentage" :value="__('Discount (%)')" />
                                    <x-text-input id="discount_percentage" name="discount_percentage" type="number" 
                                                  class="mt-1 block w-full" :value="old('discount_percentage', $quotation->discount_percentage)" 
                                                  min="0" max="100" step="0.01" />
                                    <x-input-error :messages="$errors->get('discount_percentage')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="tax_percentage" :value="__('Tax (%)')" />
                                    <x-text-input id="tax_percentage" name="tax_percentage" type="number" 
                                                  class="mt-1 block w-full" :value="old('tax_percentage', $quotation->tax_percentage)" 
                                                  min="0" max="100" step="0.01" />
                                    <x-input-error :messages="$errors->get('tax_percentage')" class="mt-2" />
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
                                              placeholder="Payment terms, delivery conditions, etc.">{{ old('terms_conditions', $quotation->terms_conditions) }}</textarea>
                                    <x-input-error :messages="$errors->get('terms_conditions')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="notes" :value="__('Notes')" />
                                    <textarea id="notes" name="notes" rows="3"
                                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                              placeholder="Additional notes or clarifications">{{ old('notes', $quotation->notes) }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="internal_notes" :value="__('Internal Notes')" />
                                    <textarea id="internal_notes" name="internal_notes" rows="3"
                                              class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                              placeholder="Internal team notes (not visible to customer)">{{ old('internal_notes', $quotation->internal_notes) }}</textarea>
                                    <x-input-error :messages="$errors->get('internal_notes')" class="mt-2" />
                                    <p class="mt-1 text-sm text-gray-500">These notes are only visible to your team and will not appear in the PDF.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t">
                            <a href="{{ route('quotations.show', $quotation) }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Update Quotation') }}
                            </x-primary-button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection