@extends('layouts.app')

@section('title', 'Lead Details')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Lead Details') }}: {{ $lead->name }}
            </h2>
            <p class="text-gray-600 mt-1">
                Created {{ $lead->created_at->format('M j, Y') }} •
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                    {{ $lead->status === 'NEW' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $lead->status === 'CONTACTED' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $lead->status === 'QUOTED' ? 'bg-purple-100 text-purple-800' : '' }}
                    {{ $lead->status === 'WON' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $lead->status === 'LOST' ? 'bg-red-100 text-red-800' : '' }}">
                    {{ ucfirst($lead->status) }}
                </span>
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('leads.index') }}"
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Leads
            </a>
            @can('create', App\Models\Quotation::class)
                @if(in_array($lead->status, ['CONTACTED', 'NEW']))
                    <a href="{{ route('leads.convert', $lead) }}"
                       class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Convert to Quotation
                    </a>
                @endif
            @endcan
            @can('update', $lead)
                <a href="{{ route('leads.edit', $lead) }}"
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Lead
                </a>
            @endcan
        </div>
    </div>
</div>
@endsection

@section('content')

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Lead Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Contact Information Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Contact Information</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Name</label>
                                    <p class="mt-1 text-gray-900 font-semibold">{{ $lead->name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Phone</label>
                                    <p class="mt-1 text-gray-900">
                                        <a href="tel:{{ $lead->phone }}" class="hover:text-blue-600">
                                            {{ $lead->formatted_phone }}
                                        </a>
                                    </p>
                                </div>
                                @if($lead->email)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Email</label>
                                    <p class="mt-1 text-gray-900">
                                        <a href="mailto:{{ $lead->email }}" class="hover:text-blue-600">
                                            {{ $lead->email }}
                                        </a>
                                    </p>
                                </div>
                                @endif
                                @if($lead->address || $lead->city || $lead->state || $lead->postal_code)
                                <div class="md:col-span-2">
                                    <label class="text-sm font-medium text-gray-500">Address</label>
                                    <p class="mt-1 text-gray-900">
                                        @if($lead->address)
                                            {{ $lead->address }}<br>
                                        @endif
                                        @if($lead->city || $lead->state || $lead->postal_code)
                                            {{ implode(' ', array_filter([$lead->postal_code, $lead->city, $lead->state])) }}
                                        @endif
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Lead Details Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Lead Details</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Source</label>
                                    <p class="mt-1 text-gray-900 capitalize">{{ str_replace('_', ' ', $lead->source) }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Urgency</label>
                                    <p class="mt-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            {{ $lead->urgency === 'high' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $lead->urgency === 'medium' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $lead->urgency === 'low' ? 'bg-green-100 text-green-800' : '' }}">
                                            {{ ucfirst($lead->urgency) }}
                                        </span>
                                    </p>
                                </div>
                                @if($lead->estimated_value)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Estimated Value</label>
                                    <p class="mt-1 text-gray-900 font-semibold">RM {{ number_format($lead->estimated_value, 2) }}</p>
                                </div>
                                @endif
                                @if($lead->lead_score > 0)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Lead Score</label>
                                    <div class="mt-1 flex items-center">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-{{ $lead->lead_score >= 70 ? 'green' : ($lead->lead_score >= 40 ? 'yellow' : 'red') }}-500 h-2 rounded-full" 
                                                 style="width: {{ $lead->lead_score }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-600">{{ $lead->lead_score }}%</span>
                                    </div>
                                </div>
                                @endif
                            </div>
                            
                            @if($lead->requirements)
                            <div class="mt-6">
                                <label class="text-sm font-medium text-gray-500">Requirements</label>
                                <p class="mt-1 text-gray-900 whitespace-pre-wrap">{{ $lead->requirements }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Activity Timeline -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Activity Timeline</h3>
                                @can('addActivity', $lead)
                                    <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        + Add Activity
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="p-6">
                            @if($lead->activities->count() > 0)
                                <div class="space-y-4">
                                    @foreach($lead->activities as $activity)
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-medium
                                                    {{ $activity->getTypeColor() }}">
                                                    {{ $activity->getTypeIcon() }}
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-sm font-medium text-gray-900">{{ $activity->title }}</p>
                                                    <p class="text-sm text-gray-500">{{ $activity->created_at->format('M j, H:i') }}</p>
                                                </div>
                                                @if($activity->description)
                                                    <p class="mt-1 text-sm text-gray-600">{{ $activity->description }}</p>
                                                @endif
                                                <div class="mt-1 flex items-center space-x-2 text-xs text-gray-500">
                                                    <span>{{ $activity->user->name }}</span>
                                                    @if($activity->outcome)
                                                        <span>•</span>
                                                        <span class="capitalize">{{ str_replace('_', ' ', $activity->outcome) }}</span>
                                                    @endif
                                                    @if($activity->duration_minutes)
                                                        <span>•</span>
                                                        <span>{{ $activity->formatted_duration }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-400">
                                    <svg class="mx-auto h-8 w-8 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-sm">No activities yet</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Assignment Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Assignment</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Team</label>
                                <p class="mt-1 text-gray-900">
                                    {{ $lead->team ? $lead->team->name : 'No Team' }}
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Assigned To</label>
                                <p class="mt-1 text-gray-900">
                                    @if($lead->assignedTo)
                                        <span class="inline-flex items-center">
                                            <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                                            {{ $lead->assignedTo->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">Unassigned</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Qualified</label>
                                <p class="mt-1">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        {{ $lead->is_qualified ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $lead->is_qualified ? 'Yes' : 'No' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Update Card -->
                    @can('update', $lead)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <form method="POST" action="{{ route('leads.update', $lead) }}">
                                @csrf
                                @method('PATCH')
                                <div class="space-y-4">
                                    <div>
                                        <label for="status" class="text-sm font-medium text-gray-500">Update Status</label>
                                        <select name="status" id="status" 
                                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="NEW" {{ $lead->status === 'NEW' ? 'selected' : '' }}>New</option>
                                            <option value="CONTACTED" {{ $lead->status === 'CONTACTED' ? 'selected' : '' }}>Contacted</option>
                                            <option value="QUOTED" {{ $lead->status === 'QUOTED' ? 'selected' : '' }}>Quoted</option>
                                            <option value="WON" {{ $lead->status === 'WON' ? 'selected' : '' }}>Won</option>
                                            <option value="LOST" {{ $lead->status === 'LOST' ? 'selected' : '' }}>Lost</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_qualified" value="1" 
                                                   {{ $lead->is_qualified ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-600">Mark as Qualified</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Keep other fields unchanged -->
                                <input type="hidden" name="name" value="{{ $lead->name }}">
                                <input type="hidden" name="phone" value="{{ $lead->phone }}">
                                <input type="hidden" name="email" value="{{ $lead->email }}">
                                <input type="hidden" name="address" value="{{ $lead->address }}">
                                <input type="hidden" name="city" value="{{ $lead->city }}">
                                <input type="hidden" name="state" value="{{ $lead->state }}">
                                <input type="hidden" name="postal_code" value="{{ $lead->postal_code }}">
                                <input type="hidden" name="source" value="{{ $lead->source }}">
                                <input type="hidden" name="urgency" value="{{ $lead->urgency }}">
                                <input type="hidden" name="requirements" value="{{ $lead->requirements }}">
                                <input type="hidden" name="estimated_value" value="{{ $lead->estimated_value }}">
                                <input type="hidden" name="team_id" value="{{ $lead->team_id }}">
                                <input type="hidden" name="assigned_to" value="{{ $lead->assigned_to }}">
                                <input type="hidden" name="notes" value="{{ $lead->notes }}">
                                
                                <div class="mt-4">
                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Update
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endcan

                    <!-- Follow-up Card -->
                    @if($lead->needsFollowUp())
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-orange-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm font-medium text-orange-800">Follow-up Required</span>
                        </div>
                        <p class="mt-1 text-sm text-orange-700">This lead requires follow-up action.</p>
                    </div>
                    @endif

                    <!-- Quotations Card -->
                    @if($lead->hasQuotations())
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Quotations</h3>
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">
                                    {{ $lead->quotations->count() }}
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                @foreach($lead->quotations->take(5) as $quotation)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <a href="{{ route('quotations.show', $quotation) }}" 
                                                   class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                                    {{ $quotation->number }}
                                                </a>
                                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    {{ $quotation->status === 'DRAFT' ? 'bg-gray-100 text-gray-800' : '' }}
                                                    {{ $quotation->status === 'SENT' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $quotation->status === 'VIEWED' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $quotation->status === 'ACCEPTED' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $quotation->status === 'REJECTED' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ $quotation->status === 'EXPIRED' ? 'bg-gray-100 text-gray-800' : '' }}
                                                    {{ $quotation->status === 'CONVERTED' ? 'bg-purple-100 text-purple-800' : '' }}">
                                                    {{ ucfirst(strtolower($quotation->status)) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $quotation->created_at->format('M j, Y') }} • RM {{ number_format($quotation->total, 2) }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            @if($quotation->status === 'ACCEPTED')
                                                <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                                
                                @if($lead->quotations->count() > 5)
                                    <div class="text-center pt-2">
                                        <a href="{{ route('quotations.index', ['search' => $lead->name]) }}" 
                                           class="text-sm text-blue-600 hover:text-blue-800">
                                            View all {{ $lead->quotations->count() }} quotations →
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection