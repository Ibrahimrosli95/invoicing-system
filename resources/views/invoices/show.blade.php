@extends('layouts.app')

@section('title', 'Invoice Details')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Invoice {{ $invoice->number }}
        </h2>
        <div class="flex space-x-2">
            @can('view', $invoice)
                <a href="{{ route('invoices.preview', $invoice) }}"
                   target="_blank"
                   class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    Preview PDF
                </a>
                <a href="{{ route('invoices.pdf', $invoice) }}"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Download PDF
                </a>
            @endcan

            @if($invoice->status !== 'PAID' && $invoice->status !== 'CANCELLED')
                @can('recordPayment', $invoice)
                    <a href="{{ route('invoices.payment-form', $invoice) }}"
                       class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Record Payment
                    </a>
                @endcan
            @endif

            @can('update', $invoice)
                @if($invoice->canBeEdited())
                    <a href="{{ route('invoices.edit', $invoice) }}"
                       class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                        Edit Invoice
                    </a>
                @endif
            @endcan

            @can('update', $invoice)
                @if($invoice->canBeSent())
                    <form method="POST" action="{{ route('invoices.mark-sent', $invoice) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                                onclick="return confirm('Mark this invoice as sent?')">
                            Mark as Sent
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Invoice Details -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->number }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        @php
                                            $statusColors = [
                                                'DRAFT' => 'bg-gray-100 text-gray-800',
                                                'SENT' => 'bg-blue-100 text-blue-800',
                                                'PARTIAL' => 'bg-yellow-100 text-yellow-800',
                                                'PAID' => 'bg-green-100 text-green-800',
                                                'OVERDUE' => 'bg-red-100 text-red-800',
                                                'CANCELLED' => 'bg-red-100 text-red-800'
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $invoice->status }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">@displayDate($invoice->created_at)</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @displayDate($invoice->due_date)
                                        @if($invoice->status === 'OVERDUE')
                                            <span class="text-red-600">({{ $invoice->overdue_days }} days overdue)</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Payment Terms</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->payment_terms_days }} days</dd>
                                </div>
                                @if($invoice->quotation)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">From Quotation</dt>
                                    <dd class="mt-1 text-sm">
                                        <a href="{{ route('quotations.show', $invoice->quotation) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            {{ $invoice->quotation->number }}
                                        </a>
                                    </dd>
                                </div>
                                @endif
                                @if($invoice->assignedTo)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->assignedTo->name }}</dd>
                                </div>
                                @endif
                                @if($invoice->team)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Team</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->team->name }}</dd>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->customer_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->customer_phone }}</dd>
                                </div>
                                @if($invoice->customer_email)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $invoice->customer_email }}</dd>
                                </div>
                                @endif
                                @if($invoice->customerSegment)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Customer Segment</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium"
                                              style="background-color: {{ $invoice->customerSegment->color }}20; color: {{ $invoice->customerSegment->color }};">
                                            <div class="w-2 h-2 rounded-full mr-2" style="background-color: {{ $invoice->customerSegment->color }};"></div>
                                            {{ $invoice->customerSegment->name }}
                                        </span>
                                    </dd>
                                </div>
                                @endif
                                @if($invoice->customer_address)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $invoice->customer_address }}<br>
                                        @if($invoice->customer_city){{ $invoice->customer_city }}, @endif
                                        @if($invoice->customer_state){{ $invoice->customer_state }} @endif
                                        @if($invoice->customer_postal_code){{ $invoice->customer_postal_code }}@endif
                                    </dd>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Items</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($invoice->items as $item)
                                            <tr>
                                                <td class="px-6 py-4 text-sm text-gray-900">
                                                    <div class="font-medium">{{ $item->description }}</div>
                                                    @if($item->item_code)
                                                        <div class="text-gray-500">Code: {{ $item->item_code }}</div>
                                                    @endif
                                                    @if($item->specifications)
                                                        <div class="text-gray-500 text-xs mt-1">{{ $item->specifications }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->unit }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->quantity, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">RM {{ number_format($item->unit_price, 2) }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">RM {{ number_format($item->total_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Financial Summary -->
                            <div class="mt-6 flex justify-end">
                                <div class="w-72">
                                    <div class="border-t border-gray-200 pt-4">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Subtotal:</span>
                                            <span class="font-medium">RM {{ number_format($invoice->subtotal_amount, 2) }}</span>
                                        </div>
                                        @if($invoice->discount_percentage > 0)
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <span>Discount ({{ $invoice->discount_percentage }}%):</span>
                                            <span>-RM {{ number_format($invoice->discount_amount, 2) }}</span>
                                        </div>
                                        @endif
                                        @if($invoice->tax_percentage > 0)
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <span>Tax ({{ $invoice->tax_percentage }}%):</span>
                                            <span>RM {{ number_format($invoice->tax_amount, 2) }}</span>
                                        </div>
                                        @endif
                                        <div class="flex justify-between text-lg font-bold border-t border-gray-200 pt-2 mt-2">
                                            <span>Total Amount:</span>
                                            <span>RM {{ number_format($invoice->total_amount, 2) }}</span>
                                        </div>
                                        @if($invoice->amount_paid > 0)
                                        <div class="flex justify-between text-sm text-green-600 mt-2">
                                            <span>Amount Paid:</span>
                                            <span>RM {{ number_format($invoice->amount_paid, 2) }}</span>
                                        </div>
                                        @endif
                                        @if($invoice->amount_due > 0)
                                        <div class="flex justify-between text-lg font-bold text-red-600 border-t border-gray-200 pt-2 mt-2">
                                            <span>Amount Due:</span>
                                            <span>RM {{ number_format($invoice->amount_due, 2) }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Payment History -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Payment History</h3>
                            @if($invoice->paymentRecords->count() > 0)
                                <div class="space-y-3">
                                    @foreach($invoice->paymentRecords as $payment)
                                        <div class="border-l-4 border-green-500 pl-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        RM {{ number_format($payment->amount, 2) }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $payment->payment_method }} • @displayDate($payment->payment_date)
                                                    </div>
                                                    @if($payment->reference_number)
                                                        <div class="text-xs text-gray-500">
                                                            Ref: {{ $payment->reference_number }}
                                                        </div>
                                                    @endif
                                                    @if($payment->receipt_number)
                                                        <div class="text-xs text-blue-600">
                                                            Receipt: {{ $payment->receipt_number }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                    {{ $payment->status === 'CLEARED' ? 'bg-green-100 text-green-800' : 
                                                       ($payment->status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ $payment->status }}
                                                </span>
                                            </div>
                                            @if($payment->notes)
                                                <div class="text-xs text-gray-600 mt-1">
                                                    {{ $payment->notes }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No payments recorded yet.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($invoice->notes)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Notes</h3>
                            <div class="text-sm text-gray-700">
                                {!! nl2br(e($invoice->notes)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Terms & Conditions -->
                    @if($invoice->terms_conditions)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Terms & Conditions</h3>
                            <div class="text-sm text-gray-700">
                                {!! nl2br(e($invoice->terms_conditions)) !!}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection