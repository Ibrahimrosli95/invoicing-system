<x-app-layout>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
                    <div>
                        <h1 class="text-3xl font-bold">My Dashboard</h1>
                        <p class="text-indigo-100 mt-2">Welcome back, {{ auth()->user()->name }}! Let's achieve your goals today.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                        <button class="bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white px-6 py-2 rounded-xl text-sm font-medium shadow-lg transition-all duration-200 border border-white/20">
                            My Performance Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-indigo-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Personal Performance Summary -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
                <!-- My Revenue -->
                <div class="group bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-400/20 to-green-600/20 rounded-bl-3xl"></div>
                        <div class="relative">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm font-medium text-gray-600">My Revenue</div>
                                    <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">RM {{ number_format($metrics['my_revenue'], 0) }}</div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="flex justify-between items-center text-xs mb-2">
                                    <span class="text-gray-500">Goal: RM {{ number_format($metrics['revenue_target'], 0) }}</span>
                                    <span class="font-semibold @if($metrics['revenue_progress'] >= 100) text-green-600 @elseif($metrics['revenue_progress'] >= 80) text-yellow-600 @else text-red-600 @endif">
                                        {{ number_format($metrics['revenue_progress'], 1) }}%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 shadow-inner">
                                    <div class="@if($metrics['revenue_progress'] >= 100) bg-gradient-to-r from-green-400 to-green-600 @elseif($metrics['revenue_progress'] >= 80) bg-gradient-to-r from-yellow-400 to-yellow-600 @else bg-gradient-to-r from-red-400 to-red-600 @endif h-3 rounded-full shadow-sm transition-all duration-500 ease-out"
                                         style="width: {{ min($metrics['revenue_progress'], 100) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Active Leads -->
                <div class="group bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-400/20 to-blue-600/20 rounded-bl-3xl"></div>
                        <div class="relative">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm font-medium text-gray-600">Active Leads</div>
                                    <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">{{ $metrics['active_leads'] }}</div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="text-sm text-gray-600">
                                    <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                        {{ $metrics['new_leads_this_week'] }} new this week
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Conversion Rate -->
                <div class="group bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-purple-400/20 to-purple-600/20 rounded-bl-3xl"></div>
                        <div class="relative">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm font-medium text-gray-600">Conversion Rate</div>
                                    <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">{{ number_format($metrics['conversion_rate'], 1) }}%</div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="text-sm text-gray-600">
                                    <span class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">
                                        {{ $metrics['won_leads'] }}/{{ $metrics['total_opportunities'] }} opportunities
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Tasks -->
                <div class="group bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-orange-400/20 to-orange-600/20 rounded-bl-3xl"></div>
                        <div class="relative">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm font-medium text-gray-600">Pending Tasks</div>
                                    <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">{{ $metrics['pending_tasks'] }}</div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="text-sm text-gray-600">
                                    @if($metrics['overdue_tasks'] > 0)
                                        <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                            {{ $metrics['overdue_tasks'] }} overdue
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                            All up to date
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
                <!-- My Pipeline -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-white/50">
                        <h3 class="text-xl font-bold text-gray-900">My Sales Pipeline</h3>
                        <p class="text-sm text-gray-600 mt-1">Personal lead distribution and progress</p>
                    </div>
                    <div class="p-6">
                        <div class="h-72 flex items-center justify-center">
                            <canvas id="pipelineChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Monthly Performance -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-white/50">
                        <h3 class="text-xl font-bold text-gray-900">My Monthly Performance</h3>
                        <p class="text-sm text-gray-600 mt-1">Track your personal achievement trends</p>
                    </div>
                    <div class="p-6">
                        <div class="h-72 flex items-center justify-center">
                            <canvas id="performanceChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Action Items -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
                <!-- Today's Tasks -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-white/50 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Today's Tasks</h3>
                            <p class="text-sm text-gray-600 mt-1">Your agenda for today</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            {{ count($todays_tasks) }} tasks
                        </span>
                    </div>
                    <div class="p-6">
                        @if(count($todays_tasks) > 0)
                            <div class="space-y-3">
                                @foreach($todays_tasks as $task)
                                <div class="flex items-center space-x-3 p-3 rounded-lg border 
                                    @if($task['priority'] == 'HIGH') border-red-200 bg-red-50
                                    @elseif($task['priority'] == 'MEDIUM') border-yellow-200 bg-yellow-50
                                    @else border-gray-200 bg-white
                                    @endif">
                                    <input type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                           @if($task['completed']) checked @endif>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 @if($task['completed']) line-through @endif">
                                            {{ $task['description'] }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $task['type'] }} • {{ $task['customer_name'] }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($task['priority'] == 'HIGH') bg-red-100 text-red-800
                                            @elseif($task['priority'] == 'MEDIUM') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ $task['priority'] }}
                                        </span>
                                        @if($task['overdue'])
                                            <span class="text-red-600 text-xs">Overdue</span>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">All done for today!</h3>
                                <p class="mt-1 text-sm text-gray-500">No tasks scheduled for today.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- My Hot Leads -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-red-50 to-pink-50 border-b border-white/50 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">My Hot Leads</h3>
                            <p class="text-sm text-gray-600 mt-1">Leads requiring immediate attention</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            {{ count($hot_leads) }} leads
                        </span>
                    </div>
                    <div class="p-6">
                        @if(count($hot_leads) > 0)
                            <div class="space-y-4">
                                @foreach($hot_leads as $lead)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">{{ $lead['company_name'] }}</h4>
                                            <p class="text-sm text-gray-600">{{ $lead['contact_person'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $lead['phone'] }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-sm font-medium text-gray-900">RM {{ number_format($lead['estimated_value'], 0) }}</span>
                                            <div class="text-xs text-gray-500">{{ $lead['days_since_contact'] }} days ago</div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($lead['status'] == 'NEW') bg-blue-100 text-blue-800
                                            @elseif($lead['status'] == 'CONTACTED') bg-yellow-100 text-yellow-800
                                            @elseif($lead['status'] == 'QUOTED') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $lead['status'] }}
                                        </span>
                                        <div class="flex space-x-2">
                                            <a href="/leads/{{ $lead['id'] }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                                View
                                            </a>
                                            @if($lead['status'] !== 'QUOTED')
                                            <a href="/quotations/create?lead_id={{ $lead['id'] }}" class="text-green-600 hover:text-green-800 text-xs font-medium">
                                                Quote
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Great job!</h3>
                                <p class="mt-1 text-sm text-gray-500">No leads requiring immediate attention.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Activity & Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- My Recent Activities -->
                <div class="lg:col-span-2 bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-white/50">
                        <h3 class="text-xl font-bold text-gray-900">My Recent Activities</h3>
                        <p class="text-sm text-gray-600 mt-1">Track your latest achievements and milestones</p>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                @foreach($recent_activities as $index => $activity)
                                <li>
                                    <div class="relative pb-8">
                                        @if($index < count($recent_activities) - 1)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gradient-to-b from-indigo-200 to-purple-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-4">
                                            <div>
                                                <span class="h-10 w-10 rounded-2xl flex items-center justify-center ring-4 ring-white shadow-lg
                                                    @if($activity['type'] == 'lead_created') bg-gradient-to-br from-blue-400 to-blue-600
                                                    @elseif($activity['type'] == 'quotation_sent') bg-gradient-to-br from-green-400 to-green-600
                                                    @elseif($activity['type'] == 'deal_won') bg-gradient-to-br from-yellow-400 to-yellow-600
                                                    @else bg-gradient-to-br from-gray-400 to-gray-600
                                                    @endif">
                                                    @if($activity['type'] == 'lead_created')
                                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                        </svg>
                                                    @elseif($activity['type'] == 'quotation_sent')
                                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    @elseif($activity['type'] == 'deal_won')
                                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                                        </svg>
                                                    @else
                                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $activity['description'] }}</p>
                                                    @if(isset($activity['details']))
                                                        <p class="text-sm text-gray-600 mt-1">{{ $activity['details'] }}</p>
                                                    @endif
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap">
                                                    <time class="text-gray-500 font-medium">{{ $activity['time_ago'] }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-white/50">
                        <h3 class="text-xl font-bold text-gray-900">Quick Actions</h3>
                        <p class="text-sm text-gray-600 mt-1">Fast access to common tasks</p>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <a href="/leads/create" class="group block w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-sm font-medium py-4 px-6 rounded-xl text-center transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <div class="flex items-center justify-center space-x-3">
                                    <div class="w-6 h-6 bg-white/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </div>
                                    <span>Add New Lead</span>
                                </div>
                            </a>

                            <a href="/quotations/create" class="group block w-full bg-gradient-to-r from-green-600 to-emerald-700 hover:from-green-700 hover:to-emerald-800 text-white text-sm font-medium py-4 px-6 rounded-xl text-center transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <div class="flex items-center justify-center space-x-3">
                                    <div class="w-6 h-6 bg-white/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <span>Create Quotation</span>
                                </div>
                            </a>

                            <a href="/leads/kanban" class="group block w-full bg-gradient-to-r from-purple-600 to-indigo-700 hover:from-purple-700 hover:to-indigo-800 text-white text-sm font-medium py-4 px-6 rounded-xl text-center transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <div class="flex items-center justify-center space-x-3">
                                    <div class="w-6 h-6 bg-white/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                        </svg>
                                    </div>
                                    <span>View Pipeline</span>
                                </div>
                            </a>

                            <a href="/service-templates" class="group block w-full bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white text-sm font-medium py-4 px-6 rounded-xl text-center transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                <div class="flex items-center justify-center space-x-3">
                                    <div class="w-6 h-6 bg-white/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                        </svg>
                                    </div>
                                    <span>Service Templates</span>
                                </div>
                            </a>
                        </div>

                        <div class="mt-8 pt-6 border-t border-gradient-to-r from-gray-200 to-purple-200">
                            <h4 class="text-sm font-bold text-gray-900 mb-4">Performance Goals</h4>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-sm mb-2">
                                        <span class="text-gray-600 font-medium">Monthly Revenue</span>
                                        <span class="font-bold @if($metrics['revenue_progress'] >= 100) text-green-600 @elseif($metrics['revenue_progress'] >= 80) text-yellow-600 @else text-red-600 @endif">
                                            {{ number_format($metrics['revenue_progress'], 1) }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 shadow-inner">
                                        <div class="@if($metrics['revenue_progress'] >= 100) bg-gradient-to-r from-green-400 to-green-600 @elseif($metrics['revenue_progress'] >= 80) bg-gradient-to-r from-yellow-400 to-yellow-600 @else bg-gradient-to-r from-red-400 to-red-600 @endif h-3 rounded-full shadow-sm transition-all duration-500 ease-out"
                                             style="width: {{ min($metrics['revenue_progress'], 100) }}%"></div>
                                    </div>
                                </div>

                                <div>
                                    <div class="flex justify-between text-sm mb-2">
                                        <span class="text-gray-600 font-medium">Leads Target</span>
                                        <span class="font-bold @if($metrics['leads_progress'] >= 100) text-green-600 @elseif($metrics['leads_progress'] >= 80) text-yellow-600 @else text-blue-600 @endif">
                                            {{ number_format($metrics['leads_progress'], 1) }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 shadow-inner">
                                        <div class="@if($metrics['leads_progress'] >= 100) bg-gradient-to-r from-green-400 to-green-600 @elseif($metrics['leads_progress'] >= 80) bg-gradient-to-r from-yellow-400 to-yellow-600 @else bg-gradient-to-r from-blue-400 to-blue-600 @endif h-3 rounded-full shadow-sm transition-all duration-500 ease-out"
                                             style="width: {{ min($metrics['leads_progress'], 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // My Pipeline Chart
        const pipelineCtx = document.getElementById('pipelineChart').getContext('2d');
        new Chart(pipelineCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($pipeline_data)) !!},
                datasets: [{
                    data: {!! json_encode(array_values($pipeline_data)) !!},
                    backgroundColor: [
                        '#60A5FA', // Blue for New
                        '#34D399', // Green for Contacted
                        '#FBBF24', // Yellow for Quoted
                        '#10B981', // Emerald for Won
                        '#EF4444'  // Red for Lost
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

        // My Performance Chart
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        new Chart(performanceCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($performance_trends['labels']) !!},
                datasets: [{
                    label: 'My Revenue',
                    data: {!! json_encode($performance_trends['revenue']) !!},
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Leads Created',
                    data: {!! json_encode($performance_trends['leads']) !!},
                    borderColor: '#6366F1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4
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
                            text: 'Count / Revenue'
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>