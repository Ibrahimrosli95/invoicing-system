@extends('layouts.app')

@section('title', 'Leads')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leads') }}
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('leads.kanban') }}"
               class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                Kanban View
            </a>
            @can('create', App\Models\Lead::class)
                <a href="{{ route('leads.create') }}"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add Lead
                </a>
            @endcan
        </div>
    </div>
</div>
@endsection

@section('content')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('leads.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div>
                                <x-input-label for="search" :value="__('Search')" />
                                <x-text-input id="search" 
                                            name="search" 
                                            type="text" 
                                            :value="request('search')" 
                                            placeholder="Name, phone, email..." 
                                            class="mt-1 block w-full" />
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" 
                                        name="status"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    <option value="">All Statuses</option>
                                    @foreach($filters['statuses'] as $value => $label)
                                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Team Filter -->
                            <div>
                                <x-input-label for="team_id" :value="__('Team')" />
                                <select id="team_id" 
                                        name="team_id"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    <option value="">All Teams</option>
                                    @foreach($filters['teams'] as $team)
                                        <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>
                                            {{ $team->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Assignee Filter -->
                            <div>
                                <x-input-label for="assigned_to" :value="__('Assigned To')" />
                                <select id="assigned_to" 
                                        name="assigned_to"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    <option value="">All Assignees</option>
                                    @foreach($filters['assignees'] as $assignee)
                                        <option value="{{ $assignee->id }}" {{ request('assigned_to') == $assignee->id ? 'selected' : '' }}>
                                            {{ $assignee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Source Filter -->
                            <div>
                                <x-input-label for="source" :value="__('Source')" />
                                <select id="source" 
                                        name="source"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    <option value="">All Sources</option>
                                    @foreach($filters['sources'] as $value => $label)
                                        <option value="{{ $value }}" {{ request('source') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Urgency Filter -->
                            <div>
                                <x-input-label for="urgency" :value="__('Urgency')" />
                                <select id="urgency" 
                                        name="urgency"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    <option value="">All Urgency Levels</option>
                                    @foreach($filters['urgencyLevels'] as $value => $label)
                                        <option value="{{ $value }}" {{ request('urgency') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Qualified Filter -->
                            <div>
                                <x-input-label for="qualified" :value="__('Qualified')" />
                                <select id="qualified" 
                                        name="qualified"
                                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full">
                                    <option value="">All Leads</option>
                                    <option value="yes" {{ request('qualified') == 'yes' ? 'selected' : '' }}>
                                        Qualified Only
                                    </option>
                                    <option value="no" {{ request('qualified') == 'no' ? 'selected' : '' }}>
                                        Not Qualified
                                    </option>
                                </select>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-end space-x-2">
                                <button type="submit" 
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Filter
                                </button>
                                @if(request()->hasAny(['search', 'status', 'team_id', 'assigned_to', 'source', 'urgency', 'qualified']))
                                    <a href="{{ route('leads.index') }}" 
                                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        Clear
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Leads List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($leads->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                               class="flex items-center space-x-1 hover:text-gray-700">
                                                <span>Lead Name</span>
                                                @if(request('sort') === 'name')
                                                    <span class="text-blue-500">
                                                        @if(request('direction') === 'asc') ↑ @else ↓ @endif
                                                    </span>
                                                @endif
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Contact
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('sort') === 'status' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                               class="flex items-center space-x-1 hover:text-gray-700">
                                                <span>Status</span>
                                                @if(request('sort') === 'status')
                                                    <span class="text-blue-500">
                                                        @if(request('direction') === 'asc') ↑ @else ↓ @endif
                                                    </span>
                                                @endif
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Team/Assignee
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'estimated_value', 'direction' => request('sort') === 'estimated_value' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                               class="flex items-center space-x-1 hover:text-gray-700">
                                                <span>Est. Value</span>
                                                @if(request('sort') === 'estimated_value')
                                                    <span class="text-blue-500">
                                                        @if(request('direction') === 'asc') ↑ @else ↓ @endif
                                                    </span>
                                                @endif
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') === 'created_at' && request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                                               class="flex items-center space-x-1 hover:text-gray-700">
                                                <span>Created</span>
                                                @if(request('sort') === 'created_at')
                                                    <span class="text-blue-500">
                                                        @if(request('direction') === 'asc') ↑ @else ↓ @endif
                                                    </span>
                                                @endif
                                            </a>
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($leads as $lead)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $lead->name }}
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            {{ $lead->source ? ucfirst(str_replace('_', ' ', $lead->source)) : '' }}
                                                            @if($lead->urgency !== 'medium')
                                                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                                    {{ $lead->urgency === 'high' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                                    {{ ucfirst($lead->urgency) }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $lead->formatted_phone }}</div>
                                                @if($lead->email)
                                                    <div class="text-sm text-gray-500">{{ $lead->email }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ 'bg-' . $lead->getStatusColor() . '-100 text-' . $lead->getStatusColor() . '-800' }}">
                                                    {{ ucfirst(strtolower($lead->status)) }}
                                                </span>
                                                @if($lead->is_qualified)
                                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Qualified
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div>{{ $lead->team?->name ?: 'No team' }}</div>
                                                <div class="text-gray-500">{{ $lead->assignedTo?->name ?: 'Unassigned' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($lead->estimated_value)
                                                    RM {{ number_format($lead->estimated_value, 0) }}
                                                @else
                                                    <span class="text-gray-400">Not set</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $lead->created_at->format('M d, Y') }}</div>
                                                <div class="text-sm text-gray-500">{{ $lead->created_at->diffForHumans() }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end space-x-2">
                                                    @can('view', $lead)
                                                        <a href="{{ route('leads.show', $lead) }}" 
                                                           class="text-blue-600 hover:text-blue-900">
                                                            View
                                                        </a>
                                                    @endcan
                                                    @can('update', $lead)
                                                        <a href="{{ route('leads.edit', $lead) }}" 
                                                           class="text-indigo-600 hover:text-indigo-900">
                                                            Edit
                                                        </a>
                                                    @endcan
                                                    @can('delete', $lead)
                                                        <form action="{{ route('leads.destroy', $lead) }}" 
                                                              method="POST" 
                                                              class="inline"
                                                              onsubmit="return confirm('Are you sure you want to delete this lead?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="text-red-600 hover:text-red-900">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $leads->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No leads found</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                @if(request()->hasAny(['search', 'status', 'team_id', 'assigned_to', 'source', 'urgency', 'qualified']))
                                    No leads match your current filters. Try adjusting your search criteria.
                                @else
                                    Get started by creating your first lead.
                                @endif
                            </p>
                            @can('create', App\Models\Lead::class)
                                <div class="mt-6">
                                    <a href="{{ route('leads.create') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        Add Lead
                                    </a>
                                </div>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection