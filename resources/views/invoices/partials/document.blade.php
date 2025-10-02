{{--
    Invoice Document Partial - Shared between builder, show page, and PDF

    Required variables:
    - $invoice: Invoice model with relationships (company, items, customer)
    - $settings: Array of optional sections and display settings
    - $mode: 'builder'|'view'|'pdf' - controls interactivity
--}}

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
                    {{ $invoice->company->name ?? 'Company Name' }}
                </h2>
                <div class="text-sm text-gray-700 space-y-2 leading-relaxed">
                    <div class="font-medium">{{ $invoice->company->address ?? '123 Business Street' }}</div>
                    <div>{{ $invoice->company->city ?? 'City' }}, {{ $invoice->company->state ?? 'State' }} {{ $invoice->company->postal_code ?? '12345' }}</div>
                    <div class="pt-2 space-y-1">
                        <div>
                            <span class="inline-block w-16 text-gray-500 text-xs uppercase tracking-wide">Phone:</span>
                            <span class="font-medium">{{ $invoice->company->phone ?? '+60 12-345 6789' }}</span>
                        </div>
                        <div>
                            <span class="inline-block w-16 text-gray-500 text-xs uppercase tracking-wide">Email:</span>
                            <span class="font-medium">{{ $invoice->company->email ?? 'info@company.com' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Logo - Right -->
            @if($settings['optional_sections']['show_company_logo'] ?? true)
            <div class="flex flex-col items-center lg:items-end w-full lg:w-1/3 order-1 lg:order-2">
                <div class="mb-4">
                    @if($invoice->company->logo_path)
                        <img src="{{ route('company.logo') }}" alt="Company Logo" class="h-20">
                    @else
                        <div class="h-20 w-20 bg-gray-100 rounded flex items-center justify-center">
                            <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h3M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Gap between rows -->
    <div class="h-2 bg-gray-50"></div>

    <!-- Row 3: Customer Billing Details and Invoice Details -->
    <div class="px-4 md:px-6 lg:px-8 py-6 bg-white border border-gray-200 rounded-lg mx-2 md:mx-4 lg:mx-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Customer Billing Details - Left -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Bill To</h3>
                <div class="space-y-1 text-sm">
                    <div class="font-medium text-gray-900">{{ $invoice->customer_name ?? 'Customer Name' }}</div>
                    @if($invoice->customer_company ?? false)
                        <div class="text-gray-600">{{ $invoice->customer_company }}</div>
                    @endif
                    @if($invoice->customer_address ?? false)
                        <div class="text-gray-600">{{ $invoice->customer_address }}</div>
                    @endif
                    @if($invoice->customer_city || $invoice->customer_state || $invoice->customer_postal_code)
                        <div class="text-gray-600">
                            @if($invoice->customer_city){{ $invoice->customer_city }}@endif
                            @if($invoice->customer_city && ($invoice->customer_state || $invoice->customer_postal_code)), @endif
                            @if($invoice->customer_state){{ $invoice->customer_state }}@endif
                            @if($invoice->customer_postal_code) {{ $invoice->customer_postal_code }}@endif
                        </div>
                    @endif
                    @if($invoice->customer_phone || $invoice->customer_email)
                        <div class="text-gray-600">
                            @if($invoice->customer_phone){{ $invoice->customer_phone }}@endif
                            @if($invoice->customer_phone && $invoice->customer_email) • @endif
                            @if($invoice->customer_email){{ $invoice->customer_email }}@endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Ship To - Middle -->
            @if(($settings['optional_sections']['show_shipping'] ?? false) && ($invoice->shipping_info ?? false))
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Ship To</h3>
                </div>
                <div class="space-y-1 text-sm">
                    @php
                        $shipping = is_array($invoice->shipping_info) ? $invoice->shipping_info : json_decode($invoice->shipping_info, true) ?? [];
                    @endphp
                    @if(($shipping['same_as_billing'] ?? false))
                        <div class="text-gray-600 italic">Same as billing address</div>
                    @else
                        @if($shipping['name'] ?? false)
                            <div class="font-medium text-gray-900">{{ $shipping['name'] }}</div>
                        @endif
                        @if($shipping['address'] ?? false)
                            <div class="text-gray-600">{{ $shipping['address'] }}</div>
                        @endif
                        @if($shipping['city'] || $shipping['state'] || $shipping['postal_code'])
                            <div class="text-gray-600">
                                @if($shipping['city']){{ $shipping['city'] }}@endif
                                @if($shipping['city'] && ($shipping['state'] || $shipping['postal_code'])), @endif
                                @if($shipping['state']){{ $shipping['state'] }}@endif
                                @if($shipping['postal_code']) {{ $shipping['postal_code'] }}@endif
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            @endif

            <!-- Invoice Details - Right -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Invoice Details</h3>
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600">Invoice #:</span>
                        <span class="text-sm font-mono font-semibold">{{ $invoice->number ?? 'INV-2025-000001' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600">Invoice Date:</span>
                        <span class="text-sm font-mono font-semibold">{{ $invoice->issued_date ? $invoice->issued_date->format('d/m/Y') : now()->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600">Due Date:</span>
                        <span class="text-sm font-mono font-semibold">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : now()->addDays(30)->format('d/m/Y') }}</span>
                    </div>
                    @if($invoice->po_number ?? false)
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600">PO Number:</span>
                        <span class="text-sm font-mono font-semibold">{{ $invoice->po_number }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-600">Terms:</span>
                        <span class="text-sm font-medium">Net {{ $invoice->payment_terms ?? 30 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gap between rows -->
    <div class="h-2 bg-gray-50"></div>

    <!-- Line Items Section -->
    <div class="px-4 md:px-6 lg:px-8 py-6 bg-white border border-gray-200 rounded-lg mx-2 md:mx-4 lg:mx-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Invoice Items</h3>

        <!-- Line Items Table - Desktop -->
        <div class="hidden md:block">
            <div class="overflow-hidden rounded-xl border border-gray-200">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 60%;">Description</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 5%;">Qty</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 20%;">Rate</th>
                            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 15%;">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($invoice->items as $item)
                        <tr>
                            <td class="px-6 py-4" style="width: 60%;">
                                <div class="text-sm font-medium text-gray-900">{{ $item->description }}</div>
                                @if($item->specifications)
                                    <div class="text-xs text-gray-600 mt-1">{{ $item->specifications }}</div>
                                @endif
                                @if($item->notes)
                                    <div class="text-xs text-gray-500 mt-1 italic">{{ $item->notes }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm" style="width: 5%;">
                                {{ number_format($item->quantity, 0) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm" style="width: 20%;">
                                RM {{ number_format($item->unit_price, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium" style="width: 15%;">
                                RM {{ number_format($item->total_price ?? ($item->quantity * $item->unit_price), 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Line Items - Mobile -->
        <div class="block md:hidden space-y-4">
            @foreach($invoice->items as $item)
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1 pr-4">
                        <div class="text-sm font-medium text-gray-900">{{ $item->description }}</div>
                        @if($item->specifications)
                            <div class="text-xs text-gray-600 mt-1">{{ $item->specifications }}</div>
                        @endif
                        @if($item->notes)
                            <div class="text-xs text-gray-500 mt-1 italic">{{ $item->notes }}</div>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Qty:</span>
                        <span class="font-medium">{{ number_format($item->quantity, 0) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Rate:</span>
                        <span class="font-medium">RM {{ number_format($item->unit_price, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Total:</span>
                        <span class="font-semibold">RM {{ number_format($item->total_price ?? ($item->quantity * $item->unit_price), 2) }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Gap between rows -->
    <div class="h-2 bg-gray-50"></div>

    <!-- Totals Section -->
    <div class="px-4 md:px-6 lg:px-8 py-6">
        <div class="flex flex-col lg:grid lg:grid-cols-2 gap-4 md:gap-6 mt-4">
            <!-- Left side: Notes/Terms/Payment Instructions -->
            <div class="space-y-6 order-2 lg:order-1">
                <!-- Payment Instructions Card -->
                @if(($settings['optional_sections']['show_payment_instructions'] ?? true) && ($invoice->payment_instructions ?? false))
                <div class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between bg-gray-200 px-5 py-4">
                        <span class="font-medium text-gray-900">Payment Instructions</span>
                    </div>
                    <div class="px-5 py-4">
                        <div class="text-sm text-gray-700 whitespace-pre-line">{{ $invoice->payment_instructions }}</div>
                    </div>
                </div>
                @endif

                <!-- Terms Card -->
                @if($invoice->terms_conditions ?? false)
                <div class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between bg-gray-200 px-5 py-4">
                        <span class="font-medium text-gray-900">Terms & Conditions</span>
                    </div>
                    <div class="px-5 py-4">
                        <div class="text-sm text-gray-700 whitespace-pre-line">{{ $invoice->terms_conditions }}</div>
                    </div>
                </div>
                @endif

                <!-- Notes Card -->
                @if($invoice->notes ?? false)
                <div class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                    <div class="flex items-center justify-between bg-gray-200 px-5 py-4">
                        <span class="font-medium text-gray-900">Notes</span>
                    </div>
                    <div class="px-5 py-4">
                        <div class="text-sm text-gray-700 whitespace-pre-line">{{ $invoice->notes }}</div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right side: Totals Summary -->
            <div class="order-1 lg:order-2">
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 space-y-6">
                    <!-- Top row -->
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-medium">RM {{ number_format($invoice->subtotal ?? 0, 2) }}</span>
                    </div>

                    <!-- Summary lines for applied discounts/taxes -->
                    @if(($invoice->discount_amount ?? 0) > 0)
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Discount:</span>
                        <span>-RM {{ number_format($invoice->discount_amount, 2) }}</span>
                    </div>
                    @endif

                    @if(($invoice->tax_amount ?? 0) > 0)
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Tax:</span>
                        <span>RM {{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>
                    @endif

                    @if(($invoice->round_amount ?? 0) != 0)
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Round Off:</span>
                        <span>RM {{ number_format($invoice->round_amount, 2) }}</span>
                    </div>
                    @endif

                    <!-- Divider line -->
                    <div class="border-b border-gray-200"></div>

                    <!-- Totals block -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-lg font-semibold">
                            <span>Total:</span>
                            <span>RM {{ number_format($invoice->total ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Paid:</span>
                            <span>RM {{ number_format($invoice->amount_paid ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold">
                            <span>Balance Due:</span>
                            <span>RM {{ number_format($invoice->amount_due ?? ($invoice->total ?? 0), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signatures (if enabled) -->
    @if($settings['optional_sections']['show_signatures'] ?? false)
    <div class="px-4 md:px-6 lg:px-8 py-6 border-t border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            <div>
                <div class="h-16 border-t border-gray-400 mt-4">
                    <div class="mt-2 text-sm text-gray-900 text-center font-medium">{{ $invoice->createdBy->name ?? 'Sales Representative' }}</div>
                    <div class="mt-1 text-sm text-gray-600 text-center">Sales Representative</div>
                    <div class="mt-1 text-xs text-gray-500 text-center">{{ $invoice->company->name ?? 'Company Name' }}</div>
                </div>
            </div>
            <div>
                <div class="h-16 border-t border-gray-400 mt-4">
                    <div class="mt-2 text-sm text-gray-900 text-center font-medium">Customer Signature</div>
                    <div class="mt-1 text-sm text-gray-600 text-center">{{ $invoice->customer_name ?? 'Customer Name' }}</div>
                    <div class="mt-1 text-xs text-gray-500 text-center">Date: _______________</div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>