{{-- Multiple Quotes Alerts Widget for Manager/Coordinator Dashboard --}}

@if(config('lead_tracking.enabled') && config('lead_tracking.dashboard.show_multiple_quotes_widget'))
    @php
        $multipleQuotesLeads = App\Models\Lead::forCompany()
            ->withMultipleQuotes()
            ->with(['assignedTo', 'team'])
            ->limit(config('lead_tracking.dashboard.recent_alerts_limit', 10))
            ->get();
    @endphp

    @if($multipleQuotesLeads->count() > 0)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-orange-800">
                        🔔 Multiple Quotes Alert
                    </h3>
                    <span class="bg-orange-100 text-orange-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $multipleQuotesLeads->count() }} leads
                    </span>
                </div>

                <div class="space-y-3">
                    @foreach($multipleQuotesLeads as $lead)
                        <div class="bg-orange-50 rounded-lg p-3 border border-orange-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex-1">
                                    <a href="{{ route('leads.show', $lead) }}" class="font-medium text-gray-900 hover:text-blue-600">
                                        {{ $lead->name }}
                                    </a>
                                    <p class="text-sm text-gray-600">{{ $lead->phone }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">Quotes:</p>
                                    <p class="text-lg font-bold text-orange-600">
                                        {{ $lead->quote_count }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-xs text-gray-600 mt-2">
                                <p class="font-medium mb-1">Contacted by:</p>
                                <div class="space-y-0.5">
                                    @foreach($lead->getActiveReps() as $rep)
                                        <p>
                                            • {{ $rep['name'] }}
                                            @if($rep['quoted'])
                                                <span class="font-medium">- RM {{ number_format($rep['quoted'], 2) }}</span>
                                            @endif
                                            <span class="text-gray-500">({{ \Carbon\Carbon::parse($rep['contacted_at'])->diffForHumans() }})</span>
                                        </p>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-3">
                                <a href="{{ route('leads.show', $lead) }}"
                                   class="text-sm text-blue-600 hover:underline">
                                    Coordinate Team →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($multipleQuotesLeads->count() >= config('lead_tracking.dashboard.recent_alerts_limit', 10))
                    <div class="mt-4 text-center">
                        <a href="{{ route('leads.index', ['filter' => 'multiple_quotes']) }}"
                           class="text-sm text-blue-600 hover:underline">
                            View All Multi-Quote Leads →
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
