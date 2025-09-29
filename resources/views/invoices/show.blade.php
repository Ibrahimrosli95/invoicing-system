@extends('layouts.app')

@section('title', 'Invoice Details')

@section('header')
<div class="bg-white border-b border-gray-200 px-4 sm:px-6 py-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Invoice {{ $invoice->number }}
        </h2>

        <!-- Mobile Action Menu -->
        <div class="sm:hidden">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg flex items-center justify-between w-full">
                    <span>Actions</span>
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                    <div class="py-2">
                        @can('view', $invoice)
                            <a href="{{ route('invoices.preview', $invoice) }}"
                               target="_blank"
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Preview PDF
                            </a>
                            <a href="{{ route('invoices.pdf', $invoice) }}"
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                               id="download-pdf-mobile">
                                <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download PDF
                            </a>
                            <button onclick="sharePDF('{{ route('invoices.pdf', $invoice) }}', '{{ $invoice->number }}')"
                                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                </svg>
                                Share PDF
                            </button>
                        @endcan

                        @if($invoice->status !== 'PAID' && $invoice->status !== 'CANCELLED')
                            @can('recordPayment', $invoice)
                                <div class="border-t border-gray-100 my-2"></div>
                                <a href="{{ route('invoices.payment-form', $invoice) }}"
                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    Record Payment
                                </a>
                            @endcan
                        @endif

                        @can('update', $invoice)
                            @if($invoice->canBeEdited())
                                <a href="{{ route('invoices.edit', $invoice) }}"
                                   class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit Invoice
                                </a>
                            @endif
                        @endcan

                        @can('update', $invoice)
                            @if($invoice->canBeSent())
                                <form method="POST" action="{{ route('invoices.mark-sent', $invoice) }}" class="inline w-full">
                                    @csrf
                                    <button type="submit"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            onclick="return confirm('Mark this invoice as sent?')">
                                        <svg class="w-4 h-4 mr-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Mark as Sent
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Actions -->
        <div class="hidden sm:flex space-x-2">
            @can('view', $invoice)
                <a href="{{ route('invoices.preview', $invoice) }}"
                   target="_blank"
                   class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Preview PDF
                </a>
                <a href="{{ route('invoices.pdf', $invoice) }}"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center"
                   id="download-pdf-desktop">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download PDF
                </a>
                <button onclick="sharePDF('{{ route('invoices.pdf', $invoice) }}', '{{ $invoice->number }}')"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                    </svg>
                    Share PDF
                </button>
            @endcan

            @if($invoice->status !== 'PAID' && $invoice->status !== 'CANCELLED')
                @can('recordPayment', $invoice)
                    <a href="{{ route('invoices.payment-form', $invoice) }}"
                       class="bg-green-600 hover:bg-green-800 text-white font-bold py-2 px-4 rounded flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                        Record Payment
                    </a>
                @endcan
            @endif

            @can('update', $invoice)
                @if($invoice->canBeEdited())
                    <a href="{{ route('invoices.edit', $invoice) }}"
                       class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Invoice
                    </a>
                @endif
            @endcan

            @can('update', $invoice)
                @if($invoice->canBeSent())
                    <form method="POST" action="{{ route('invoices.mark-sent', $invoice) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded flex items-center"
                                onclick="return confirm('Mark this invoice as sent?')">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
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

    <div class="py-4 sm:py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 sm:mt-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                <div class="lg:col-span-2 order-2 lg:order-1">
                    <!-- Status Information Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 sm:mb-6">
                        <div class="p-4 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Status & Details</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
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
                <div class="space-y-4 sm:space-y-6 order-1 lg:order-2">
                    <!-- Payment History -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 sm:p-6">
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
                        <div class="p-4 sm:p-6">
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
                        <div class="p-4 sm:p-6">
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

