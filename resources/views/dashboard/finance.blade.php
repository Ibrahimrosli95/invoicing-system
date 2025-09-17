<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Financial Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Financial Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Revenue -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-sm font-medium text-gray-500">Total Revenue</div>
                                <div class="text-2xl font-bold text-gray-900">RM {{ number_format($financial_metrics['total_revenue'], 2) }}</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    @if($financial_metrics['revenue_growth'] > 0)
                                        <span class="text-green-600">↗ +{{ number_format($financial_metrics['revenue_growth'], 1) }}%</span>
                                    @elseif($financial_metrics['revenue_growth'] < 0)
                                        <span class="text-red-600">↘ {{ number_format($financial_metrics['revenue_growth'], 1) }}%</span>
                                    @else
                                        <span class="text-gray-500">→ 0%</span>
                                    @endif
                                    vs last month
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Outstanding Invoices -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-sm font-medium text-gray-500">Outstanding</div>
                                <div class="text-2xl font-bold text-gray-900">RM {{ number_format($financial_metrics['outstanding_amount'], 2) }}</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $financial_metrics['outstanding_count'] }} invoices
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overdue Amount -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-sm font-medium text-gray-500">Overdue</div>
                                <div class="text-2xl font-bold text-gray-900">RM {{ number_format($financial_metrics['overdue_amount'], 2) }}</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $financial_metrics['overdue_count'] }} invoices
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Collection Rate -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-sm font-medium text-gray-500">Collection Rate</div>
                                <div class="text-2xl font-bold text-gray-900">{{ number_format($financial_metrics['collection_rate'], 1) }}%</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Avg: {{ $financial_metrics['avg_payment_days'] }} days
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Revenue Trends -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue & Collection Trends</h3>
                        <canvas id="revenueChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Invoice Aging Analysis -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Invoice Aging Analysis</h3>
                        <canvas id="agingChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Invoice Aging Details -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Invoice Aging Report</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Age Range
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Count
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Percentage
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Risk Level
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($aging_report as $range)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $range['label'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $range['count'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    RM {{ number_format($range['amount'], 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <span class="font-medium">{{ number_format($range['percentage'], 1) }}%</span>
                                        <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full transition-all duration-300
                                                @if($range['risk_level'] == 'LOW') bg-green-500
                                                @elseif($range['risk_level'] == 'MEDIUM') bg-yellow-500
                                                @else bg-red-500
                                                @endif" 
                                                 style="width: {{ $range['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($range['risk_level'] == 'LOW') bg-green-100 text-green-800
                                        @elseif($range['risk_level'] == 'MEDIUM') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800
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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Overdue Invoices Requiring Attention -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Critical Overdue Invoices</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ count($critical_overdue) }} invoices
                        </span>
                    </div>
                    <div class="p-6">
                        @if(count($critical_overdue) > 0)
                            <div class="space-y-4">
                                @foreach($critical_overdue as $invoice)
                                <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">{{ $invoice['number'] }}</h4>
                                            <p class="text-sm text-gray-600">{{ $invoice['customer_name'] }}</p>
                                            <p class="text-xs text-gray-500">Due: {{ $invoice['due_date'] }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-sm font-medium text-gray-900">RM {{ number_format($invoice['total'], 2) }}</span>
                                            <div class="text-xs text-red-600 font-medium">{{ $invoice['days_overdue'] }} days overdue</div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ $invoice['status'] }}
                                        </span>
                                        <div class="flex space-x-2">
                                            <button class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                                Send Reminder
                                            </button>
                                            <button class="text-green-600 hover:text-green-800 text-xs font-medium">
                                                Record Payment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Great!</h3>
                                <p class="mt-1 text-sm text-gray-500">No critical overdue invoices.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Top Customers by Revenue -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Top Customers by Revenue</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($top_customers as $index => $customer)
                            <div class="flex items-center justify-between py-2">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full 
                                            @if($index == 0) bg-yellow-100 text-yellow-800
                                            @elseif($index == 1) bg-gray-100 text-gray-800
                                            @elseif($index == 2) bg-orange-100 text-orange-800
                                            @else bg-blue-100 text-blue-800
                                            @endif">
                                            <span class="text-sm font-medium">#{{ $index + 1 }}</span>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $customer['name'] }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $customer['invoice_count'] }} invoices
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">
                                        RM {{ number_format($customer['total_revenue'], 0) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Payment Methods Distribution -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Methods Distribution</h3>
                        <canvas id="paymentMethodChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Payments</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($recent_payments as $payment)
                            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-green-100">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $payment['invoice_number'] }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $payment['customer_name'] }} • {{ $payment['method'] }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">
                                        RM {{ number_format($payment['amount'], 2) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
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
</x-app-layout>