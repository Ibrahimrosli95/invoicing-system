<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Executive Dashboard') }}
            </h2>
            <div class="flex space-x-3">
                <select class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <option>Last 30 Days</option>
                    <option>Last 3 Months</option>
                    <option>Last 6 Months</option>
                    <option>This Year</option>
                </select>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Export Report
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Monthly Revenue -->
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
                                <div class="text-sm font-medium text-gray-500">Monthly Revenue</div>
                                <div class="text-2xl font-bold text-gray-900">RM {{ number_format($metrics['monthly_revenue'], 2) }}</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-sm text-gray-600">
                                <span class="text-green-600 font-medium">+12.5%</span> from last month
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Outstanding Amount -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-sm font-medium text-gray-500">Outstanding</div>
                                <div class="text-2xl font-bold text-gray-900">RM {{ number_format($metrics['outstanding_amount'], 2) }}</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-sm text-gray-600">
                                {{ $metrics['overdue_invoices'] }} overdue invoices
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Leads -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-sm font-medium text-gray-500">Active Leads</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $metrics['active_leads'] }}</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-sm text-gray-600">
                                {{ $metrics['pending_quotations'] }} pending quotations
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conversion Rate -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-sm font-medium text-gray-500">Conversion Rate</div>
                                <div class="text-2xl font-bold text-gray-900">{{ $metrics['lead_conversion_rate'] }}%</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="text-sm text-gray-600">
                                Lead → Quotation
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Revenue Trend Chart -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Monthly Revenue Trend</h3>
                    </div>
                    <div class="p-6">
                        <div class="h-64 flex items-center justify-center">
                            <canvas id="revenueTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Conversion Funnel -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Sales Conversion Funnel</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @php
                                $funnel = $charts['lead_conversion_funnel'];
                                $maxCount = max(array_values($funnel));
                            @endphp
                            
                            @foreach([
                                ['key' => 'leads', 'label' => 'Leads', 'color' => 'bg-blue-500'],
                                ['key' => 'quotations', 'label' => 'Quotations', 'color' => 'bg-green-500'],
                                ['key' => 'accepted_quotations', 'label' => 'Accepted', 'color' => 'bg-yellow-500'],
                                ['key' => 'invoices', 'label' => 'Invoices', 'color' => 'bg-orange-500'],
                                ['key' => 'paid_invoices', 'label' => 'Paid', 'color' => 'bg-purple-500']
                            ] as $stage)
                                @php
                                    $count = $funnel[$stage['key']] ?? 0;
                                    $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                                @endphp
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 rounded-full {{ $stage['color'] }} mr-3"></div>
                                        <span class="text-sm font-medium text-gray-700">{{ $stage['label'] }}</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-32 bg-gray-200 rounded-full h-2">
                                            <div class="{{ $stage['color'] }} h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900 w-8 text-right">{{ $count }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Performance & Customer Segments -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Team Performance Ranking -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Team Performance This Month</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @forelse($charts['team_performance_ranking'] as $index => $team)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600 mr-3">
                                            {{ $index + 1 }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $team['name'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $team['member_count'] }} members</div>
                                        </div>
                                    </div>
                                    <div class="text-sm font-bold text-gray-900">
                                        RM {{ number_format($team['revenue'], 2) }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-gray-500 py-4">No team performance data available</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Customer Segment Revenue -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Revenue by Customer Segment</h3>
                    </div>
                    <div class="p-6">
                        <div class="h-64 flex items-center justify-center">
                            <canvas id="segmentRevenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Metrics -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Company Overview -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Company Overview</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Teams</span>
                                <span class="text-sm font-medium text-gray-900">{{ $metrics['total_teams'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Active Users</span>
                                <span class="text-sm font-medium text-gray-900">{{ $metrics['active_users'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Average Deal Size</span>
                                <span class="text-sm font-medium text-gray-900">RM {{ number_format($metrics['average_deal_size'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Yearly Revenue</span>
                                <span class="text-sm font-medium text-gray-900">RM {{ number_format($metrics['yearly_revenue'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performer -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Top Performer This Month</h3>
                    </div>
                    <div class="p-6">
                        @if($metrics['top_performer'])
                            <div class="text-center">
                                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                    </svg>
                                </div>
                                <h4 class="text-lg font-medium text-gray-900">{{ $metrics['top_performer']->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $metrics['top_performer']->email }}</p>
                                <p class="text-lg font-bold text-green-600 mt-2">
                                    RM {{ number_format($metrics['top_performer']->assigned_invoices_sum_total ?: 0, 2) }}
                                </p>
                            </div>
                        @else
                            <div class="text-center text-gray-500 py-4">No performance data available</div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('leads.index') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg border border-gray-200">
                                View All Leads
                            </a>
                            <a href="{{ route('quotations.index') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg border border-gray-200">
                                Review Quotations
                            </a>
                            <a href="{{ route('invoices.index') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg border border-gray-200">
                                Check Overdue Invoices
                            </a>
                            <a href="{{ route('teams.index') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg border border-gray-200">
                                Manage Teams
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Revenue Trend Chart
        const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
        new Chart(revenueTrendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($charts['monthly_revenue_trend']->pluck('period')) !!},
                datasets: [{
                    label: 'Revenue (RM)',
                    data: {!! json_encode($charts['monthly_revenue_trend']->pluck('revenue')) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'RM ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Customer Segment Revenue Chart
        const segmentRevenueCtx = document.getElementById('segmentRevenueChart').getContext('2d');
        const segmentData = {!! json_encode($charts['customer_segment_revenue']) !!};
        
        new Chart(segmentRevenueCtx, {
            type: 'doughnut',
            data: {
                labels: segmentData.map(s => s.name),
                datasets: [{
                    data: segmentData.map(s => s.revenue),
                    backgroundColor: segmentData.map(s => s.color),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': RM ' + context.parsed.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>