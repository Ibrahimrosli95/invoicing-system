{{-- Price War Alerts Widget for Manager Dashboard --}}

@if(config('lead_tracking.enabled') && config('lead_tracking.dashboard.show_price_war_widget'))
    @php
        $priceWarLeads = App\Models\Lead::forCompany()
            ->flaggedForReview()
            ->whereJsonContains('review_flags', ['type' => 'price_war'])
            ->with(['assignedTo', 'team'])
            ->limit(config('lead_tracking.dashboard.recent_alerts_limit', 10))
            ->get();
    @endphp

    @if($priceWarLeads->count() > 0)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-red-800">
                        🚨 Price War Alerts
                    </h3>
                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $priceWarLeads->count() }} active
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach($priceWarLeads as $lead)
                        @php
                            $latestFlag = collect($lead->review_flags)->where('type', 'price_war')->last();
                        @endphp

                        <div class="bg-red-50 rounded-lg p-3 border border-red-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex-1">
                                    <a href="{{ route('leads.show', $lead) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                        {{ $lead->name }}
                                    </a>
                                    <p class="text-sm text-gray-600">{{ $lead->phone }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">Price drop:</p>
                                    <p class="text-lg font-bold text-red-600">
                                        {{ $latestFlag['details']['drop_percentage'] ?? 0 }}%
                                    </p>
                                </div>
                            </div>

                            <div class="text-xs text-gray-600 mt-2">
                                <p>
                                    <span class="font-medium">Previous:</span> RM {{ number_format($latestFlag['details']['previous_quote'] ?? 0, 2) }}
                                    →
                                    <span class="font-medium">New:</span> RM {{ number_format($latestFlag['details']['new_quote'] ?? 0, 2) }}
                                </p>
                                <p class="mt-1">
                                    <span class="font-medium">Latest by:</span> {{ $latestFlag['details']['user_name'] ?? 'Unknown' }}
                                </p>
                            </div>

                            <div class="mt-3 flex items-center justify-between">
                                <a href="{{ route('leads.show', $lead) }}"
                                   class="text-sm text-blue-600 hover:underline">
                                    Review & Intervene →
                                </a>
                                <form method="POST" action="{{ route('leads.clear-flags', $lead) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs text-gray-500 hover:text-gray-700">
                                        Clear Flag
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($priceWarLeads->count() >= config('lead_tracking.dashboard.recent_alerts_limit', 10))
                    <div class="mt-4 text-center">
                        <a href="{{ route('leads.index', ['filter' => 'flagged']) }}"
                           class="text-sm text-blue-600 hover:underline">
                            View All Flagged Leads →
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
