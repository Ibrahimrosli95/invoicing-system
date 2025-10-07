@extends('layouts.app')

@section('title', 'Service Template Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $serviceTemplate->name }}</h1>
                    @if($serviceTemplate->is_active)
                        <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    @else
                        <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Inactive
                        </span>
                    @endif
                    @if($serviceTemplate->requires_approval)
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Requires Approval
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-600">{{ $serviceTemplate->category->name ?? 'N/A' }} • Created {{ $serviceTemplate->created_at->diffForHumans() }}</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <a href="{{ route('service-templates.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                    Back to Templates
                </a>

                @can('update', $serviceTemplate)
                <a href="{{ route('service-templates.edit', $serviceTemplate) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 011-1h1a2 2 0 100-4H7a1 1 0 01-1-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                    </svg>
                    Edit Template
                </a>
                @endcan

                @can('create', App\Models\ServiceTemplate::class)
                <form method="POST" action="{{ route('service-templates.duplicate', $serviceTemplate) }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Duplicate
                    </button>
                </form>
                @endcan

                @can('create', App\Models\Quotation::class)
                <form method="POST" action="{{ route('service-templates.convert', $serviceTemplate) }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Create Quotation
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Template Overview -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">Template Overview</h2>
                </div>
                <div class="p-6">
                    @if($serviceTemplate->description)
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Description</h3>
                            <p class="text-gray-900">{{ $serviceTemplate->description }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-1">Estimated Hours</h3>
                            <p class="text-lg font-semibold text-gray-900">
                                @if($serviceTemplate->estimated_hours)
                                    {{ number_format($serviceTemplate->estimated_hours, 1) }} hours
                                @else
                                    <span class="text-gray-400">Not specified</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-1">Base Price</h3>
                            <p class="text-lg font-semibold text-gray-900">
                                @if($serviceTemplate->base_price)
                                    RM {{ number_format($serviceTemplate->base_price, 2) }}
                                @else
                                    <span class="text-gray-400">Variable pricing</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <h3 class="text-sm font-medium text-gray-700 mb-1">Usage Count</h3>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $serviceTemplate->usage_count ?? 0 }} times
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Sections -->
            @if($serviceTemplate->sections && $serviceTemplate->sections->count() > 0)
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Template Sections</h2>
                    </div>
                    <div class="divide-y divide-gray-200">
                        @foreach($serviceTemplate->sections->sortBy('sort_order') as $section)
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-md font-medium text-gray-900">{{ $section->name }}</h3>
                                    @if($section->items && $section->items->count() > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $section->items->count() }} items
                                        </span>
                                    @endif
                                </div>

                                @if($section->description)
                                    <p class="text-sm text-gray-600 mb-4">{{ $section->description }}</p>
                                @endif

                                @if($section->items && $section->items->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="w-1/2 px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                    <th class="w-[8%] px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                                    <th class="w-[12%] px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                    <th class="w-[15%] px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                                                    <th class="w-[15%] px-2 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @php $sectionTotal = 0; @endphp
                                                @foreach($section->items->sortBy('sort_order') as $item)
                                                    @php
                                                        $itemTotal = ($item->default_quantity ?? 1) * ($item->default_unit_price ?? 0);
                                                        $sectionTotal += $itemTotal;
                                                    @endphp
                                                    <tr>
                                                        <td class="px-4 py-2 text-sm text-gray-900">
                                                            {{ $item->description }}
                                                            @if($item->specifications)
                                                                <div class="text-xs text-gray-500 mt-1">{{ $item->specifications }}</div>
                                                            @endif
                                                            @if($item->item_code)
                                                                <div class="text-xs text-gray-400 mt-1">Code: {{ $item->item_code }}</div>
                                                            @endif
                                                        </td>
                                                        <td class="px-2 py-2 text-sm text-gray-900">{{ $item->unit ?? 'Nos' }}</td>
                                                        <td class="px-2 py-2 text-sm text-gray-900 text-right">{{ number_format($item->default_quantity ?? 1, 2) }}</td>
                                                        <td class="px-2 py-2 text-sm text-gray-900 text-right">
                                                            @if($item->default_unit_price)
                                                                RM {{ number_format($item->default_unit_price, 2) }}
                                                            @else
                                                                <span class="text-gray-400">TBD</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-2 py-2 text-sm font-medium text-gray-900 text-right">
                                                            @if($item->default_unit_price)
                                                                RM {{ number_format($itemTotal, 2) }}
                                                            @else
                                                                <span class="text-gray-400">TBD</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            @if($sectionTotal > 0)
                                                <tfoot class="bg-gray-50">
                                                    <tr>
                                                        <td colspan="4" class="px-4 py-2 text-sm font-medium text-gray-900 text-right">Section Total:</td>
                                                        <td class="px-4 py-2 text-sm font-bold text-gray-900 text-right">RM {{ number_format($sectionTotal, 2) }}</td>
                                                    </tr>
                                                </tfoot>
                                            @endif
                                        </table>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 italic">No items defined for this section</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No sections defined</h3>
                        <p class="mt-1 text-sm text-gray-500">This template doesn't have any sections yet.</p>
                        @can('update', $serviceTemplate)
                            <div class="mt-6">
                                <a href="{{ route('service-templates.edit', $serviceTemplate) }}"
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Add Sections
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Template Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Template Information</h2>

                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Category</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $serviceTemplate->category->name ?? 'N/A' }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-700">Created By</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $serviceTemplate->createdBy->name ?? 'Unknown' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-700">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $serviceTemplate->created_at->format('M j, Y g:i A') }}
                        </dd>
                    </div>

                    @if($serviceTemplate->updated_at && $serviceTemplate->updated_at != $serviceTemplate->created_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-700">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $serviceTemplate->updated_at->format('M j, Y g:i A') }}
                            </dd>
                        </div>
                    @endif

                    @if($serviceTemplate->last_used_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-700">Last Used</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $serviceTemplate->last_used_at->diffForHumans() }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Applicable Teams -->
            @if($serviceTemplate->applicable_teams && count($serviceTemplate->applicable_teams) > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Applicable Teams</h2>
                    <div class="space-y-2">
                        @foreach($serviceTemplate->applicable_teams as $teamId)
                            @if($team = $teams->find($teamId))
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">{{ $team->name }}</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Usage Statistics -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Usage Statistics</h2>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Times Used</span>
                        <span class="text-sm font-medium text-gray-900">{{ $serviceTemplate->usage_count ?? 0 }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Total Sections</span>
                        <span class="text-sm font-medium text-gray-900">{{ $serviceTemplate->sections ? $serviceTemplate->sections->count() : 0 }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Total Items</span>
                        <span class="text-sm font-medium text-gray-900">
                            {{ $serviceTemplate->sections ? $serviceTemplate->sections->sum(function($section) { return $section->items ? $section->items->count() : 0; }) : 0 }}
                        </span>
                    </div>

                    @php
                        $templateTotal = 0;
                        if ($serviceTemplate->sections) {
                            foreach ($serviceTemplate->sections as $section) {
                                if ($section->items) {
                                    foreach ($section->items as $item) {
                                        $templateTotal += ($item->default_quantity ?? 1) * ($item->default_unit_price ?? 0);
                                    }
                                }
                            }
                        }
                    @endphp

                    @if($templateTotal > 0)
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Template Total</span>
                            <span class="text-sm font-bold text-gray-900">RM {{ number_format($templateTotal, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>

                <div class="space-y-3">
                    @can('update', $serviceTemplate)
                        @if($serviceTemplate->is_active)
                            <form method="POST" action="{{ route('service-templates.toggle-status', $serviceTemplate) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="w-full bg-yellow-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                    Deactivate Template
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('service-templates.toggle-status', $serviceTemplate) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="w-full bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    Activate Template
                                </button>
                            </form>
                        @endif
                    @endcan

                    @can('delete', $serviceTemplate)
                        <form method="POST" action="{{ route('service-templates.destroy', $serviceTemplate) }}"
                              onsubmit="return confirm('Are you sure you want to delete this template? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="w-full bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                Delete Template
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection