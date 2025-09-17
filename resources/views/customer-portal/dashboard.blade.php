<x-customer-portal.layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium">Welcome back, {{ $user->name }}!</h3>
                    <p class="text-gray-600 mt-1">Here's an overview of your account</p>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total Quotations -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Quotations</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_quotations'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Invoices -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Invoices</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_invoices'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Outstanding Balance -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Outstanding Balance</p>
                                <p class="text-2xl font-semibold text-gray-900">RM {{ number_format($stats['outstanding_balance'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Paid -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Paid</p>
                                <p class="text-2xl font-semibold text-gray-900">RM {{ number_format($stats['total_paid'], 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Recent Quotations -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Recent Quotations</h3>
                            <a href="{{ route('customer-portal.quotations.index') }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                View All
                            </a>
                        </div>
                        
                        @if($quotations->count() > 0)
                            <div class="space-y-3">
                                @foreach($quotations as $quotation)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $quotation->number }}</p>
                                            <p class="text-sm text-gray-600">{{ $quotation->project_name ?: 'No project name' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($quotation->status === 'ACCEPTED') bg-green-100 text-green-800
                                                @elseif($quotation->status === 'SENT') bg-blue-100 text-blue-800
                                                @elseif($quotation->status === 'REJECTED') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ $quotation->status }}
                                            </span>
                                            <p class="text-sm text-gray-600 mt-1">RM {{ number_format($quotation->total, 2) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">No quotations found</p>
                        @endif
                    </div>
                </div>

                <!-- Recent Invoices -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Recent Invoices</h3>
                            <a href="{{ route('customer-portal.invoices.index') }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                View All
                            </a>
                        </div>
                        
                        @if($invoices->count() > 0)
                            <div class="space-y-3">
                                @foreach($invoices as $invoice)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $invoice->number }}</p>
                                            <p class="text-sm text-gray-600">Due: {{ $invoice->due_date->format('M d, Y') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($invoice->status === 'PAID') bg-green-100 text-green-800
                                                @elseif($invoice->status === 'OVERDUE') bg-red-100 text-red-800
                                                @elseif($invoice->status === 'PARTIAL') bg-yellow-100 text-yellow-800
                                                @else bg-blue-100 text-blue-800
                                                @endif">
                                                {{ $invoice->status }}
                                            </span>
                                            <p class="text-sm text-gray-600 mt-1">RM {{ number_format($invoice->outstanding_amount, 2) }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">No invoices found</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Overdue Alert -->
            @if($stats['overdue_invoices'] > 0)
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                You have {{ $stats['overdue_invoices'] }} overdue invoice{{ $stats['overdue_invoices'] > 1 ? 's' : '' }}
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>Please review your outstanding invoices to avoid late fees.</p>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('customer-portal.invoices.index', ['status' => 'OVERDUE']) }}" 
                                   class="bg-red-100 text-red-800 hover:bg-red-200 px-3 py-2 rounded-md text-sm font-medium">
                                    View Overdue Invoices
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-customer-portal.layouts.app>