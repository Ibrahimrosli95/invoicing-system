@extends('layouts.app')

@section('title', 'Financial Dashboard')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 text-white rounded-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-3xl font-bold">Financial Dashboard</h1>
                    <p class="text-emerald-100 mt-2">Monitor cash flow, collections, and financial health in real-time.</p>
                </div>
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                    <button class="bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white px-6 py-2 rounded-xl text-sm font-medium shadow-lg transition-all duration-200 border border-white/20">
                        Export Financial Report
                    </button>
                    <button class="bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white px-6 py-2 rounded-xl text-sm font-medium shadow-lg transition-all duration-200 border border-white/20">
                        Aging Analysis
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')

    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-emerald-50 to-teal-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Financial Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
                <!-- Total Revenue -->
                <div class="group bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-emerald-400/20 to-emerald-600/20 rounded-bl-3xl"></div>
                        <div class="relative">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm font-medium text-gray-600">Total Revenue</div>
                                    <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">RM {{ number_format($financial_metrics['total_revenue'], 0) }}</div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="text-sm">
                                    @if($financial_metrics['revenue_growth'] > 0)
                                        <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                            ↗ +{{ number_format($financial_metrics['revenue_growth'], 1) }}% vs last month
                                        </span>
                                    @elseif($financial_metrics['revenue_growth'] < 0)
                                        <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                            ↘ {{ number_format($financial_metrics['revenue_growth'], 1) }}% vs last month
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">
                                            → No change vs last month
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Outstanding Invoices -->
                <div class="group bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-yellow-400/20 to-yellow-600/20 rounded-bl-3xl"></div>
                        <div class="relative">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm font-medium text-gray-600">Outstanding</div>
                                    <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">RM {{ number_format($financial_metrics['outstanding_amount'], 0) }}</div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="text-sm">
                                    <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">
                                        {{ $financial_metrics['outstanding_count'] }} invoices pending
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overdue Amount -->
                <div class="group bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-red-400/20 to-red-600/20 rounded-bl-3xl"></div>
                        <div class="relative">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-br from-red-400 to-red-600 rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm font-medium text-gray-600">Overdue</div>
                                    <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">RM {{ number_format($financial_metrics['overdue_amount'], 0) }}</div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="text-sm">
                                    @if($financial_metrics['overdue_count'] > 0)
                                        <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                            {{ $financial_metrics['overdue_count'] }} invoices overdue
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                            All current
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Collection Rate -->
                <div class="group bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-400/20 to-blue-600/20 rounded-bl-3xl"></div>
                        <div class="relative">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm font-medium text-gray-600">Collection Rate</div>
                                    <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">{{ number_format($financial_metrics['collection_rate'], 1) }}%</div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="text-sm">
                                    <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                        {{ $financial_metrics['avg_payment_days'] }} days average
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
                <!-- Revenue Trends -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-emerald-50 to-teal-50 border-b border-white/50">
                        <h3 class="text-xl font-bold text-gray-900">Revenue & Collection Trends</h3>
                        <p class="text-sm text-gray-600 mt-1">Track incoming revenue and payment collection patterns</p>
                    </div>
                    <div class="p-6">
                        <div class="h-72 flex items-center justify-center">
                            <canvas id="revenueChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Invoice Aging Analysis -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-cyan-50 to-blue-50 border-b border-white/50">
                        <h3 class="text-xl font-bold text-gray-900">Invoice Aging Analysis</h3>
                        <p class="text-sm text-gray-600 mt-1">Monitor aging buckets and collection risks</p>
                    </div>
                    <div class="p-6">
                        <div class="h-72 flex items-center justify-center">
                            <canvas id="agingChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Aging Details -->
            <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50 mb-8">
                <div class="px-6 py-5 bg-gradient-to-r from-gray-50 to-slate-50 border-b border-white/50">
                    <h3 class="text-xl font-bold text-gray-900">Invoice Aging Report</h3>
                    <p class="text-sm text-gray-600 mt-1">Detailed breakdown of invoice aging and risk assessment</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Age Range
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Count
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Total Amount
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Percentage
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Risk Level
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white/70 divide-y divide-gray-200">
                            @foreach($aging_report as $range)
                            <tr class="hover:bg-white/90 transition-all duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ $range['label'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    {{ $range['count'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    RM {{ number_format($range['amount'], 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center space-x-3">
                                        <span class="font-bold">{{ number_format($range['percentage'], 1) }}%</span>
                                        <div class="flex-1 w-20 bg-gray-200 rounded-full h-3 shadow-inner">
                                            <div class="h-3 rounded-full transition-all duration-500
                                                @if($range['risk_level'] == 'LOW') bg-gradient-to-r from-green-400 to-green-600
                                                @elseif($range['risk_level'] == 'MEDIUM') bg-gradient-to-r from-yellow-400 to-yellow-600
                                                @else bg-gradient-to-r from-red-400 to-red-600
                                                @endif"
                                                 style="width: {{ $range['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-bold
                                        @if($range['risk_level'] == 'LOW') bg-green-100 text-green-800 border border-green-200
                                        @elseif($range['risk_level'] == 'MEDIUM') bg-yellow-100 text-yellow-800 border border-yellow-200
                                        @else bg-red-100 text-red-800 border border-red-200
                                        @endif">
                                        {{ $range['risk_level'] }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Critical Financial Items -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Overdue Invoices Requiring Attention -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-red-50 to-pink-50 border-b border-white/50 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Critical Overdue Invoices</h3>
                            <p class="text-sm text-gray-600 mt-1">Invoices requiring immediate attention</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-bold bg-red-100 text-red-800 border border-red-200">
                            {{ count($critical_overdue) }} invoices
                        </span>
                    </div>
                    <div class="p-6">
                        @if(count($critical_overdue) > 0)
                            <div class="space-y-4">
                                @foreach($critical_overdue as $invoice)
                                <div class="border border-red-200 rounded-xl p-4 bg-gradient-to-r from-red-50 to-pink-50 hover:shadow-lg transition-all duration-200">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="text-sm font-bold text-gray-900">{{ $invoice['number'] }}</h4>
                                            <p class="text-sm text-gray-700 font-medium">{{ $invoice['customer_name'] }}</p>
                                            <p class="text-xs text-gray-600">Due: {{ $invoice['due_date'] }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-sm font-bold text-gray-900">RM {{ number_format($invoice['total'], 0) }}</span>
                                            <div class="text-xs text-red-600 font-bold">{{ $invoice['days_overdue'] }} days overdue</div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                            {{ $invoice['status'] }}
                                        </span>
                                        <div class="flex space-x-3">
                                            <button class="text-blue-600 hover:text-blue-800 text-xs font-bold transition-colors">
                                                Send Reminder
                                            </button>
                                            <button class="text-green-600 hover:text-green-800 text-xs font-bold transition-colors">
                                                Record Payment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Excellent!</h3>
                                <p class="text-sm text-gray-600 mt-1">No critical overdue invoices to worry about.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Top Customers by Revenue -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-yellow-50 to-orange-50 border-b border-white/50">
                        <h3 class="text-xl font-bold text-gray-900">Top Customers by Revenue</h3>
                        <p class="text-sm text-gray-600 mt-1">Your highest value customers and their payment patterns</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($top_customers as $index => $customer)
                            <div class="flex items-center justify-between py-3 px-4 rounded-xl border border-gray-200 bg-white/70 hover:bg-white/90 transition-all duration-200">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-2xl font-bold text-sm shadow-lg
                                            @if($index == 0) bg-gradient-to-br from-yellow-400 to-yellow-600 text-white
                                            @elseif($index == 1) bg-gradient-to-br from-gray-300 to-gray-500 text-white
                                            @elseif($index == 2) bg-gradient-to-br from-orange-400 to-orange-600 text-white
                                            @else bg-gradient-to-br from-blue-400 to-blue-600 text-white
                                            @endif">
                                            #{{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-gray-900 truncate">
                                            {{ $customer['name'] }}
                                        </p>
                                        <p class="text-xs text-gray-600 font-medium">
                                            {{ $customer['invoice_count'] }} invoices
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">
                                        RM {{ number_format($customer['total_revenue'], 0) }}
                                    </p>
                                    <p class="text-xs text-gray-600 font-medium">
                                        {{ number_format($customer['avg_payment_days'], 0) }}d avg payment
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Method Analysis & Recent Payments -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Payment Methods Distribution -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-purple-50 to-indigo-50 border-b border-white/50">
                        <h3 class="text-xl font-bold text-gray-900">Payment Methods Distribution</h3>
                        <p class="text-sm text-gray-600 mt-1">Preferred payment channels and trends</p>
                    </div>
                    <div class="p-6">
                        <div class="h-72 flex items-center justify-center">
                            <canvas id="paymentMethodChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-white/50">
                        <h3 class="text-xl font-bold text-gray-900">Recent Payments</h3>
                        <p class="text-sm text-gray-600 mt-1">Latest payment transactions and collections</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($recent_payments as $payment)
                            <div class="flex items-center justify-between py-3 px-4 border border-gray-200 rounded-xl bg-white/70 hover:bg-white/90 transition-all duration-200">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center h-10 w-10 rounded-2xl bg-gradient-to-br from-green-400 to-green-600 shadow-lg">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-bold text-gray-900">
                                            {{ $payment['invoice_number'] }}
                                        </p>
                                        <p class="text-xs text-gray-600 font-medium">
                                            {{ $payment['customer_name'] }} • {{ $payment['method'] }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">
                                        RM {{ number_format($payment['amount'], 0) }}
                                    </p>
                                    <p class="text-xs text-gray-600 font-medium">
                                        {{ $payment['date'] }}
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Revenue Trends Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenue_trends['labels']) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode($revenue_trends['revenue']) !!},
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    yAxisID: 'y'
                }, {
                    label: 'Collections',
                    data: {!! json_encode($revenue_trends['collections']) !!},
                    borderColor: '#6366F1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    yAxisID: 'y'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Amount (RM)'
                        }
                    }
                }
            }
        });

        // Invoice Aging Chart
        const agingCtx = document.getElementById('agingChart').getContext('2d');
        new Chart(agingCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($aging_report, 'label')) !!},
                datasets: [{
                    label: 'Amount (RM)',
                    data: {!! json_encode(array_column($aging_report, 'amount')) !!},
                    backgroundColor: [
                        '#10B981', // Current - Green
                        '#F59E0B', // 1-30 - Yellow
                        '#EF4444', // 31-60 - Red
                        '#7C2D12', // 60+ - Dark Red
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (RM)'
                        }
                    }
                }
            }
        });

        // Payment Methods Chart
        const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
        new Chart(paymentMethodCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($payment_methods)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($payment_methods)) !!},
                    backgroundColor: [
                        '#3B82F6', // Bank Transfer - Blue
                        '#10B981', // Cash - Green
                        '#F59E0B', // Cheque - Yellow
                        '#8B5CF6', // Credit Card - Purple
                        '#6B7280'  // Others - Gray
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
@endsection