<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Organization Structure') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('organization.chart') }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    View Org Chart
                </a>
                @can('viewAny', App\Models\Team::class)
                    <a href="{{ route('teams.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Manage Teams
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Company Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center space-x-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $company->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $company->email }}</p>
                            @if($company->phone)
                                <p class="text-sm text-gray-500">{{ $company->phone }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <dt class="text-sm font-medium text-gray-500">Total Users</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_users'] }}</dd>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <dt class="text-sm font-medium text-gray-500">Active Users</dt>
                        <dd class="mt-1 text-3xl font-semibold text-green-600">{{ $stats['active_users'] }}</dd>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <dt class="text-sm font-medium text-gray-500">Total Teams</dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_teams'] }}</dd>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <dt class="text-sm font-medium text-gray-500">Managers</dt>
                        <dd class="mt-1 text-3xl font-semibold text-blue-600">{{ $stats['managers'] }}</dd>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <dt class="text-sm font-medium text-gray-500">Coordinators</dt>
                        <dd class="mt-1 text-3xl font-semibold text-yellow-600">{{ $stats['coordinators'] }}</dd>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <dt class="text-sm font-medium text-gray-500">Executives</dt>
                        <dd class="mt-1 text-3xl font-semibold text-purple-600">{{ $stats['executives'] }}</dd>
                    </div>
                </div>
            </div>

            <!-- Company Leadership -->
            @if($company->users->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Company Leadership</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($company->users as $user)
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-12 w-12 rounded-full bg-blue-200 flex items-center justify-center">
                                                <span class="text-sm font-medium text-blue-700">
                                                    {{ substr($user->name, 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                            @foreach($user->roles as $role)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1">
                                                    {{ str_replace('_', ' ', title_case($role->name)) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Teams Overview -->
            @if($company->teams->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Teams Overview</h3>
                        <div class="space-y-6">
                            @foreach($company->teams as $team)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h4 class="text-lg font-medium text-gray-900">{{ $team->name }}</h4>
                                            <p class="text-sm text-gray-500">{{ $team->description ?: 'No description' }}</p>
                                            @if($team->territory)
                                                <p class="text-sm text-gray-500">Territory: {{ $team->territory }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            @if($team->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Inactive
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Team Leadership -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <h5 class="font-medium text-gray-700 mb-2">Team Manager</h5>
                                            @if($team->manager)
                                                <div class="flex items-center space-x-2">
                                                    <div class="h-8 w-8 rounded-full bg-blue-200 flex items-center justify-center">
                                                        <span class="text-xs font-medium text-blue-700">
                                                            {{ substr($team->manager->name, 0, 2) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $team->manager->name }}</p>
                                                        <p class="text-xs text-gray-500">{{ $team->manager->email }}</p>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="text-sm text-gray-500">Not assigned</p>
                                            @endif
                                        </div>

                                        <div>
                                            <h5 class="font-medium text-gray-700 mb-2">Team Coordinator</h5>
                                            @if($team->coordinator)
                                                <div class="flex items-center space-x-2">
                                                    <div class="h-8 w-8 rounded-full bg-yellow-200 flex items-center justify-center">
                                                        <span class="text-xs font-medium text-yellow-700">
                                                            {{ substr($team->coordinator->name, 0, 2) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $team->coordinator->name }}</p>
                                                        <p class="text-xs text-gray-500">{{ $team->coordinator->email }}</p>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="text-sm text-gray-500">Not assigned</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Team Members -->
                                    @if($team->users->count() > 0)
                                        <div>
                                            <h5 class="font-medium text-gray-700 mb-2">Team Members ({{ $team->users->count() }})</h5>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($team->users as $user)
                                                    <div class="flex items-center space-x-2 bg-gray-50 px-3 py-1 rounded-full">
                                                        <div class="h-6 w-6 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-gray-700">
                                                                {{ substr($user->name, 0, 1) }}
                                                            </span>
                                                        </div>
                                                        <span class="text-sm text-gray-900">{{ $user->name }}</span>
                                                        @if($team->manager_id === $user->id)
                                                            <span class="text-xs text-blue-600">(Manager)</span>
                                                        @elseif($team->coordinator_id === $user->id)
                                                            <span class="text-xs text-yellow-600">(Coordinator)</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500">No members assigned</p>
                                    @endif

                                    @can('view', $team)
                                        <div class="mt-4 flex justify-end">
                                            <a href="{{ route('teams.show', $team) }}" 
                                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                View Team Details →
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No teams created</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first team.</p>
                        @can('create', App\Models\Team::class)
                            <div class="mt-6">
                                <a href="{{ route('teams.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Create Team
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>