@extends('layouts.app')

@section('content')
@php
    // Check if current user is the owner (created by them)
    $isOwner = $invoice->created_by === auth()->id();
    $canFullyEdit = $isOwner && $invoice->canBeEdited();
@endphp

<div class="min-h-screen bg-gray-50" x-data="invoiceEditor()">
    <!-- Header Bar -->
    <div class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-40">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('invoices.show', $invoice) }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-lg font-semibold text-gray-900">{{ $isOwner ? 'Edit' : 'View' }} Invoice {{ $invoice->number }}</h1>
                <span class="px-2 py-1 text-xs font-medium {{ $invoice->status === 'DRAFT' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800' }} rounded">{{ $invoice->status }}</span>
                @if(!$isOwner)
                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded">View Only</span>
                @endif
            </div>
            <div class="flex items-center space-x-3 relative z-50">
                <button type="button" @click="previewPDF" class="relative z-50 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                    Preview PDF
                </button>
                @if($isOwner && $invoice->canBeEdited())
                    <button type="button" @click="updateInvoice" class="relative z-50 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 cursor-pointer">
                        Update Invoice
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Status Notice -->
    @if(!$isOwner)
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mx-6 mt-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>View Only Mode:</strong> This invoice was created by another user. You can view details but cannot make any changes.
                        Created by: <strong>{{ $invoice->createdBy->name ?? 'Unknown' }}</strong>
                    </p>
                </div>
            </div>
        </div>
    @elseif($isOwner && !$invoice->canBeEdited())
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mx-6 mt-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Limited editing is available for invoices with status: <strong>{{ $invoice->status }}</strong>.
                        Line items cannot be modified. Only customer information, notes, and terms can be updated.
                    </p>
                </div>
            </div>
        </div>
    @endif

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
                            <div class="flex flex-col items-center lg:items-end w-full lg:w-1/3 order-1 lg:order-2">
                                <img src="{{ auth()->user()->company->logo_url ?? '/images/placeholder-logo.png' }}" alt="Company Logo" class="h-20">
                            </div>
                        </div>
                    </div>

                    <!-- Gap between rows -->
                    <div class="h-2 bg-gray-50"></div>

                    <!-- Row 3: Customer Billing Details and Invoice Details -->
                    <div class="px-4 md:px-6 lg:px-8 py-6 bg-white border border-gray-200 rounded-lg mx-2 md:mx-4 lg:mx-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Customer Billing Details - Left (Editable) -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-6">Bill To</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Company/Customer Name</label>
                                        <input type="text" x-model="customerName"
                                               {{ !$isOwner ? 'readonly' : '' }}
                                               class="w-full border-0 border-b border-gray-300 focus:border-blue-500 focus:ring-0 px-0 py-2 text-sm font-medium text-gray-900 bg-transparent"
                                               placeholder="Enter customer name">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Email</label>
                                        <input type="email" x-model="customerEmail"
                                               {{ !$isOwner ? 'readonly' : '' }}
                                               class="w-full border-0 border-b border-gray-300 focus:border-blue-500 focus:ring-0 px-0 py-2 text-sm text-gray-700 bg-transparent {{ !$isOwner ? 'cursor-not-allowed bg-gray-50' : '' }}"
                                               placeholder="customer@email.com">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Phone</label>
                                        <input type="tel" x-model="customerPhone"
                                               {{ !$isOwner ? 'readonly' : '' }}
                                               class="w-full border-0 border-b border-gray-300 focus:border-blue-500 focus:ring-0 px-0 py-2 text-sm text-gray-700 bg-transparent {{ !$isOwner ? 'cursor-not-allowed bg-gray-50' : '' }}"
                                               placeholder="+60 12-345 6789">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Address</label>
                                        <textarea x-model="customerAddress"
                                                  {{ !$isOwner ? 'readonly' : '' }}
                                                  class="w-full border-0 border-b border-gray-300 focus:border-blue-500 focus:ring-0 px-0 py-2 text-sm text-gray-700 bg-transparent resize-none {{ !$isOwner ? 'cursor-not-allowed bg-gray-50' : '' }}"
                                                  rows="3" placeholder="Customer address"></textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">City</label>
                                            <input type="text" x-model="customerCity"
                                                   {{ !$isOwner ? 'readonly' : '' }}
                                                   class="w-full border-0 border-b border-gray-300 focus:border-blue-500 focus:ring-0 px-0 py-2 text-sm text-gray-700 bg-transparent {{ !$isOwner ? 'cursor-not-allowed bg-gray-50' : '' }}"
                                                   placeholder="City">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Postal Code</label>
                                            <input type="text" x-model="customerPostalCode"
                                                   {{ !$isOwner ? 'readonly' : '' }}
                                                   class="w-full border-0 border-b border-gray-300 focus:border-blue-500 focus:ring-0 px-0 py-2 text-sm text-gray-700 bg-transparent {{ !$isOwner ? 'cursor-not-allowed bg-gray-50' : '' }}"
                                                   placeholder="12345">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Details - Right -->
                            <div class="lg:col-span-2">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Invoice Details</h3>
                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-500">Invoice Number:</span>
                                                <span class="text-sm font-bold text-gray-900">{{ $invoice->number }}</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-500">Invoice Date:</span>
                                                <span class="text-sm text-gray-700">{{ $invoice->invoice_date ? $invoice->invoice_date->format('M d, Y') : $invoice->created_at->format('M d, Y') }}</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-500">Due Date:</span>
                                                <input type="date" x-model="dueDate"
                                                       {{ !$isOwner ? 'readonly' : '' }}
                                                       class="text-sm text-gray-700 border-0 border-b border-gray-300 focus:border-blue-500 focus:ring-0 px-0 py-1 bg-transparent {{ !$isOwner ? 'cursor-not-allowed' : '' }}">
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-500">Payment Terms:</span>
                                                <input type="number" x-model="paymentTerms"
                                                       {{ !$isOwner ? 'readonly' : '' }}
                                                       class="text-sm text-gray-700 border-0 border-b border-gray-300 focus:border-blue-500 focus:ring-0 px-0 py-1 bg-transparent w-16 {{ !$isOwner ? 'cursor-not-allowed' : '' }}">
                                                <span class="text-xs text-gray-500 ml-1">days</span>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">PO Number</label>
                                                <input type="text" x-model="poNumber"
                                                       {{ !$isOwner ? 'readonly' : '' }}
                                                       class="w-full text-sm text-gray-700 border border-gray-300 rounded-md focus:border-blue-500 focus:ring-1 focus:ring-blue-500 px-2 py-1 {{ !$isOwner ? 'cursor-not-allowed bg-gray-50' : '' }}"
                                                       placeholder="Optional">
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-500">Customer Segment:</span>
                                                <span class="text-sm text-gray-700">
                                                    @if($invoice->customer && $invoice->customer->customerSegment)
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                                              style="background-color: {{ $invoice->customer->customerSegment->color }}20; color: {{ $invoice->customer->customerSegment->color }}">
                                                            {{ $invoice->customer->customerSegment->name }}
                                                        </span>
                                                    @elseif($invoice->customerSegment)
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                                                              style="background-color: {{ $invoice->customerSegment->color }}20; color: {{ $invoice->customerSegment->color }}">
                                                            {{ $invoice->customerSegment->name }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">Not set</span>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-gray-500">Status:</span>
                                                <span class="text-sm px-2 py-1 rounded {{ $invoice->status === 'DRAFT' ? 'bg-gray-100 text-gray-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ $invoice->status }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Assignment</h3>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Team</label>
                                                <select x-model="teamId" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                    <option value="">Select Team</option>
                                                    @foreach($teams as $team)
                                                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Assigned To</label>
                                                <select x-model="assignedTo" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                    <option value="">Select Assignee</option>
                                                    @foreach($assignees as $assignee)
                                                        <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gap between rows -->
                    <div class="h-2 bg-gray-50"></div>

                    <!-- Row 4: Line Items (Read Only) -->
                    <div class="px-4 md:px-6 lg:px-8 py-6 bg-white border border-gray-200 rounded-lg mx-2 md:mx-4 lg:mx-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Invoice Items (Read Only)</h3>

                        @if($invoice->items->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full table-fixed">
                                    <thead>
                                        <tr class="border-b-2 border-gray-300">
                                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide" style="width: 50%;">Description</th>
                                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide" style="width: 12%;">Qty</th>
                                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide" style="width: 19%;">Unit Price</th>
                                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wide" style="width: 19%;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice->items as $item)
                                            <tr class="border-b border-gray-200">
                                                <td class="px-4 py-4" style="width: 50%;">
                                                    <div class="font-medium text-gray-900 text-sm">{{ $item->description }}</div>
                                                    @if($item->specifications)
                                                        <div class="text-xs text-gray-500 mt-1">{{ $item->specifications }}</div>
                                                    @endif
                                                    @if($item->notes)
                                                        <div class="text-xs text-gray-500 mt-1">Note: {{ $item->notes }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-4 text-center text-sm text-gray-700" style="width: 12%;">{{ $item->quantity }} {{ $item->unit }}</td>
                                                <td class="px-4 py-4 text-right text-sm text-gray-700" style="width: 19%;">RM {{ number_format($item->unit_price, 2) }}</td>
                                                <td class="px-4 py-4 text-right font-medium text-gray-900 text-sm" style="width: 19%;">RM {{ number_format($item->total_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totals Section -->
                            <div class="mt-6 flex justify-end">
                                <div class="w-64 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotal:</span>
                                        <span class="font-medium">RM {{ number_format($invoice->subtotal, 2) }}</span>
                                    </div>
                                    @if($invoice->discount_amount > 0)
                                        <div class="flex justify-between text-sm text-green-600">
                                            <span>Discount ({{ $invoice->discount_percentage }}%):</span>
                                            <span>-RM {{ number_format($invoice->discount_amount, 2) }}</span>
                                        </div>
                                    @endif
                                    @if($invoice->tax_amount > 0)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Tax ({{ $invoice->tax_percentage }}%):</span>
                                            <span class="font-medium">RM {{ number_format($invoice->tax_amount, 2) }}</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2">
                                        <span>Total:</span>
                                        <span>RM {{ number_format($invoice->total, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <p>No items found in this invoice.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Gap between rows -->
                    <div class="h-2 bg-gray-50"></div>

                    <!-- Row 5: Notes, Terms & Conditions (Editable) -->
                    <div class="px-4 md:px-6 lg:px-8 py-6 bg-white border border-gray-200 rounded-lg mx-2 md:mx-4 lg:mx-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Notes -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Description/Notes</h3>
                                <textarea x-model="description"
                                          {{ !$isOwner ? 'readonly' : '' }}
                                          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ !$isOwner ? 'cursor-not-allowed bg-gray-50' : '' }}"
                                          rows="6" placeholder="Add description or notes..."></textarea>
                            </div>

                            <!-- Terms & Conditions -->
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Terms & Conditions</h3>
                                <textarea x-model="termsConditions"
                                          {{ !$isOwner ? 'readonly' : '' }}
                                          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ !$isOwner ? 'cursor-not-allowed bg-gray-50' : '' }}"
                                          rows="6" placeholder="Add terms and conditions..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function invoiceEditor() {
    return {
        // Initialize with existing invoice data
        customerName: @json($invoice->customer_name),
        customerEmail: @json($invoice->customer_email),
        customerPhone: @json($invoice->customer_phone),
        customerAddress: @json($invoice->customer_address),
        customerCity: @json($invoice->customer_city),
        customerState: @json($invoice->customer_state),
        customerPostalCode: @json($invoice->customer_postal_code),
        description: @json($invoice->description),
        termsConditions: @json($invoice->terms_conditions),
        dueDate: @json($invoice->due_date ? $invoice->due_date->format('Y-m-d') : ''),
        paymentTerms: @json($invoice->payment_terms ?? 30),
        poNumber: @json($invoice->po_number ?? ''),
        teamId: @json($invoice->team_id),
        assignedTo: @json($invoice->assigned_to),

        async updateInvoice() {
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('_method', 'PUT');
                formData.append('customer_name', this.customerName || '');
                formData.append('customer_email', this.customerEmail || '');
                formData.append('customer_phone', this.customerPhone || '');
                formData.append('customer_address', this.customerAddress || '');
                formData.append('customer_city', this.customerCity || '');
                formData.append('customer_state', this.customerState || '');
                formData.append('customer_postal_code', this.customerPostalCode || '');
                formData.append('description', this.description || '');
                formData.append('terms_conditions', this.termsConditions || '');
                formData.append('due_date', this.dueDate || '');
                formData.append('payment_terms_days', this.paymentTerms || 30);
                formData.append('po_number', this.poNumber || '');
                formData.append('team_id', this.teamId || '');
                formData.append('assigned_to', this.assignedTo || '');

                const response = await fetch(`/invoices/{{ $invoice->id }}`, {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    window.location.href = `/invoices/{{ $invoice->id }}`;
                } else {
                    const error = await response.text();
                    throw new Error('Failed to update invoice');
                }
            } catch (error) {
                console.error('Error updating invoice:', error);
                alert('Failed to update invoice. Please try again.');
            }
        },

        async previewPDF() {
            try {
                window.open(`/invoices/{{ $invoice->id }}/preview`, '_blank');
            } catch (error) {
                console.error('Error previewing PDF:', error);
                alert('Failed to preview PDF. Please try again.');
            }
        }
    };
}
</script>
@endsection