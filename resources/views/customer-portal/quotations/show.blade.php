<x-customer-portal.layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $quotation->number }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $quotation->project_name ?: 'Quotation Details' }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                @if(Auth::guard('customer-portal')->user()->can_download_pdfs)
                    <a href="{{ route('customer-portal.quotations.pdf', $quotation) }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download PDF
                    </a>
                @endif
                <a href="{{ route('customer-portal.quotations.index') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Status and Actions -->
            <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($quotation->status === 'ACCEPTED') bg-green-100 text-green-800
                                @elseif($quotation->status === 'SENT') bg-blue-100 text-blue-800
                                @elseif($quotation->status === 'VIEWED') bg-purple-100 text-purple-800
                                @elseif($quotation->status === 'REJECTED') bg-red-100 text-red-800
                                @elseif($quotation->status === 'EXPIRED') bg-gray-100 text-gray-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ $quotation->status }}
                            </span>
                            <span class="text-sm text-gray-500">
                                Valid until {{ $quotation->valid_until->format('M d, Y') }}
                            </span>
                        </div>

                        <!-- Action Buttons -->
                        @if(in_array($quotation->status, ['SENT', 'VIEWED']))
                            <div class="flex items-center space-x-3">
                                <!-- Accept Button -->
                                <button type="button" 
                                        onclick="openAcceptModal()"
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Accept Quotation
                                </button>
                                
                                <!-- Reject Button -->
                                <button type="button" 
                                        onclick="openRejectModal()"
                                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Reject Quotation
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quotation Details -->
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
                                    <span class="text-gray-900">{{ $quotation->customer_name }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Email:</span>
                                    <span class="text-gray-900">{{ $quotation->customer_email }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Phone:</span>
                                    <span class="text-gray-900">{{ $quotation->customer_phone }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Company:</span>
                                    <span class="text-gray-900">{{ $quotation->customer_company ?: 'N/A' }}</span>
                                </div>
                            </div>
                            @if($quotation->customer_address)
                                <div class="mt-4">
                                    <span class="font-medium text-gray-700">Address:</span>
                                    <p class="text-gray-900 mt-1">{{ $quotation->customer_address }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Items/Services -->
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Items & Services</h3>
                            
                            @if($quotation->sections->count() > 0)
                                <!-- Service Quotation with Sections -->
                                @foreach($quotation->sections as $section)
                                    <div class="mb-6 border border-gray-200 rounded-lg p-4">
                                        <h4 class="font-medium text-gray-900 mb-2">{{ $section->name }}</h4>
                                        @if($section->description)
                                            <p class="text-gray-600 text-sm mb-3">{{ $section->description }}</p>
                                        @endif
                                        
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @foreach($section->items as $item)
                                                        <tr>
                                                            <td class="px-4 py-2">
                                                                <div class="text-sm font-medium text-gray-900">{{ $item->description }}</div>
                                                                @if($item->notes)
                                                                    <div class="text-xs text-gray-500">{{ $item->notes }}</div>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-2 text-center text-sm text-gray-900">{{ $item->quantity }}</td>
                                                            <td class="px-4 py-2 text-right text-sm text-gray-900">RM {{ number_format($item->unit_price, 2) }}</td>
                                                            <td class="px-4 py-2 text-right text-sm font-medium text-gray-900">RM {{ number_format($item->total, 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <div class="mt-3 text-right">
                                            <span class="text-sm font-medium text-gray-900">Section Total: RM {{ number_format($section->subtotal, 2) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <!-- Product Quotation with Direct Items -->
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
                                            @foreach($quotation->items as $item)
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
                            @endif
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
                                    <span class="text-gray-900">RM {{ number_format($quotation->subtotal, 2) }}</span>
                                </div>
                                @if($quotation->discount_amount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Discount:</span>
                                        <span class="text-red-600">-RM {{ number_format($quotation->discount_amount, 2) }}</span>
                                    </div>
                                @endif
                                @if($quotation->tax_amount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Tax:</span>
                                        <span class="text-gray-900">RM {{ number_format($quotation->tax_amount, 2) }}</span>
                                    </div>
                                @endif
                                <hr class="my-2">
                                <div class="flex justify-between font-medium text-lg">
                                    <span class="text-gray-900">Total:</span>
                                    <span class="text-gray-900">RM {{ number_format($quotation->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quotation Info -->
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Information</h3>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Created:</span>
                                    <span class="text-gray-900 block">{{ $quotation->created_at->format('M d, Y g:i A') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Valid Until:</span>
                                    <span class="text-gray-900 block">{{ $quotation->valid_until->format('M d, Y') }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Sales Rep:</span>
                                    <span class="text-gray-900 block">{{ $quotation->createdBy->name }}</span>
                                </div>
                                @if($quotation->description)
                                    <div>
                                        <span class="font-medium text-gray-700">Description:</span>
                                        <p class="text-gray-900 mt-1">{{ $quotation->description }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accept Modal -->
    <div id="acceptModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form method="POST" action="{{ route('customer-portal.quotations.accept', $quotation) }}">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Accept Quotation
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to accept this quotation? This action will notify our team to proceed with your order.
                                    </p>
                                    <div class="mt-4">
                                        <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes (Optional)</label>
                                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm" placeholder="Any special requirements or notes..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Accept Quotation
                        </button>
                        <button type="button" onclick="closeAcceptModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form method="POST" action="{{ route('customer-portal.quotations.reject', $quotation) }}">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Reject Quotation
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Please let us know why you're rejecting this quotation so we can improve our service.
                                    </p>
                                    <div class="mt-4">
                                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Reason for Rejection *</label>
                                        <textarea id="rejection_reason" name="rejection_reason" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Please explain why you're rejecting this quotation..."></textarea>
                                    </div>
                                    <div class="mt-4">
                                        <label for="reject_notes" class="block text-sm font-medium text-gray-700">Additional Notes (Optional)</label>
                                        <textarea id="reject_notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm" placeholder="Any additional feedback..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Reject Quotation
                        </button>
                        <button type="button" onclick="closeRejectModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAcceptModal() {
            document.getElementById('acceptModal').classList.remove('hidden');
        }

        function closeAcceptModal() {
            document.getElementById('acceptModal').classList.add('hidden');
        }

        function openRejectModal() {
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            const acceptModal = document.getElementById('acceptModal');
            const rejectModal = document.getElementById('rejectModal');
            
            if (event.target === acceptModal) {
                closeAcceptModal();
            }
            if (event.target === rejectModal) {
                closeRejectModal();
            }
        });
    </script>
</x-customer-portal.layouts.app>