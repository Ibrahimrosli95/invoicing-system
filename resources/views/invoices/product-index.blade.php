@extends('layouts.app')

@section('title', 'Product Invoices')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Product Invoices') }}
        </h2>
        <div class="flex space-x-2">
            @can('create', App\Models\Invoice::class)
                <a href="{{ route('invoices.builder') }}"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create Product Invoice
                </a>
            @endcan
        </div>
    </div>
</div>
@endsection

@section('content')

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            
            <!-- Summary Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="text-sm font-medium text-gray-500">Total Invoices</div>
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_invoices']) }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="text-sm font-medium text-gray-500">Total Amount</div>
                        <div class="text-2xl font-bold text-green-600">RM {{ number_format($stats['total_amount'], 2) }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="text-sm font-medium text-gray-500">Paid Amount</div>
                        <div class="text-2xl font-bold text-blue-600">RM {{ number_format($stats['paid_amount'], 2) }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="text-sm font-medium text-gray-500">Outstanding</div>
                        <div class="text-2xl font-bold text-orange-600">RM {{ number_format($stats['outstanding_amount'], 2) }}</div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="text-sm font-medium text-gray-500">Overdue</div>
                        <div class="text-2xl font-bold text-red-600">{{ number_format($stats['overdue_count']) }}</div>
                    </div>
                </div>
            </div>

            <!-- Aging Buckets -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Invoice Aging Report</h3>
                    <p class="text-sm text-gray-600">Outstanding invoices grouped by days overdue</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Current (Not Due) -->
                        <div class="text-center">
                            <div class="bg-green-100 text-green-800 px-4 py-6 rounded-lg border-2 border-green-200">
                                <div class="text-sm font-medium mb-2">Current</div>
                                <div class="text-2xl font-bold">{{ $agingStats['current']['count'] }}</div>
                                <div class="text-xs text-green-600 mt-1">
                                    RM {{ number_format($agingStats['current']['amount'], 2) }}
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Not due yet</p>
                        </div>

                        <!-- 1-30 Days -->
                        <div class="text-center">
                            <div class="bg-yellow-100 text-yellow-800 px-4 py-6 rounded-lg border-2 border-yellow-200">
                                <div class="text-sm font-medium mb-2">1-30 Days</div>
                                <div class="text-2xl font-bold">{{ $agingStats['0-30']['count'] }}</div>
                                <div class="text-xs text-yellow-600 mt-1">
                                    RM {{ number_format($agingStats['0-30']['amount'], 2) }}
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Recently overdue</p>
                        </div>

                        <!-- 31-60 Days -->
                        <div class="text-center">
                            <div class="bg-orange-100 text-orange-800 px-4 py-6 rounded-lg border-2 border-orange-200">
                                <div class="text-sm font-medium mb-2">31-60 Days</div>
                                <div class="text-2xl font-bold">{{ $agingStats['31-60']['count'] }}</div>
                                <div class="text-xs text-orange-600 mt-1">
                                    RM {{ number_format($agingStats['31-60']['amount'], 2) }}
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Follow-up needed</p>
                        </div>

                        <!-- 61-90 Days -->
                        <div class="text-center">
                            <div class="bg-red-100 text-red-800 px-4 py-6 rounded-lg border-2 border-red-200">
                                <div class="text-sm font-medium mb-2">61-90 Days</div>
                                <div class="text-2xl font-bold">{{ $agingStats['61-90']['count'] }}</div>
                                <div class="text-xs text-red-600 mt-1">
                                    RM {{ number_format($agingStats['61-90']['amount'], 2) }}
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Urgent action</p>
                        </div>

                        <!-- 90+ Days -->
                        <div class="text-center">
                            <div class="bg-red-200 text-red-900 px-4 py-6 rounded-lg border-2 border-red-300">
                                <div class="text-sm font-medium mb-2">90+ Days</div>
                                <div class="text-2xl font-bold">{{ $agingStats['90+']['count'] }}</div>
                                <div class="text-xs text-red-700 mt-1">
                                    RM {{ number_format($agingStats['90+']['amount'], 2) }}
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Collections risk</p>
                        </div>
                    </div>

                    <!-- Aging Chart or Progress Bar (Optional Enhancement) -->
                    @php
                        $totalOutstanding = array_sum(array_column($agingStats, 'amount'));
                    @endphp
                    @if($totalOutstanding > 0)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Outstanding Amount Distribution</h4>
                            <div class="flex rounded-lg overflow-hidden h-4">
                                @foreach($agingStats as $bucket => $data)
                                    @php
                                        $percentage = $totalOutstanding > 0 ? ($data['amount'] / $totalOutstanding) * 100 : 0;
                                        $color = match($bucket) {
                                            'current' => 'bg-green-500',
                                            '0-30' => 'bg-yellow-500',
                                            '31-60' => 'bg-orange-500',
                                            '61-90' => 'bg-red-500',
                                            '90+' => 'bg-red-700',
                                            default => 'bg-gray-500',
                                        };
                                    @endphp
                                    @if($percentage > 0)
                                        <div class="{{ $color }}" 
                                             style="width: {{ $percentage }}%"
                                             title="{{ ucfirst($bucket) }}: {{ number_format($percentage, 1) }}%">
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-2">
                                <span>Low Risk</span>
                                <span>High Risk</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('invoices.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-7 gap-4">
                            <!-- Search -->
                            <div class="md:col-span-2">
                                <x-text-input 
                                    name="search" 
                                    type="text" 
                                    placeholder="Search invoices..."
                                    :value="request('search')"
                                    class="w-full" />
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <select name="status" 
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">All Status</option>
                                    @foreach($filters['statuses'] as $value => $label)
                                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Team Filter -->
                            <div>
                                <select name="team_id" 
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">All Teams</option>
                                    @foreach($filters['teams'] as $team)
                                        <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>
                                            {{ $team->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Assignee Filter -->
                            <div>
                                <select name="assigned_to" 
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">All Assignees</option>
                                    @foreach($filters['assignees'] as $assignee)
                                        <option value="{{ $assignee->id }}" {{ request('assigned_to') == $assignee->id ? 'selected' : '' }}>
                                            {{ $assignee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Quick Filters -->
                            <div class="flex space-x-2">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="overdue_only" value="1" 
                                           {{ request('overdue_only') ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-600">Overdue</span>
                                </label>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <button type="submit" 
                                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Filter
                                </button>
                                <a href="{{ route('invoices.index') }}" 
                                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                    Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ route('invoices.index', array_merge(request()->all(), ['sort' => 'number', 'direction' => request('sort') === 'number' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                                       class="flex items-center hover:text-gray-700">
                                        Invoice Number
                                        @if(request('sort') === 'number')
                                            @if(request('direction') === 'asc')
                                                <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ route('invoices.index', array_merge(request()->all(), ['sort' => 'total_amount', 'direction' => request('sort') === 'total_amount' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                                       class="flex items-center hover:text-gray-700">
                                        Total Amount
                                        @if(request('sort') === 'total_amount')
                                            @if(request('direction') === 'asc')
                                                <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount Due
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Assigned To
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ route('invoices.index', array_merge(request()->all(), ['sort' => 'due_date', 'direction' => request('sort') === 'due_date' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                                       class="flex items-center hover:text-gray-700">
                                        Due Date
                                        @if(request('sort') === 'due_date')
                                            @if(request('direction') === 'asc')
                                                <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                </svg>
                                            @else
                                                <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            @endif
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($invoices as $invoice)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">
                                                <a href="{{ route('invoices.show', $invoice) }}" 
                                                   class="text-blue-600 hover:text-blue-900">
                                                    {{ $invoice->number }}
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $invoice->customer_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $invoice->customer_phone }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                                        @if(!$invoice->isPaid() && !$invoice->isCancelled())
                                            <div class="mt-1">
                                                <span class="px-2 inline-flex text-xs leading-4 font-medium rounded-full {{ $invoice->getAgingBucketColor() }}">
                                                    {{ $invoice->getAgingBucketName() }}
                                                </span>
                                            </div>
                                            @if($invoice->isOverdue())
                                                <div class="text-xs text-red-600 mt-1">
                                                    {{ abs($invoice->getDaysOverdue()) }} days overdue
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        RM {{ number_format($invoice->total_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">RM {{ number_format($invoice->amount_due, 2) }}</div>
                                        @if($invoice->amount_paid > 0)
                                            <div class="text-xs text-green-600">
                                                Paid: RM {{ number_format($invoice->amount_paid, 2) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($invoice->assignedTo)
                                            <div class="text-sm text-gray-900">
                                                {{ $invoice->assignedTo->name }}
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">Unassigned</span>
                                        @endif
                                        @if($invoice->team)
                                            <div class="text-xs text-gray-500">
                                                {{ $invoice->team->name }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @displayDate($invoice->due_date)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        @can('view', $invoice)
                                            <a href="{{ route('invoices.show', $invoice) }}" 
                                               class="text-blue-600 hover:text-blue-900">View</a>
                                        @endcan
                                        
                                        @if($invoice->status !== 'PAID' && $invoice->status !== 'CANCELLED')
                                            @can('recordPayment', $invoice)
                                                <a href="{{ route('invoices.payment-form', $invoice) }}" 
                                                   class="text-green-600 hover:text-green-900">Record Payment</a>
                                            @endcan
                                        @endif

                                        @can('view', $invoice)
                                            <a href="{{ route('invoices.pdf', $invoice) }}" 
                                               class="text-purple-600 hover:text-purple-900" target="_blank">PDF</a>
                                        @endcan

                                        @can('update', $invoice)
                                            @if($invoice->canBeEdited())
                                                <a href="{{ route('invoices.edit', $invoice) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No invoices found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($invoices->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $invoices->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection