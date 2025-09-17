<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $team->name }}
            </h2>
            <div class="flex space-x-2">
                @can('update', $team)
                    <a href="{{ route('teams.settings', $team) }}" 
                       class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Settings
                    </a>
                    <a href="{{ route('teams.edit', $team) }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Edit Team
                    </a>
                @endcan
                <a href="{{ route('teams.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Teams
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Team Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Team Overview</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Status -->
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if($team->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactive
                                    </span>
                                @endif
                            </dd>
                        </div>

                        <!-- Total Members -->
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500">Total Members</dt>
                            <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total_users'] }}</dd>
                        </div>

                        <!-- Active Members -->
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500">Active Members</dt>
                            <dd class="mt-1 text-2xl font-semibold text-green-600">{{ $stats['active_users'] }}</dd>
                        </div>

                        <!-- Target Revenue -->
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <dt class="text-sm font-medium text-gray-500">Target Revenue</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">
                                @if($team->target_revenue)
                                    RM {{ number_format($team->target_revenue, 2) }}
                                @else
                                    Not set
                                @endif
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Details -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Team Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Basic Information</h4>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $team->description ?: 'No description provided' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Territory</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $team->territory ?: 'Not specified' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $team->created_at->format('M d, Y') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-900 mb-3">Leadership</h4>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Manager</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $team->manager?->name ?: 'Not assigned' }}
                                        @if($team->manager?->email)
                                            <span class="text-gray-500">({{ $team->manager->email }})</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Coordinator</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $team->coordinator?->name ?: 'Not assigned' }}
                                        @if($team->coordinator?->email)
                                            <span class="text-gray-500">({{ $team->coordinator->email }})</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Members -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Team Members</h3>
                        @can('update', $team)
                            <a href="{{ route('teams.members', $team) }}" 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                Manage Members
                            </a>
                        @endcan
                    </div>

                    @if($team->users->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($team->users as $user)
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ substr($user->name, 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $user->name }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ $user->email }}
                                            </p>
                                            @if($user->title)
                                                <p class="text-xs text-gray-400">
                                                    {{ $user->title }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="flex-shrink-0">
                                            @if($user->is_active)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Inactive
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            <h4 class="mt-2 text-sm font-medium text-gray-900">No team members</h4>
                            <p class="mt-1 text-sm text-gray-500">This team doesn't have any members assigned yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>