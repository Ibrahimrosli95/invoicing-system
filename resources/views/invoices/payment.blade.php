<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Record Payment - Invoice {{ $invoice->number }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Invoice Summary -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-500">Invoice Number</div>
                                <div class="text-lg font-bold text-gray-900">{{ $invoice->number }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Customer</div>
                                <div class="text-lg text-gray-900">{{ $invoice->customer_name }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Amount Due</div>
                                <div class="text-2xl font-bold text-red-600">RM {{ number_format($invoice->amount_due, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form method="POST" action="{{ route('invoices.record-payment', $invoice) }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Payment Amount -->
                            <div>
                                <x-input-label for="amount" :value="__('Payment Amount')" />
                                <x-text-input id="amount" 
                                              name="amount" 
                                              type="number" 
                                              step="0.01"
                                              min="0.01"
                                              max="{{ $invoice->amount_due }}"
                                              class="mt-1 block w-full" 
                                              :value="old('amount', $invoice->amount_due)"
                                              required />
                                <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                                <p class="mt-1 text-sm text-gray-500">Maximum: RM {{ number_format($invoice->amount_due, 2) }}</p>
                            </div>

                            <!-- Payment Method -->
                            <div>
                                <x-input-label for="payment_method" :value="__('Payment Method')" />
                                <select id="payment_method" 
                                        name="payment_method" 
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                        required>
                                    <option value="">Select Payment Method</option>
                                    <option value="CASH" {{ old('payment_method') == 'CASH' ? 'selected' : '' }}>Cash</option>
                                    <option value="CHEQUE" {{ old('payment_method') == 'CHEQUE' ? 'selected' : '' }}>Cheque</option>
                                    <option value="BANK_TRANSFER" {{ old('payment_method') == 'BANK_TRANSFER' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="CREDIT_CARD" {{ old('payment_method') == 'CREDIT_CARD' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="ONLINE_BANKING" {{ old('payment_method') == 'ONLINE_BANKING' ? 'selected' : '' }}>Online Banking</option>
                                    <option value="OTHER" {{ old('payment_method') == 'OTHER' ? 'selected' : '' }}>Other</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('payment_method')" />
                            </div>

                            <!-- Payment Date -->
                            <div>
                                <x-input-label for="payment_date" :value="__('Payment Date')" />
                                <x-text-input id="payment_date" 
                                              name="payment_date" 
                                              type="date" 
                                              class="mt-1 block w-full" 
                                              :value="old('payment_date', now()->format('Y-m-d'))"
                                              required />
                                <x-input-error class="mt-2" :messages="$errors->get('payment_date')" />
                            </div>

                            <!-- Reference Number -->
                            <div>
                                <x-input-label for="reference_number" :value="__('Reference Number')" />
                                <x-text-input id="reference_number" 
                                              name="reference_number" 
                                              type="text" 
                                              class="mt-1 block w-full" 
                                              :value="old('reference_number')"
                                              placeholder="e.g., Cheque number, transaction ID" />
                                <x-input-error class="mt-2" :messages="$errors->get('reference_number')" />
                                <p class="mt-1 text-sm text-gray-500">Optional - for tracking purposes</p>
                            </div>

                            <!-- Clearance Date -->
                            <div>
                                <x-input-label for="clearance_date" :value="__('Clearance Date')" />
                                <x-text-input id="clearance_date" 
                                              name="clearance_date" 
                                              type="date" 
                                              class="mt-1 block w-full" 
                                              :value="old('clearance_date')" />
                                <x-input-error class="mt-2" :messages="$errors->get('clearance_date')" />
                                <p class="mt-1 text-sm text-gray-500">Leave blank for cash payments or immediate clearing</p>
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Notes')" />
                                <textarea id="notes" 
                                          name="notes" 
                                          rows="3"
                                          class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                          placeholder="Additional notes about this payment...">{{ old('notes') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end mt-6 space-x-4">
                            <a href="{{ route('invoices.show', $invoice) }}" 
                               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Record Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing Payments -->
            @if($invoice->paymentRecords->count() > 0)
                <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Previous Payments</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($invoice->paymentRecords as $payment)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $payment->payment_date->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $payment->payment_method }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                RM {{ number_format($payment->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $payment->reference_number ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                    {{ $payment->status === 'CLEARED' ? 'bg-green-100 text-green-800' : 
                                                       ($payment->status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ $payment->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                                                {{ $payment->receipt_number ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethodSelect = document.getElementById('payment_method');
            const clearanceDateInput = document.getElementById('clearance_date');
            const paymentDateInput = document.getElementById('payment_date');
            
            paymentMethodSelect.addEventListener('change', function() {
                if (this.value === 'CASH' || this.value === 'CREDIT_CARD') {
                    // For cash and credit card, set clearance date to payment date
                    clearanceDateInput.value = paymentDateInput.value;
                }
            });
            
            paymentDateInput.addEventListener('change', function() {
                const paymentMethod = paymentMethodSelect.value;
                if (paymentMethod === 'CASH' || paymentMethod === 'CREDIT_CARD') {
                    clearanceDateInput.value = this.value;
                }
            });
        });
    </script>
</x-app-layout>