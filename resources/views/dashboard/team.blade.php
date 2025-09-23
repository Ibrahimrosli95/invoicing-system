@extends('layouts.app')

@section('title', 'Team Dashboard')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 text-white rounded-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-3xl font-bold">Team Dashboard</h1>
                    <p class="text-emerald-100 mt-2">Monitor your team's performance and drive success together.</p>
                </div>
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                    <button class="bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white px-6 py-2 rounded-xl text-sm font-medium shadow-lg transition-all duration-200 border border-white/20">
                        Team Report
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
            <!-- Team Performance Summary -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8">
                <!-- Team Revenue -->
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
                                    <div class="text-sm font-medium text-gray-600">Team Revenue</div>
                                    <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">RM {{ number_format($metrics['team_revenue'], 0) }}</div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="flex items-center text-sm">
                                    @if($metrics['team_revenue_growth'] > 0)
                                        <div class="flex items-center text-emerald-600 font-semibold">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                            </svg>
                                            +{{ number_format($metrics['team_revenue_growth'], 1) }}%
                                        </div>
                                    @elseif($metrics['team_revenue_growth'] < 0)
                                        <div class="flex items-center text-red-600 font-semibold">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                            </svg>
                                            {{ number_format($metrics['team_revenue_growth'], 1) }}%
                                        </div>
                                    @else
                                        <div class="flex items-center text-gray-500 font-semibold">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            0%
                                        </div>
                                    @endif
                                    <span class="text-gray-500 ml-2">vs last month</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Leads -->
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

                <!-- Conversion Rate -->
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
                                    <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">{{ number_format($metrics['team_conversion_rate'], 1) }}%</div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="text-sm text-gray-600">
                                    <span class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">
                                        {{ $metrics['won_leads'] }}/{{ $metrics['total_opportunities'] }} leads
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Avg Deal Size -->
                <div class="group bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50 hover:shadow-2xl hover:scale-105 transition-all duration-300">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-orange-400/20 to-orange-600/20 rounded-bl-3xl"></div>
                        <div class="relative">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-br from-orange-400 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="text-sm font-medium text-gray-600">Avg Deal Size</div>
                                    <div class="text-2xl lg:text-3xl font-bold text-gray-900 mt-1">RM {{ number_format($metrics['avg_deal_size'], 0) }}</div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="text-sm text-gray-600">
                                    <span class="inline-flex items-center px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-medium">
                                        {{ $metrics['closed_deals'] }} deals closed
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
                <!-- Sales Pipeline Chart -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-white/50">
                        <h3 class="text-xl font-bold text-gray-900">Sales Pipeline</h3>
                        <p class="text-sm text-gray-600 mt-1">Current lead distribution across pipeline stages</p>
                    </div>
                    <div class="p-6">
                        <div class="h-72 flex items-center justify-center">
                            <canvas id="pipelineChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Team Performance Trends -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-emerald-50 to-teal-50 border-b border-white/50">
                        <h3 class="text-xl font-bold text-gray-900">Performance Trends</h3>
                        <p class="text-sm text-gray-600 mt-1">Track team performance over time</p>
                    </div>
                    <div class="p-6">
                        <div class="h-72 flex items-center justify-center">
                            <canvas id="performanceChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Members Performance Ranking -->
            <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50 mb-8">
                <div class="px-6 py-5 bg-gradient-to-r from-yellow-50 to-amber-50 border-b border-white/50">
                    <h3 class="text-xl font-bold text-gray-900">Team Members Performance</h3>
                    <p class="text-sm text-gray-600 mt-1">Individual performance rankings and metrics</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rank
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Member
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Active Leads
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quotations
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Revenue
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Conversion
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($team_members as $index => $member)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        @if($index == 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                🏆 #{{ $index + 1 }}
                                            </span>
                                        @elseif($index == 1)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                🥈 #{{ $index + 1 }}
                                            </span>
                                        @elseif($index == 2)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                🥉 #{{ $index + 1 }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                #{{ $index + 1 }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ strtoupper(substr($member['name'], 0, 2)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $member['name'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $member['email'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="font-medium">{{ $member['active_leads'] }}</span>
                                    @if($member['new_leads_this_week'] > 0)
                                        <span class="text-green-600 text-xs ml-1">
                                            (+{{ $member['new_leads_this_week'] }})
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <span class="font-medium">{{ $member['quotations_sent'] }}</span>
                                        <span class="text-gray-400 mx-1">/</span>
                                        <span class="text-gray-600">{{ $member['quotations_total'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    RM {{ number_format($member['revenue'], 0) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center">
                                        <span class="font-medium">{{ number_format($member['conversion_rate'], 1) }}%</span>
                                        <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" 
                                                 style="width: {{ min($member['conversion_rate'], 100) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($member['performance_score'] >= 85)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Excellent
                                        </span>
                                    @elseif($member['performance_score'] >= 70)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Good
                                        </span>
                                    @elseif($member['performance_score'] >= 50)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Average
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Needs Attention
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Activities & Pipeline Management -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <!-- Recent Team Activities -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-white/50">
                        <h3 class="text-xl font-bold text-gray-900">Recent Team Activities</h3>
                        <p class="text-sm text-gray-600 mt-1">Latest team actions and achievements</p>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                @foreach($recent_activities as $index => $activity)
                                <li>
                                    <div class="relative pb-8">
                                        @if($index < count($recent_activities) - 1)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                    @if($activity['type'] == 'lead_created') bg-blue-500
                                                    @elseif($activity['type'] == 'quotation_sent') bg-green-500
                                                    @elseif($activity['type'] == 'deal_won') bg-yellow-500
                                                    @else bg-gray-500
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
                                                    <p class="text-sm text-gray-900">
                                                        <strong>{{ $activity['user_name'] }}</strong> {{ $activity['description'] }}
                                                    </p>
                                                    @if(isset($activity['details']))
                                                        <p class="text-sm text-gray-500">{{ $activity['details'] }}</p>
                                                    @endif
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time>{{ $activity['time_ago'] }}</time>
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

                <!-- Pipeline Hot Leads -->
                <div class="bg-white/70 backdrop-blur-sm overflow-hidden shadow-xl rounded-2xl border border-white/50">
                    <div class="px-6 py-5 bg-gradient-to-r from-red-50 to-pink-50 border-b border-white/50 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Hot Leads Requiring Attention</h3>
                            <p class="text-sm text-gray-600 mt-1">Priority leads needing immediate follow-up</p>
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
                                        </div>
                                        <div class="text-right">
                                            <span class="text-sm font-medium text-gray-900">RM {{ number_format($lead['estimated_value'], 0) }}</span>
                                            <div class="text-xs text-gray-500">{{ $lead['days_since_contact'] }} days ago</div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($lead['priority'] == 'HIGH') bg-red-100 text-red-800
                                                @elseif($lead['priority'] == 'MEDIUM') bg-yellow-100 text-yellow-800
                                                @else bg-green-100 text-green-800
                                                @endif">
                                                {{ $lead['priority'] }}
                                            </span>
                                            <span class="text-xs text-gray-500">Assigned to: {{ $lead['assigned_to'] }}</span>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                                Follow Up
                                            </button>
                                            <button class="text-green-600 hover:text-green-800 text-xs font-medium">
                                                Create Quote
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
                                <h3 class="mt-2 text-sm font-medium text-gray-900">All caught up!</h3>
                                <p class="mt-1 text-sm text-gray-500">No hot leads requiring immediate attention.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Pipeline Distribution Chart
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

        // Performance Trends Chart
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        new Chart(performanceCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($performance_trends['labels']) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode($performance_trends['revenue']) !!},
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    yAxisID: 'y'
                }, {
                    label: 'Conversion Rate (%)',
                    data: {!! json_encode($performance_trends['conversion']) !!},
                    borderColor: '#6366F1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    yAxisID: 'y1'
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
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue (RM)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Conversion Rate (%)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    </script>
@endsection