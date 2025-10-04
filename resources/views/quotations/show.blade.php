@extends('layouts.app')

@section('title', 'Quotation: ' . $quotation->number)

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Quotation') }}: {{ $quotation->number }}
            </h2>
            <p class="text-gray-600 mt-1">
                Created {{ $quotation->created_at->format('M j, Y H:i') }} •
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $quotation->getStatusBadgeColor() }}">
                    {{ $quotation->status }}
                </span>
                @if($quotation->isExpired() && in_array($quotation->status, ['SENT', 'VIEWED']))
                    • <span class="text-red-500 text-sm">Expired</span>
                @endif
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('quotations.index') }}"
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Quotations
                </a>
                @can('update', $quotation)
                    @if($quotation->canBeEdited())
                        <a href="{{ route('quotations.edit', $quotation) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit
                        </a>
                    @endif
                @endcan
                @if($quotation->canBeSent())
                    <form method="POST" action="{{ route('quotations.mark-sent', $quotation) }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Mark as Sent
                        </button>
                    </form>
                @endif
                <!-- PDF Actions -->
                <div class="flex space-x-1">
                    <a href="{{ route('quotations.preview', $quotation) }}" 
                       target="_blank"
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded text-sm">
                        Preview PDF
                    </a>
                    <a href="{{ route('quotations.pdf', $quotation) }}" 
                       class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded text-sm">
                        Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Customer Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Customer Information</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Customer Name</label>
                                    <p class="mt-1 text-gray-900 font-semibold">{{ $quotation->customer_name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Phone</label>
                                    <p class="mt-1 text-gray-900">
                                        <a href="tel:{{ $quotation->customer_phone }}" class="hover:text-blue-600">
                                            {{ $quotation->customer_phone }}
                                        </a>
                                    </p>
                                </div>
                                @if($quotation->customer_email)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Email</label>
                                    <p class="mt-1 text-gray-900">
                                        <a href="mailto:{{ $quotation->customer_email }}" class="hover:text-blue-600">
                                            {{ $quotation->customer_email }}
                                        </a>
                                    </p>
                                </div>
                                @endif
                                @if($quotation->customerSegment)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Customer Segment</label>
                                    <p class="mt-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium"
                                              style="background-color: {{ $quotation->customerSegment->color }}20; color: {{ $quotation->customerSegment->color }};">
                                            <div class="w-2 h-2 rounded-full mr-2" style="background-color: {{ $quotation->customerSegment->color }};"></div>
                                            {{ $quotation->customerSegment->name }}
                                        </span>
                                    </p>
                                </div>
                                @endif
                                @if($quotation->getFormattedCustomerAddress())
                                <div class="md:col-span-2">
                                    <label class="text-sm font-medium text-gray-500">Address</label>
                                    <p class="mt-1 text-gray-900 whitespace-pre-line">{{ $quotation->getFormattedCustomerAddress() }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quotation Details -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Quotation Details</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Title</label>
                                    <p class="mt-1 text-gray-900 font-semibold">{{ $quotation->title }}</p>
                                </div>
                                @if($quotation->description)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Description</label>
                                    <p class="mt-1 text-gray-900 whitespace-pre-wrap">{{ $quotation->description }}</p>
                                </div>
                                @endif
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Type</label>
                                        <p class="mt-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium
                                                {{ $quotation->type === 'product' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ ucfirst($quotation->type) }} Quotation
                                            </span>
                                        </p>
                                    </div>
                                    @if($quotation->valid_until)
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Valid Until</label>
                                        <p class="mt-1 text-gray-900 {{ $quotation->isExpired() ? 'text-red-600 font-semibold' : '' }}">
                                            {{ $quotation->valid_until->format('M j, Y') }}
                                            @if($quotation->isExpired())
                                                <span class="text-red-500 text-sm">(Expired)</span>
                                            @endif
                                        </p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items/Sections -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">
                                @if($quotation->type === 'service' && $quotation->sections->isNotEmpty())
                                    Sections & Items
                                @else
                                    Items
                                @endif
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            @if($quotation->type === 'service' && $quotation->sections->isNotEmpty())
                                <!-- Service Quotation with Sections -->
                                @foreach($quotation->sections as $section)
                                    <div class="border-b border-gray-200 last:border-b-0">
                                        <div class="px-6 py-4 bg-gray-25 border-b border-gray-100">
                                            <h4 class="font-semibold text-gray-900">{{ $section->name }}</h4>
                                            @if($section->description)
                                                <p class="text-sm text-gray-600 mt-1">{{ $section->description }}</p>
                                            @endif
                                        </div>
                                        @if($section->items->isNotEmpty())
                                            <table class="min-w-full">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Unit</th>
                                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @foreach($section->items as $item)
                                                        <tr>
                                                            <td class="px-6 py-4">
                                                                <div class="text-sm text-gray-900">{{ $item->description }}</div>
                                                                @if($item->specifications)
                                                                    <div class="text-xs text-gray-500 mt-1">{{ $item->specifications }}</div>
                                                                @endif
                                                                @if($item->item_code)
                                                                    <div class="text-xs text-gray-400 mt-1">Code: {{ $item->item_code }}</div>
                                                                @endif
                                                            </td>
                                                            <td class="px-6 py-4 text-center text-sm text-gray-900">{{ $item->unit }}</td>
                                                            <td class="px-6 py-4 text-right text-sm text-gray-900">{{ number_format($item->quantity, 2) }}</td>
                                                            <td class="px-6 py-4 text-right text-sm text-gray-900">RM {{ number_format($item->unit_price, 2) }}</td>
                                                            <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">RM {{ number_format($item->total_price, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    <tr class="bg-gray-50">
                                                        <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-900">Section Total:</td>
                                                        <td class="px-6 py-3 text-right text-sm font-bold text-gray-900">RM {{ number_format($section->total, 2) }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <!-- Product Quotation or Service without Sections -->
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($quotation->items as $item)
                                            <tr>
                                                <td class="px-6 py-4">
                                                    <div class="text-sm text-gray-900 font-medium">{{ $item->description }}</div>
                                                    @if($item->specifications)
                                                        <div class="text-xs text-gray-500 mt-1">{{ $item->specifications }}</div>
                                                    @endif
                                                    @if($item->item_code)
                                                        <div class="text-xs text-gray-400 mt-1">Item Code: {{ $item->item_code }}</div>
                                                    @endif
                                                    @if($item->notes)
                                                        <div class="text-xs text-gray-600 mt-1 italic">{{ $item->notes }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-center text-sm text-gray-900">{{ $item->unit }}</td>
                                                <td class="px-6 py-4 text-right text-sm text-gray-900">{{ number_format($item->quantity, 2) }}</td>
                                                <td class="px-6 py-4 text-right text-sm text-gray-900">RM {{ number_format($item->unit_price, 2) }}</td>
                                                <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">RM {{ number_format($item->total_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Financial Summary</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium">RM {{ number_format($quotation->subtotal, 2) }}</span>
                                </div>
                                @if($quotation->discount_amount > 0)
                                    <div class="flex justify-between text-red-600">
                                        <span>Discount ({{ number_format($quotation->discount_percentage, 1) }}%):</span>
                                        <span>-RM {{ number_format($quotation->discount_amount, 2) }}</span>
                                    </div>
                                @endif
                                @if($quotation->tax_amount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Tax ({{ number_format($quotation->tax_percentage, 1) }}%):</span>
                                        <span class="font-medium">RM {{ number_format($quotation->tax_amount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-lg font-bold border-t pt-3">
                                    <span>Total Amount:</span>
                                    <span class="text-green-600">RM {{ number_format($quotation->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    @if($quotation->terms_conditions)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-lg font-semibold text-gray-900">Terms & Conditions</h3>
                            </div>
                            <div class="p-6">
                                <p class="text-gray-900 whitespace-pre-wrap">{{ $quotation->terms_conditions }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Notes -->
                    @if($quotation->notes)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                                <h3 class="text-lg font-semibold text-gray-900">Notes</h3>
                            </div>
                            <div class="p-6">
                                <p class="text-gray-900 whitespace-pre-wrap">{{ $quotation->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Status & Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Status & Actions</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Current Status</label>
                                <p class="mt-1">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $quotation->getStatusBadgeColor() }}">
                                        {{ $quotation->status }}
                                    </span>
                                </p>
                            </div>

                            @if($quotation->view_count > 0)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Views</label>
                                    <p class="mt-1 text-gray-900">{{ $quotation->view_count }} times</p>
                                </div>
                            @endif

                            @if($quotation->sent_at)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Sent At</label>
                                    <p class="mt-1 text-gray-900">{{ $quotation->sent_at->format('M j, Y H:i') }}</p>
                                </div>
                            @endif

                            @if($quotation->viewed_at)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">First Viewed At</label>
                                    <p class="mt-1 text-gray-900">{{ $quotation->viewed_at->format('M j, Y H:i') }}</p>
                                </div>
                            @endif

                            @if($quotation->accepted_at)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Accepted At</label>
                                    <p class="mt-1 text-green-600 font-semibold">{{ $quotation->accepted_at->format('M j, Y H:i') }}</p>
                                </div>
                            @endif

                            @if($quotation->rejected_at)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Rejected At</label>
                                    <p class="mt-1 text-red-600 font-semibold">{{ $quotation->rejected_at->format('M j, Y H:i') }}</p>
                                    @if($quotation->rejection_reason)
                                        <p class="text-sm text-gray-600 mt-1">Reason: {{ $quotation->rejection_reason }}</p>
                                    @endif
                                </div>
                            @endif

                            <!-- Quick Actions -->
                            @can('update', $quotation)
                                <div class="space-y-2">
                                    @if($quotation->canBeAccepted())
                                        <form method="POST" action="{{ route('quotations.mark-accepted', $quotation) }}" class="w-full">
                                            @csrf
                                            <button type="submit" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                                Mark as Accepted
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($quotation->status, ['SENT', 'VIEWED']))
                                        <form method="POST" action="{{ route('quotations.mark-rejected', $quotation) }}" class="w-full">
                                            @csrf
                                            <div class="space-y-2">
                                                <input type="text" name="rejection_reason" placeholder="Reason (optional)" 
                                                       class="w-full text-sm border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md">
                                                <button type="submit" class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                                                    Mark as Rejected
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            @endcan
                        </div>
                    </div>

                    <!-- Assignment Info -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">Assignment</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            @if($quotation->lead)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Related Lead</label>
                                    <p class="mt-1">
                                        @can('view', $quotation->lead)
                                            <a href="{{ route('leads.show', $quotation->lead) }}" class="text-blue-600 hover:text-blue-800">
                                                View Lead Details
                                            </a>
                                        @else
                                            <span class="text-gray-900">Lead exists</span>
                                        @endcan
                                    </p>
                                </div>
                            @endif

                            <div>
                                <label class="text-sm font-medium text-gray-500">Team</label>
                                <p class="mt-1 text-gray-900">
                                    {{ $quotation->team ? $quotation->team->name : 'No Team' }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Assigned To</label>
                                <p class="mt-1 text-gray-900">
                                    {{ $quotation->assignedTo ? $quotation->assignedTo->name : 'Unassigned' }}
                                </p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Created By</label>
                                <p class="mt-1 text-gray-900">{{ $quotation->createdBy->name }}</p>
                            </div>
                        </div>
                    </div>

                    @if($quotation->canBeConverted())
                        <!-- Conversion Actions -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm font-medium text-green-800">Ready to Convert</span>
                            </div>
                            <p class="text-sm text-green-700 mb-3">This quotation has been accepted and can be converted to an invoice.</p>
                            @can('convert', $quotation)
                                <a href="{{ route('quotations.convert', $quotation) }}"
                                   onclick="return confirm('Are you sure you want to convert this quotation to an invoice? This action cannot be undone.')"
                                   class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm text-center transition-colors">
                                    Convert to Invoice
                                </a>
                            @endcan
                        </div>
                    @endif

                    <!-- Internal Notes -->
                    @if($quotation->internal_notes)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-yellow-800 mb-2">Internal Notes</h4>
                            <p class="text-sm text-yellow-700">{{ $quotation->internal_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection