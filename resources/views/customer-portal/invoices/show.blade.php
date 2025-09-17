<x-customer-portal.layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $invoice->number }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Invoice Details
                </p>
            </div>
            <div class="flex items-center space-x-3">
                @if(Auth::guard('customer-portal')->user()->can_download_pdfs)
                    <a href="{{ route('customer-portal.invoices.pdf', $invoice) }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download PDF
                    </a>
                @endif
                <a href="{{ route('customer-portal.invoices.index') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Status and Summary -->
            <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($invoice->status === 'PAID') bg-green-100 text-green-800
                                @elseif($invoice->status === 'OVERDUE') bg-red-100 text-red-800
                                @elseif($invoice->status === 'PARTIAL') bg-yellow-100 text-yellow-800
                                @elseif($invoice->status === 'UNPAID') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $invoice->status }}
                            </span>
                            <span class="text-sm text-gray-500">
                                Due {{ $invoice->due_date->format('M d, Y') }}
                                @if($invoice->status === 'OVERDUE')
                                    ({{ $invoice->due_date->diffForHumans() }})
                                @endif
                            </span>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-gray-900">RM {{ number_format($invoice->total, 2) }}</div>
                            @if($invoice->outstanding_amount > 0)
                                <div class="text-sm text-red-600">Outstanding: RM {{ number_format($invoice->outstanding_amount, 2) }}</div>
                            @else
                                <div class="text-sm text-green-600">Fully Paid</div>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Progress Bar -->
                    @if($invoice->total > 0)
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($invoice->paid_amount / $invoice->total) * 100 }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-1">
                            <span>Paid: RM {{ number_format($invoice->paid_amount, 2) }}</span>
                            <span>{{ number_format(($invoice->paid_amount / $invoice->total) * 100, 1) }}% Complete</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- Customer Information -->
                    <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Name:</span>
                                    <span class="text-gray-900">{{ $invoice->customer_name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Email:</span>
                                    <span class="text-gray-900">{{ $invoice->customer_email }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Phone:</span>
                                    <span class="text-gray-900">{{ $invoice->customer_phone }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Company:</span>
                                    <span class="text-gray-900">{{ $invoice->customer_company ?: 'N/A' }}</span>
                                </div>
                            </div>
                            @if($invoice->customer_address)
                                <div class="mt-4">
                                    <span class="font-medium text-gray-700">Address:</span>
                                    <p class="text-gray-900 mt-1">{{ $invoice->customer_address }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Items</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($invoice->items as $item)
                                            <tr>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $item->description }}</div>
                                                    @if($item->notes)
                                                        <div class="text-sm text-gray-500">{{ $item->notes }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-center text-sm text-gray-900">{{ $item->quantity }}</td>
                                                <td class="px-6 py-4 text-right text-sm text-gray-900">RM {{ number_format($item->unit_price, 2) }}</td>
                                                <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">RM {{ number_format($item->total, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Financial Summary -->
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Summary</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="text-gray-900">RM {{ number_format($invoice->subtotal, 2) }}</span>
                                </div>
                                @if($invoice->discount_amount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Discount:</span>
                                        <span class="text-red-600">-RM {{ number_format($invoice->discount_amount, 2) }}</span>
                                    </div>
                                @endif
                                @if($invoice->tax_amount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Tax:</span>
                                        <span class="text-gray-900">RM {{ number_format($invoice->tax_amount, 2) }}</span>
                                    </div>
                                @endif
                                <hr class="my-2">
                                <div class="flex justify-between font-medium text-lg">
                                    <span class="text-gray-900">Total:</span>
                                    <span class="text-gray-900">RM {{ number_format($invoice->total, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-green-600">
                                    <span>Paid:</span>
                                    <span>RM {{ number_format($invoice->paid_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between font-medium text-red-600">
                                    <span>Outstanding:</span>
                                    <span>RM {{ number_format($invoice->outstanding_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Info -->
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Information</h3>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Invoice Date:</span>
                                    <span class="text-gray-900 block">{{ $invoice->invoice_date->format('M d, Y') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Due Date:</span>
                                    <span class="text-gray-900 block">{{ $invoice->due_date->format('M d, Y') }}</span>
                                </div>
                                @if($invoice->quotation)
                                    <div>
                                        <span class="font-medium text-gray-700">Quotation:</span>
                                        <a href="{{ route('customer-portal.quotations.show', $invoice->quotation) }}" class="text-blue-600 hover:text-blue-500 block">
                                            {{ $invoice->quotation->number }}
                                        </a>
                                    </div>
                                @endif
                                <div>
                                    <span class="font-medium text-gray-700">Created By:</span>
                                    <span class="text-gray-900 block">{{ $invoice->createdBy->name }}</span>
                                </div>
                                @if($invoice->description)
                                    <div>
                                        <span class="font-medium text-gray-700">Description:</span>
                                        <p class="text-gray-900 mt-1">{{ $invoice->description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Payment History -->
                    @if(Auth::guard('customer-portal')->user()->can_view_payment_history && $invoice->paymentRecords->count() > 0)
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Payment History</h3>
                                    <a href="{{ route('customer-portal.payments.index') }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                        View All
                                    </a>
                                </div>
                                
                                <div class="space-y-3">
                                    @foreach($invoice->paymentRecords->take(5) as $payment)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $payment->receipt_number }}</p>
                                                <p class="text-sm text-gray-600">{{ $payment->payment_method }}</p>
                                                <p class="text-xs text-gray-500">{{ $payment->payment_date->format('M d, Y') }}</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($payment->status === 'CLEARED') bg-green-100 text-green-800
                                                    @elseif($payment->status === 'PENDING') bg-yellow-100 text-yellow-800
                                                    @elseif($payment->status === 'BOUNCED') bg-red-100 text-red-800
                                                    @else bg-gray-100 text-gray-800
                                                    @endif">
                                                    {{ $payment->status }}
                                                </span>
                                                <p class="text-sm font-medium text-gray-900 mt-1">RM {{ number_format($payment->amount, 2) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bank Details (if outstanding amount) -->
            @if($invoice->outstanding_amount > 0)
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-blue-900 mb-4">Payment Instructions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div>
                            <h4 class="font-medium text-blue-800 mb-2">Bank Transfer Details</h4>
                            <div class="space-y-1">
                                <div><span class="font-medium">Bank:</span> Maybank</div>
                                <div><span class="font-medium">Account Name:</span> {{ $invoice->company->name }}</div>
                                <div><span class="font-medium">Account Number:</span> 123456789012</div>
                                <div><span class="font-medium">Swift Code:</span> MBBEMYKL</div>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-blue-800 mb-2">Payment Reference</h4>
                            <div class="space-y-1">
                                <div><span class="font-medium">Reference:</span> {{ $invoice->number }}</div>
                                <div><span class="font-medium">Amount:</span> RM {{ number_format($invoice->outstanding_amount, 2) }}</div>
                            </div>
                            <div class="mt-3 p-3 bg-blue-100 rounded">
                                <p class="text-blue-800 text-xs">
                                    Please include the invoice number ({{ $invoice->number }}) as your payment reference to ensure proper allocation.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-customer-portal.layouts.app>