@push('scripts')
<script>
    // Fix PDF download functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Handle desktop download button
        const downloadButtonDesktop = document.getElementById('download-pdf-desktop');
        if (downloadButtonDesktop) {
            downloadButtonDesktop.addEventListener('click', function(e) {
                e.preventDefault();
                downloadPDF(this.href, '{{ $invoice->number }}');
            });
        }

        // Handle mobile download button
        const downloadButtonMobile = document.getElementById('download-pdf-mobile');
        if (downloadButtonMobile) {
            downloadButtonMobile.addEventListener('click', function(e) {
                e.preventDefault();
                downloadPDF(this.href, '{{ $invoice->number }}');
            });
        }
    });

    // PDF Download function
    function downloadPDF(url, invoiceNumber) {
        // Show loading indicator
        const originalTexts = {};
        const buttons = document.querySelectorAll('#download-pdf-desktop, #download-pdf-mobile');

        buttons.forEach(button => {
            originalTexts[button.id] = button.textContent;
            if (button.tagName === 'A') {
                button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Downloading...';
            } else {
                button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Downloading...';
            }
            button.disabled = true;
        });

        // Create a temporary link to trigger download
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.blob();
        })
        .then(blob => {
            // Create blob URL and trigger download
            const blobUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = blobUrl;
            link.download = `invoice-${invoiceNumber}.pdf`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(blobUrl);

            // Show success message
            showNotification('PDF downloaded successfully!', 'success');
        })
        .catch(error => {
            console.error('Download error:', error);
            showNotification('Failed to download PDF. Please try again.', 'error');
        })
        .finally(() => {
            // Restore original button states
            buttons.forEach(button => {
                if (button.tagName === 'A') {
                    button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>Download PDF';
                } else {
                    button.innerHTML = '<svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>Download PDF';
                }
                button.disabled = false;
            });
        });
    }

    // PDF Share function
    function sharePDF(url, invoiceNumber) {
        const filename = `invoice-${invoiceNumber}.pdf`;

        // Check if Web Share API is supported
        if (navigator.share) {
            // First fetch the PDF as blob
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.blob())
            .then(blob => {
                const file = new File([blob], filename, { type: 'application/pdf' });

                return navigator.share({
                    title: `Invoice ${invoiceNumber}`,
                    text: `Invoice ${invoiceNumber} from {{ $invoice->company->name ?? 'Company' }}`,
                    files: [file]
                });
            })
            .then(() => {
                showNotification('PDF shared successfully!', 'success');
            })
            .catch(error => {
                console.error('Share error:', error);
                // Fallback to URL sharing if file sharing fails
                fallbackShare(url, invoiceNumber);
            });
        } else {
            // Fallback for browsers that don't support Web Share API
            fallbackShare(url, invoiceNumber);
        }
    }

    // Fallback share function
    function fallbackShare(url, invoiceNumber) {
        const shareText = `Invoice ${invoiceNumber} - `;
        const shareUrl = window.location.origin + url;

        // Create modal for sharing options
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
        modal.innerHTML = `
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Share Invoice PDF</h3>
                    <div class="space-y-3">
                        <button onclick="shareViaWhatsApp('${shareText}', '${shareUrl}')" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-500 hover:bg-green-600">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                            </svg>
                            WhatsApp
                        </button>

                        <button onclick="shareViaEmail('${shareText}', '${shareUrl}')" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-500 hover:bg-blue-600">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Email
                        </button>

                        <button onclick="copyToClipboard('${shareUrl}')" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Copy Link
                        </button>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button onclick="closeShareModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeShareModal();
            }
        });
    }

    // Share functions
    function shareViaWhatsApp(text, url) {
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(text + url)}`;
        window.open(whatsappUrl, '_blank');
        closeShareModal();
    }

    function shareViaEmail(subject, url) {
        const emailUrl = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent('Please find the invoice PDF at: ' + url)}`;
        window.location.href = emailUrl;
        closeShareModal();
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification('Link copied to clipboard!', 'success');
            closeShareModal();
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showNotification('Link copied to clipboard!', 'success');
            closeShareModal();
        });
    }

    function closeShareModal() {
        const modal = document.querySelector('.fixed.inset-0.bg-gray-600');
        if (modal) {
            modal.remove();
        }
    }

    // Show notification function
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';

        notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
</script>
@endpush