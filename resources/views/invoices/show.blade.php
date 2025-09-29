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
        <div class="max-w-4xl mx-auto px-4 md:px-6 lg:px-8">
            <!-- Invoice Document using the shared partial -->
            @include('invoices.partials.document', [
                'invoice' => $invoice,
                'settings' => [
                    'optional_sections' => [
                        'show_company_logo' => true,
                        'show_shipping' => !empty($invoice->shipping_info),
                        'show_payment_instructions' => !empty($invoice->payment_instructions),
                        'show_signatures' => true
                    ]
                ],
                'mode' => 'view'
            ])
        </div>

        <!-- Additional Information Sidebar -->
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <!-- Status Information Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Status & Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                                        @if($invoice->status === 'OVERDUE')
                                            <div class="text-red-600 text-sm mt-1">({{ $invoice->overdue_days }} days overdue)</div>
                                        @endif
                                    </dd>
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