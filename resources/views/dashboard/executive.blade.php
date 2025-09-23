@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('header')
<div class="bg-gradient-to-r from-slate-900 via-indigo-900 to-blue-700 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-indigo-200">Executive Overview</p>
                <h1 class="mt-3 text-3xl font-bold">Executive Dashboard</h1>
                <p class="mt-3 text-indigo-100 max-w-2xl">
                    A high-level view of revenue health, sales momentum, and team performance across the organisation.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <select class="rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white transition focus:border-white/60 focus:outline-none focus:ring-2 focus:ring-white/50">
                    <option value="30" class="text-slate-900">Last 30 days</option>
                    <option value="90" class="text-slate-900">Last 90 days</option>
                    <option value="365" class="text-slate-900">Last year</option>
                </select>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
    <x-slot name="header">
        <div class="bg-gradient-to-r from-slate-900 via-indigo-900 to-blue-700 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-indigo-200">Executive Overview</p>
                        <h1 class="mt-3 text-3xl font-bold">Executive Dashboard</h1>
                        <p class="mt-3 text-indigo-100 max-w-2xl">
                            A high-level view of revenue health, sales momentum, and team performance across the organisation.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <select class="rounded-xl border border-white/20 bg-white/10 px-4 py-2 text-sm font-medium text-white transition focus:border-white/60 focus:outline-none focus:ring-2 focus:ring-white/50">
                            <option value="30" class="text-slate-900">Last 30 days</option>
                            <option value="90" class="text-slate-900">Last 90 days</option>
                            <option value="180" class="text-slate-900">Last 6 months</option>
                            <option value="365" class="text-slate-900">This year</option>
                        </select>
                        <button class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-5 py-2 text-sm font-semibold text-white transition hover:bg-white/25">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 10l5 5 5-5M12 4v11" />
                            </svg>
                            Export report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    @php

        $primaryCards = [
            [
                'label' => 'Monthly revenue',
                'value' => 'RM ' . number_format($metrics['monthly_revenue'] ?? 0, 0),
                'hint' => 'Collected from paid invoices in the current month.',
                'icon' => 'revenue',
                'accent' => 'bg-indigo-100 text-indigo-600'
            ],
            [
                'label' => 'Outstanding amount',
                'value' => 'RM ' . number_format($metrics['outstanding_amount'] ?? 0, 0),
                'hint' => ($metrics['overdue_invoices'] ?? 0) . ' invoices awaiting payment.',
                'icon' => 'outstanding',
                'accent' => 'bg-amber-100 text-amber-600'
            ],
            [
                'label' => 'Year-to-date revenue',
                'value' => 'RM ' . number_format($metrics['yearly_revenue'] ?? 0, 0),
                'hint' => 'Since January 1 of the current year.',
                'icon' => 'calendar',
                'accent' => 'bg-emerald-100 text-emerald-600'
            ],
            [
                'label' => 'Average deal size',
                'value' => 'RM ' . number_format($metrics['average_deal_size'] ?? 0, 0),
                'hint' => 'Average value of closed invoices.',
                'icon' => 'deal',
                'accent' => 'bg-sky-100 text-sky-600'
            ],
        ];

        $pipelineCards = [
            [
                'label' => 'Active leads',
                'value' => number_format($metrics['active_leads'] ?? 0),
                'description' => 'In early stages of the pipeline.',
                'icon' => 'leads',
            ],
            [
                'label' => 'Pending quotations',
                'value' => number_format($metrics['pending_quotations'] ?? 0),
                'description' => 'Awaiting client feedback.',
                'icon' => 'quote',
            ],
            [
                'label' => 'Overdue invoices',
                'value' => number_format($metrics['overdue_invoices'] ?? 0),
                'description' => 'Require finance follow-up.',
                'icon' => 'alert',
            ],
            [
                'label' => 'Active teams',
                'value' => number_format($metrics['total_teams'] ?? 0),
                'description' => number_format($metrics['active_users'] ?? 0) . ' active users in total.',
                'icon' => 'teams',
            ],
        ];

        $conversionCards = [
            [
                'label' => 'Lead to Quote',
                'value' => round($metrics['lead_conversion_rate'] ?? 0, 1),
                'description' => 'Leads converted to quotations.',
                'color' => 'bg-indigo-500',
                'icon' => 'conversion',
            ],
            [
                'label' => 'Quote to Invoice',
                'value' => round($metrics['quotation_conversion_rate'] ?? 0, 1),
                'description' => 'Quotations converted to invoices.',
                'color' => 'bg-violet-500',
                'icon' => 'conversion',
            ],
        ];

        $icons = [
            'revenue' => <<<'SVG'
<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
</svg>
SVG,
            'outstanding' => <<<'SVG'
<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c1.104 0 2 .672 2 1.5S13.104 11 12 11s-2 .672-2 1.5S10.896 14 12 14m0-6c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2" />
</svg>
SVG,
            'calendar' => <<<'SVG'
<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V5m8 2V5M5 9h14m-1 11H6a2 2 0 01-2-2V7a2 2 0 012-2h12a2 2 0 012 2v11a2 2 0 01-2 2z" />
</svg>
SVG,
            'deal' => <<<'SVG'
<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 12.3a3 3 0 11-4.197 4.197L9 11.697V9l3.297-3.297A3 3 0 1116.3 9l-1.55 1.55" />
</svg>
SVG,
            'leads' => <<<'SVG'
<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0" />
</svg>
SVG,
            'quote' => <<<'SVG'
<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m2 0h1a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h1m12 0H6" />
</svg>
SVG,
            'alert' => <<<'SVG'
<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v3m0 3h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
</svg>
SVG,
            'teams' => <<<'SVG'
<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20v-2a3 3 0 00-3-3H6a3 3 0 00-3 3v2m18 0v-2a3 3 0 00-2.356-2.929M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 6a3 3 0 11-6 0 3 3 0 016 0zm-6 7v-2a3.001 3.001 0 012.356-2.929M9 14a3 3 0 00-2.356 2.929M9 14a3 3 0 015 0" />
</svg>
SVG,
            'conversion' => <<<'SVG'
<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h8m-8 5h16" />
</svg>
SVG,
        ];

        $funnel = $charts['lead_conversion_funnel'] ?? [];
        $maxFunnel = count($funnel) ? max($funnel) : 0;
        $funnelStages = [
            ['key' => 'leads', 'label' => 'Leads', 'color' => 'bg-blue-500'],
            ['key' => 'quotations', 'label' => 'Quotations', 'color' => 'bg-emerald-500'],
            ['key' => 'accepted_quotations', 'label' => 'Accepted quotes', 'color' => 'bg-amber-500'],
            ['key' => 'invoices', 'label' => 'Invoices', 'color' => 'bg-orange-500'],
            ['key' => 'paid_invoices', 'label' => 'Paid invoices', 'color' => 'bg-purple-500'],
        ];

        $segmentData = $charts['customer_segment_revenue'] ?? collect();
        if ($segmentData instanceof \Illuminate\Support\Collection) {
            $segmentData = $segmentData->toArray();
        }

        $invoiceAging = $charts['invoice_aging_chart'] ?? [];
        $topPerformer = $metrics['top_performer'] ?? null;
    @endphp

    <div class="bg-slate-100 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Primary revenue highlights -->
            <section>
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800">Revenue snapshot</h2>
                    <span class="text-xs font-medium uppercase tracking-wider text-slate-500">Updated {{ now()->format('d M Y') }}</span>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($primaryCards as $card)
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                                    <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $card['value'] }}</p>
                                </div>
                                <div class="rounded-xl p-3 {{ $card['accent'] }}">
                                    {!! $icons[$card['icon']] ?? '' !!}
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-slate-500">{{ $card['hint'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- Pipeline and team health -->
            <section>
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800">Pipeline health</h2>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($pipelineCards as $card)
                        <div class="flex items-center rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                            <div class="rounded-lg bg-slate-100 p-3 text-slate-700">
                                {!! $icons[$card['icon']] ?? '' !!}
                            </div>
                            <div class="ml-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                                <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $card['value'] }}</p>
                                <p class="text-sm text-slate-500">{{ $card['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- Conversion metrics -->
            <section>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach ($conversionCards as $card)
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                                    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($card['value'], 1) }}%</p>
                                    <p class="mt-2 text-sm text-slate-500">{{ $card['description'] }}</p>
                                </div>
                                <div class="rounded-lg bg-slate-100 p-3 text-slate-700">
                                    {!! $icons[$card['icon']] ?? '' !!}
                                </div>
                            </div>
                            <div class="mt-5">
                                <div class="h-2 rounded-full bg-slate-200">
                                    <div class="h-2 rounded-full {{ $card['color'] }}" style="width: {{ min(100, max(0, $card['value'])) }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- Charts -->
            <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-800">Monthly revenue trend</h3>
                        <p class="text-sm text-slate-500">Track cash collected over the last periods.</p>
                    </div>
                    <div class="p-6">
                        <div class="h-72">
                            <canvas id="revenueTrendChart" class="h-full w-full"></canvas>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-800">Sales conversion funnel</h3>
                        <p class="text-sm text-slate-500">Volume of work progressing through each stage.</p>
                    </div>
                    <div class="p-6 space-y-5">
                        @foreach ($funnelStages as $stage)
                            @php
                                $count = $funnel[$stage['key']] ?? 0;
                                $percentage = $maxFunnel > 0 ? ($count / $maxFunnel) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-sm font-medium text-slate-700">
                                    <span>{{ $stage['label'] }}</span>
                                    <span>{{ number_format($count) }}</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-200">
                                    <div class="h-2 rounded-full {{ $stage['color'] }}" style="width: {{ $percentage }}%"></div>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">{{ number_format($percentage, 1) }}% of top-of-funnel volume</p>
                            </div>
                        @endforeach
                        @if ($maxFunnel === 0)
                            <p class="text-center text-sm text-slate-500">No pipeline data available yet.</p>
                        @endif
                    </div>
                </div>
            </section>

            <!-- Team performance & customer segments -->
            <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-800">Team performance ranking</h3>
                        <p class="text-sm text-slate-500">Revenue generated per team this month.</p>
                    </div>
                    <div class="p-6 space-y-4">
                        @forelse ($charts['team_performance_ranking'] as $position => $team)
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-900 text-base font-semibold text-white">{{ $position + 1 }}</div>
                                    <div>
                                        <p class="text-base font-semibold text-slate-800">{{ $team['name'] }}</p>
                                        <p class="text-xs text-slate-500">{{ $team['member_count'] }} members</p>
                                    </div>
                                </div>
                                <p class="text-lg font-semibold text-slate-900">RM {{ number_format($team['revenue'] ?? 0, 0) }}</p>
                            </div>
                        @empty
                            <p class="text-center text-sm text-slate-500 py-8">No team performance data available yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-800">Revenue by customer segment</h3>
                        <p class="text-sm text-slate-500">Distribution of paid invoices by segment year-to-date.</p>
                    </div>
                    <div class="p-6">
                        @if (count($segmentData))
                            <div class="flex flex-col gap-6 lg:flex-row lg:items-center">
                                <div class="h-64 w-full lg:w-1/2">
                                    <canvas id="segmentRevenueChart" class="h-full w-full"></canvas>
                                </div>
                                <div class="flex-1 space-y-3">
                                    @foreach ($segmentData as $segment)
                                        <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <span class="inline-block h-3 w-3 rounded-full" style="background-color: {{ $segment['color'] ?? '#6366f1' }}"></span>
                                                <span class="text-sm font-medium text-slate-700">{{ $segment['name'] }}</span>
                                            </div>
                                            <span class="text-sm font-semibold text-slate-900">RM {{ number_format($segment['revenue'] ?? 0, 0) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-center text-sm text-slate-500 py-8">No revenue recorded for customer segments yet.</p>
                        @endif
                    </div>
                </div>
            </section>

            <!-- Aging & top performer -->
            <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h3 class="text-lg font-semibold text-slate-800">Invoice aging</h3>
                        <p class="text-sm text-slate-500">Outstanding value grouped by due window.</p>
                    </div>
                    <div class="p-6">
                        @if (!empty($invoiceAging))
                            <div class="h-64">
                                <canvas id="invoiceAgingChart" class="h-full w-full"></canvas>
                            </div>
                        @else
                            <p class="text-center text-sm text-slate-500 py-8">No overdue invoices at the moment.</p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-800">Top performer</h3>
                        @if ($topPerformer)
                            <div class="mt-5 flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-600 text-lg font-semibold text-white">
                                    {{ strtoupper(substr($topPerformer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-base font-semibold text-slate-900">{{ $topPerformer->name }}</p>
                                    <p class="text-sm text-slate-500">{{ $topPerformer->email }}</p>
                                </div>
                            </div>
                            <div class="mt-6 rounded-xl border border-indigo-100 bg-indigo-50 px-5 py-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500">Revenue this month</p>
                                <p class="mt-2 text-2xl font-semibold text-indigo-700">RM {{ number_format($topPerformer->assigned_invoices_sum_total ?? 0, 0) }}</p>
                            </div>
                        @else
                            <p class="text-sm text-slate-500 mt-4">No performance data available yet.</p>
                        @endif
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-slate-800">Quick actions</h3>
                        <div class="mt-4 space-y-3">
                            <a href="{{ route('leads.index') }}" class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                                <span>View all leads</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            <a href="{{ route('quotations.index') }}" class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
                                <span>Review quotations</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                            <a href="{{ route('invoices.index') }}" class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-600">
                                <span>Check overdue invoices</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const revenueCanvas = document.getElementById('revenueTrendChart');
            if (revenueCanvas) {
                new Chart(revenueCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($charts['monthly_revenue_trend']->pluck('period') ?? []) !!},
                        datasets: [{
                            label: 'Revenue (RM)',
                            data: {!! json_encode($charts['monthly_revenue_trend']->pluck('revenue') ?? []) !!},
                            borderColor: 'rgb(79, 70, 229)',
                            backgroundColor: 'rgba(79, 70, 229, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: value => 'RM ' + Number(value).toLocaleString()
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }

            const segmentData = {!! json_encode($segmentData) !!};
            const segmentCanvas = document.getElementById('segmentRevenueChart');
            if (segmentCanvas && segmentData.length) {
                new Chart(segmentCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: segmentData.map(item => item.name),
                        datasets: [{
                            data: segmentData.map(item => item.revenue),
                            backgroundColor: segmentData.map(item => item.color || '#6366f1'),
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: context => `${context.label}: RM ${Number(context.parsed).toLocaleString()}`
                                }
                            }
                        }
                    }
                });
            }

            const invoiceAging = {!! json_encode($invoiceAging) !!};
            const agingCanvas = document.getElementById('invoiceAgingChart');
            if (agingCanvas && Object.keys(invoiceAging).length) {
                new Chart(agingCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: Object.keys(invoiceAging),
                        datasets: [{
                            label: 'Amount due (RM)',
                            data: Object.values(invoiceAging),
                            backgroundColor: 'rgba(249, 115, 22, 0.2)',
                            borderColor: 'rgb(249, 115, 22)',
                            borderWidth: 1.5,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: value => 'RM ' + Number(value).toLocaleString()
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        </script>
    @endpush
@endsection
