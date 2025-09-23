@extends('layouts.app')

@section('title', 'Lead Pipeline')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lead Pipeline') }}
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('leads.index') }}"
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                List View
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

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('leads.kanban') }}" class="flex items-center space-x-4">
                        <div class="flex-1">
                            <select name="team_id" 
                                    onchange="this.form.submit()"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">All Teams</option>
                                @foreach($filters['teams'] as $team)
                                    <option value="{{ $team->id }}" {{ request('team_id') == $team->id ? 'selected' : '' }}>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex-1">
                            <select name="assigned_to" 
                                    onchange="this.form.submit()"
                                    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">All Assignees</option>
                                @foreach($filters['assignees'] as $assignee)
                                    <option value="{{ $assignee->id }}" {{ request('assigned_to') == $assignee->id ? 'selected' : '' }}>
                                        {{ $assignee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if(request()->hasAny(['team_id', 'assigned_to']))
                            <a href="{{ route('leads.kanban') }}" 
                               class="text-gray-500 hover:text-gray-700">
                                Clear Filters
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Kanban Board -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6" id="kanban-board">
                @foreach($leadsByStatus as $status => $leads)
                    @php
                        $statusInfo = match($status) {
                            'NEW' => ['color' => 'blue', 'label' => 'New'],
                            'CONTACTED' => ['color' => 'yellow', 'label' => 'Contacted'],
                            'QUOTED' => ['color' => 'purple', 'label' => 'Quoted'],
                            'WON' => ['color' => 'green', 'label' => 'Won'],
                            'LOST' => ['color' => 'red', 'label' => 'Lost'],
                            default => ['color' => 'gray', 'label' => $status]
                        };
                    @endphp

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200" data-status="{{ $status }}">
                        <!-- Column Header -->
                        <div class="px-4 py-3 border-b border-gray-200 bg-{{ $statusInfo['color'] }}-50">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-{{ $statusInfo['color'] }}-800">
                                    {{ $statusInfo['label'] }}
                                </h3>
                                <span class="bg-{{ $statusInfo['color'] }}-100 text-{{ $statusInfo['color'] }}-800 text-xs font-medium px-2 py-1 rounded-full">
                                    {{ $leads->count() }}
                                </span>
                            </div>
                        </div>

                        <!-- Lead Cards -->
                        <div class="p-2 space-y-2 min-h-96" data-droppable="{{ $status }}">
                            @foreach($leads as $lead)
                                <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm hover:shadow-md transition-shadow cursor-move"
                                     data-lead-id="{{ $lead->id }}"
                                     data-status="{{ $lead->status }}"
                                     draggable="true">
                                    
                                    <!-- Lead Header -->
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-semibold text-gray-900 truncate">
                                                {{ $lead->name }}
                                            </h4>
                                            <p class="text-xs text-gray-500 truncate">
                                                {{ $lead->formatted_phone }}
                                            </p>
                                        </div>
                                        
                                        <!-- Urgency Badge -->
                                        @if($lead->urgency !== 'medium')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                {{ $lead->urgency === 'high' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                {{ ucfirst($lead->urgency) }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Lead Info -->
                                    @if($lead->requirements)
                                        <p class="text-xs text-gray-600 mb-2 line-clamp-2">
                                            {{ Str::limit($lead->requirements, 60) }}
                                        </p>
                                    @endif

                                    <!-- Estimated Value -->
                                    @if($lead->estimated_value)
                                        <div class="text-xs text-gray-500 mb-2">
                                            Est. Value: <span class="font-medium text-gray-700">RM {{ number_format($lead->estimated_value, 0) }}</span>
                                        </div>
                                    @endif

                                    <!-- Assignment Info -->
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        <div>
                                            @if($lead->assignedTo)
                                                <span class="inline-flex items-center">
                                                    <span class="w-2 h-2 bg-green-400 rounded-full mr-1"></span>
                                                    {{ Str::limit($lead->assignedTo->name, 10) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">Unassigned</span>
                                            @endif
                                        </div>
                                        
                                        <div>
                                            {{ $lead->created_at->format('M j') }}
                                        </div>
                                    </div>

                                    <!-- Lead Score -->
                                    @if($lead->lead_score > 0)
                                        <div class="mt-2">
                                            <div class="flex items-center">
                                                <div class="flex-1 bg-gray-200 rounded-full h-1">
                                                    <div class="bg-{{ $lead->lead_score >= 70 ? 'green' : ($lead->lead_score >= 40 ? 'yellow' : 'red') }}-500 h-1 rounded-full" 
                                                         style="width: {{ $lead->lead_score }}%"></div>
                                                </div>
                                                <span class="ml-2 text-xs text-gray-500">{{ $lead->lead_score }}%</span>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Quick Actions -->
                                    <div class="mt-3 pt-2 border-t border-gray-100">
                                        <div class="flex items-center justify-between">
                                            @can('view', $lead)
                                                <a href="{{ route('leads.show', $lead) }}" 
                                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                                    View Details
                                                </a>
                                            @endcan
                                            
                                            @if($lead->needsFollowUp())
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                    Follow-up Due
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if($leads->count() === 0)
                                <div class="text-center py-8 text-gray-400">
                                    <svg class="mx-auto h-8 w-8 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <p class="text-sm">No leads</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        // Drag and Drop Functionality
        let draggedElement = null;

        document.addEventListener('DOMContentLoaded', function() {
            const leadCards = document.querySelectorAll('[data-lead-id]');
            const dropZones = document.querySelectorAll('[data-droppable]');

            // Add drag event listeners to lead cards
            leadCards.forEach(card => {
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
            });

            // Add drop event listeners to columns
            dropZones.forEach(zone => {
                zone.addEventListener('dragover', handleDragOver);
                zone.addEventListener('drop', handleDrop);
                zone.addEventListener('dragenter', handleDragEnter);
                zone.addEventListener('dragleave', handleDragLeave);
            });
        });

        function handleDragStart(e) {
            draggedElement = this;
            this.style.opacity = '0.5';
        }

        function handleDragEnd(e) {
            this.style.opacity = '1';
            draggedElement = null;
        }

        function handleDragOver(e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            return false;
        }

        function handleDragEnter(e) {
            this.classList.add('bg-blue-50', 'border-blue-200');
        }

        function handleDragLeave(e) {
            this.classList.remove('bg-blue-50', 'border-blue-200');
        }

        function handleDrop(e) {
            if (e.stopPropagation) {
                e.stopPropagation();
            }

            this.classList.remove('bg-blue-50', 'border-blue-200');

            if (draggedElement !== null) {
                const leadId = draggedElement.dataset.leadId;
                const newStatus = this.dataset.droppable;
                const currentStatus = draggedElement.dataset.status;

                if (newStatus !== currentStatus) {
                    // Update the lead status via AJAX
                    updateLeadStatus(leadId, newStatus);
                    
                    // Move the element to new column
                    this.appendChild(draggedElement);
                    draggedElement.dataset.status = newStatus;
                }
            }

            return false;
        }

        function updateLeadStatus(leadId, newStatus) {
            fetch(`/leads/${leadId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message (you can customize this)
                    console.log('Lead status updated successfully');
                } else {
                    // Handle error
                    console.error('Error updating lead status');
                    // Optionally reload the page or revert the change
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                location.reload();
            });
        }
    </script>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        [data-droppable].bg-blue-50 {
            background-color: rgba(239, 246, 255, 0.5);
            border-color: rgba(147, 197, 253, 0.5);
        }
    </style>
@endsection