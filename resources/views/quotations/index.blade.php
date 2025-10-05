@extends('layouts.app')

@section('title', 'Quotations')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quotations') }}
        </h2>
        <div class="flex space-x-2">
            @can('create', App\Models\Quotation::class)
                <!-- Dropdown for Create Quotation Options -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center space-x-2">
                        <span>Create Quotation</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="open"
                         @click.away="open = false"
                         x-transition
                         class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg border border-gray-200 z-50">
                        <div class="py-1">
                            <a href="{{ route('quotations.product-builder') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 border-l-4 border-blue-500">
                                <div class="font-medium text-blue-700">📄 Product Quotation Builder</div>
                                <div class="text-xs text-blue-600">NEW: Document-style builder for product quotations</div>
                            </a>
                            <a href="{{ route('quotations.service-builder') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 border-l-4 border-green-500">
                                <div class="font-medium text-green-700">🛠️ Service Quotation Builder</div>
                                <div class="text-xs text-green-600">NEW: Section-based builder for service quotations</div>
                            </a>
                            <div class="border-t border-gray-200 my-1"></div>
                            <a href="{{ route('quotations.create') }}"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <div class="font-medium">Basic Quotation Form</div>
                                <div class="text-xs text-gray-500">Simple form-based quotation creation</div>
                            </a>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection

@section('content')

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('quotations.index') }}" class="space-y-4">
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                                <!-- Search -->
                                <div class="md:col-span-2">
                                    <x-text-input 
                                        name="search" 
                                        type="text" 
                                        placeholder="Search quotations..."
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

                                <!-- Type Filter -->
                                <div>
                                    <select name="type" 
                                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">All Types</option>
                                        @foreach($filters['types'] as $value => $label)
                                            <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>
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

                                <!-- Actions -->
                                <div class="flex space-x-2">
                                    <button type="submit" 
                                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        Filter
                                    </button>
                                    @if(request()->hasAny(['search', 'status', 'type', 'team_id', 'customer_segment_id', 'assigned_to', 'date_from', 'date_to']))
                                        <a href="{{ route('quotations.index') }}" 
                                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                            Clear
                                        </a>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Second Row - Customer Segment Filter -->
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                                <div class="md:col-span-2">
                                    <select name="customer_segment_id" 
                                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">All Customer Segments</option>
                                        @foreach($filters['customer_segments'] as $segment)
                                            <option value="{{ $segment->id }}" {{ request('customer_segment_id') == $segment->id ? 'selected' : '' }}>
                                                {{ $segment->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-4">
                                    <!-- Empty space for alignment -->
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quotations Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'number', 'direction' => request('sort') === 'number' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center hover:text-gray-700">
                                        Quotation #
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
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'total', 'direction' => request('sort') === 'total' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center hover:text-gray-700">
                                        Total
                                        @if(request('sort') === 'total')
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
                                    Assigned To
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'valid_until', 'direction' => request('sort') === 'valid_until' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                       class="flex items-center hover:text-gray-700">
                                        Valid Until
                                        @if(request('sort') === 'valid_until')
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
                            @forelse($quotations as $quotation)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $quotation->number }}
                                        </div>
                                        @if($quotation->lead)
                                            <div class="text-xs text-gray-500">
                                                from Lead
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $quotation->customer_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $quotation->customer_phone }}
                                        </div>
                                        @if($quotation->customer_email)
                                            <div class="text-xs text-gray-400">
                                                {{ $quotation->customer_email }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $quotation->getStatusBadgeColor() }}">
                                            {{ $quotation->status }}
                                        </span>
                                        @if($quotation->isExpired() && in_array($quotation->status, ['SENT', 'VIEWED']))
                                            <div class="text-xs text-red-500 mt-1">Expired</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            RM {{ number_format($quotation->total, 2) }}
                                        </div>
                                        @if($quotation->items_count ?? $quotation->items()->count())
                                            <div class="text-xs text-gray-500">
                                                {{ $quotation->items_count ?? $quotation->items()->count() }} items
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($quotation->assignedTo)
                                            <div class="text-sm text-gray-900">
                                                {{ $quotation->assignedTo->name }}
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">Unassigned</span>
                                        @endif
                                        @if($quotation->team)
                                            <div class="text-xs text-gray-500">
                                                {{ $quotation->team->name }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($quotation->valid_until)
                                            <div class="text-sm text-gray-900">
                                                @displayDate($quotation->valid_until)
                                            </div>
                                            @if($quotation->isExpired())
                                                <div class="text-xs text-red-500">
                                                    Expired
                                                </div>
                                            @else
                                                <div class="text-xs text-gray-500">
                                                    {{ $quotation->valid_until->diffForHumans() }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-sm text-gray-400">No expiry</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            @can('view', $quotation)
                                                <a href="{{ route('quotations.show', $quotation) }}" 
                                                   class="text-blue-600 hover:text-blue-900">View</a>
                                                <a href="{{ route('quotations.pdf', $quotation) }}" 
                                                   class="text-red-600 hover:text-red-900" title="Download PDF">PDF</a>
                                            @endcan
                                            @can('update', $quotation)
                                                @if($quotation->canBeEdited())
                                                    <a href="{{ route('quotations.edit', $quotation) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                @endif
                                            @endcan
                                            @can('delete', $quotation)
                                                @if($quotation->canBeEdited())
                                                    <form method="POST" action="{{ route('quotations.destroy', $quotation) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                onclick="return confirm('Are you sure you want to delete this quotation?')"
                                                                class="text-red-600 hover:text-red-900">Delete</button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-lg font-medium">No quotations found</p>
                                            <p class="text-sm">Create your first quotation to get started.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($quotations->hasPages())
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $quotations->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>

            <!-- Summary Stats -->
            @if($quotations->isNotEmpty())
                <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4">
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $quotations->total() }}
                            </div>
                            <div class="text-sm text-gray-500">Total Quotations</div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4">
                            <div class="text-2xl font-bold text-green-600">
                                RM {{ number_format($quotations->sum('total'), 0) }}
                            </div>
                            <div class="text-sm text-gray-500">Total Value (Current Page)</div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4">
                            <div class="text-2xl font-bold text-blue-600">
                                {{ $quotations->where('status', 'SENT')->count() + $quotations->where('status', 'VIEWED')->count() }}
                            </div>
                            <div class="text-sm text-gray-500">Pending Response</div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4">
                            <div class="text-2xl font-bold text-purple-600">
                                {{ $quotations->where('status', 'ACCEPTED')->count() }}
                            </div>
                            <div class="text-sm text-gray-500">Accepted</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection