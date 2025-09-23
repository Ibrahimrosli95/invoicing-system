@extends('layouts.app')

@section('title', 'Organization Chart')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Organization Chart') }}
        </h2>
        <div class="flex space-x-2">
            <a href="{{ route('organization.index') }}"
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                List View
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div id="org-chart" class="overflow-x-auto">
                        <!-- Company Level -->
                        <div class="flex flex-col items-center space-y-8">
                            <div class="org-node company-node">
                                <div class="bg-blue-600 text-white p-4 rounded-lg shadow-lg text-center min-w-48">
                                    <h3 class="font-bold text-lg">{{ $hierarchy['name'] }}</h3>
                                    <p class="text-sm opacity-90">Company</p>
                                </div>
                            </div>

                            @if(count($hierarchy['children']) > 0)
                                <!-- Vertical connector -->
                                <div class="w-px h-8 bg-gray-300"></div>

                                <!-- Managers Level -->
                                <div class="flex flex-wrap justify-center gap-12">
                                    @foreach($hierarchy['children'] as $child)
                                        <div class="flex flex-col items-center space-y-4">
                                            @if($child['type'] === 'manager')
                                                <!-- Manager Node -->
                                                <div class="org-node manager-node">
                                                    <div class="bg-blue-500 text-white p-3 rounded-lg shadow-md text-center min-w-44">
                                                        <h4 class="font-semibold">{{ $child['name'] }}</h4>
                                                        <p class="text-xs opacity-90">{{ $child['title'] }}</p>
                                                        <p class="text-xs opacity-75">{{ $child['email'] }}</p>
                                                    </div>
                                                </div>

                                                @if(count($child['children']) > 0)
                                                    <!-- Vertical connector -->
                                                    <div class="w-px h-6 bg-gray-300"></div>

                                                    <!-- Teams under this manager -->
                                                    <div class="flex flex-wrap justify-center gap-8">
                                                        @foreach($child['children'] as $team)
                                                            <div class="flex flex-col items-center space-y-4">
                                                                <!-- Team Node -->
                                                                <div class="org-node team-node">
                                                                    <div class="bg-green-500 text-white p-3 rounded-lg shadow-md text-center min-w-40">
                                                                        <h5 class="font-semibold">{{ $team['name'] }}</h5>
                                                                        <p class="text-xs opacity-90">Team</p>
                                                                        @if($team['territory'])
                                                                            <p class="text-xs opacity-75">{{ $team['territory'] }}</p>
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                @if(count($team['children']) > 0)
                                                                    <!-- Vertical connector -->
                                                                    <div class="w-px h-4 bg-gray-300"></div>

                                                                    <!-- Team members/coordinators -->
                                                                    <div class="flex flex-wrap justify-center gap-4">
                                                                        @foreach($team['children'] as $member)
                                                                            <div class="flex flex-col items-center">
                                                                                @if($member['type'] === 'coordinator')
                                                                                    <!-- Coordinator Node -->
                                                                                    <div class="org-node coordinator-node mb-2">
                                                                                        <div class="bg-yellow-500 text-white p-2 rounded-lg shadow text-center min-w-36">
                                                                                            <h6 class="font-medium text-sm">{{ $member['name'] }}</h6>
                                                                                            <p class="text-xs opacity-90">Coordinator</p>
                                                                                            <p class="text-xs opacity-75 truncate">{{ Str::limit($member['email'], 20) }}</p>
                                                                                        </div>
                                                                                    </div>

                                                                                    @if(count($member['children']) > 0)
                                                                                        <!-- Members under coordinator -->
                                                                                        <div class="w-px h-3 bg-gray-300 mb-2"></div>
                                                                                        <div class="flex flex-wrap justify-center gap-2">
                                                                                            @foreach($member['children'] as $teamMember)
                                                                                                <div class="org-node member-node">
                                                                                                    <div class="bg-purple-500 text-white p-2 rounded shadow text-center min-w-32">
                                                                                                        <h6 class="font-medium text-xs">{{ Str::limit($teamMember['name'], 15) }}</h6>
                                                                                                        <p class="text-xs opacity-90">{{ $teamMember['title'] }}</p>
                                                                                                    </div>
                                                                                                </div>
                                                                                            @endforeach
                                                                                        </div>
                                                                                    @endif
                                                                                @else
                                                                                    <!-- Direct team member -->
                                                                                    <div class="org-node member-node">
                                                                                        <div class="bg-purple-500 text-white p-2 rounded shadow text-center min-w-32">
                                                                                            <h6 class="font-medium text-xs">{{ Str::limit($member['name'], 15) }}</h6>
                                                                                            <p class="text-xs opacity-90">{{ $member['title'] }}</p>
                                                                                        </div>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @elseif($child['type'] === 'team')
                                                <!-- Team directly under company (no manager) -->
                                                <div class="org-node team-node">
                                                    <div class="bg-green-500 text-white p-3 rounded-lg shadow-md text-center min-w-40">
                                                        <h5 class="font-semibold">{{ $child['name'] }}</h5>
                                                        <p class="text-xs opacity-90">Team</p>
                                                        @if($child['territory'])
                                                            <p class="text-xs opacity-75">{{ $child['territory'] }}</p>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if(count($child['children']) > 0)
                                                    <!-- Vertical connector -->
                                                    <div class="w-px h-4 bg-gray-300"></div>

                                                    <!-- Team members -->
                                                    <div class="flex flex-wrap justify-center gap-2">
                                                        @foreach($child['children'] as $member)
                                                            <div class="org-node member-node">
                                                                <div class="bg-purple-500 text-white p-2 rounded shadow text-center min-w-32">
                                                                    <h6 class="font-medium text-xs">{{ Str::limit($member['name'], 15) }}</h6>
                                                                    <p class="text-xs opacity-90">{{ $member['title'] }}</p>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h4 class="font-medium text-gray-900 mb-4">Legend</h4>
                        <div class="flex flex-wrap gap-4">
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-blue-600 rounded"></div>
                                <span class="text-sm text-gray-700">Company</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-blue-500 rounded"></div>
                                <span class="text-sm text-gray-700">Manager</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-green-500 rounded"></div>
                                <span class="text-sm text-gray-700">Team</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                                <span class="text-sm text-gray-700">Coordinator</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-purple-500 rounded"></div>
                                <span class="text-sm text-gray-700">Team Member</span>
                            </div>
                        </div>
                    </div>

                    @if(empty($hierarchy['children']))
                        <!-- Empty state -->
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No organization structure</h3>
                            <p class="mt-1 text-sm text-gray-500">Start by creating teams and assigning managers and team members.</p>
                            @can('create', App\Models\Team::class)
                                <div class="mt-6">
                                    <a href="{{ route('teams.create') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                        Create First Team
                                    </a>
                                </div>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .org-node {
            transition: transform 0.2s ease-in-out;
        }
        
        .org-node:hover {
            transform: scale(1.05);
            z-index: 10;
        }
        
        #org-chart {
            min-width: 800px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .org-node {
                transform: scale(0.9);
            }
            
            .min-w-48 { min-width: 10rem; }
            .min-w-44 { min-width: 9rem; }
            .min-w-40 { min-width: 8rem; }
            .min-w-36 { min-width: 7rem; }
            .min-w-32 { min-width: 6rem; }
        }
    </style>
@endsection