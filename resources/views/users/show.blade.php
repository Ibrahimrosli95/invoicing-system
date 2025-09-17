<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('users.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    {{ __('Back to Users') }}
                </a>
                @can('update', $user)
                    <a href="{{ route('users.edit', $user) }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                        {{ __('Edit User') }}
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- User Profile Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <!-- Avatar -->
                        <div class="flex-shrink-0 mr-6">
                            @if($user->avatar)
                                <img src="{{ Storage::url($user->avatar) }}" 
                                     alt="{{ $user->name }}"
                                     class="h-24 w-24 object-cover rounded-full border-2 border-gray-200">
                            @else
                                <div class="h-24 w-24 bg-gray-100 rounded-full border-2 border-gray-200 flex items-center justify-center">
                                    <span class="text-3xl font-medium text-gray-600">
                                        {{ substr($user->name, 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- User Details -->
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-2">{{ $user->name }}</h3>
                            <p class="text-lg text-gray-600 mb-2">{{ $user->email }}</p>
                            @if($user->phone)
                                <p class="text-gray-600 mb-3">{{ $user->phone }}</p>
                            @endif
                            
                            <!-- Status -->
                            <div class="mb-3">
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                                        </svg>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                                        </svg>
                                        Inactive
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Roles -->
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach($user->roles as $role)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                    </span>
                                @endforeach
                            </div>

                            <!-- Teams -->
                            @if($user->teams->isNotEmpty())
                                <div class="flex flex-wrap gap-2">
                                    @foreach($user->teams as $team)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            {{ $team->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- User Information Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6 pt-6 border-t border-gray-200">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Company</dt>
                            <dd class="text-sm text-gray-900">{{ $user->company->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                            <dd class="text-sm text-gray-900">{{ $user->created_at->format('M j, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="text-sm text-gray-900">{{ $user->updated_at->diffForHumans() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                            <dd class="text-sm text-gray-900">
                                {{ $user->email_verified_at ? $user->email_verified_at->format('M j, Y') : 'Not verified' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Teams Count</dt>
                            <dd class="text-sm text-gray-900">{{ $stats['teams_count'] }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Statistics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Activity Statistics</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $stats['leads_count'] }}</div>
                            <div class="text-sm text-gray-500">Leads</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ $stats['quotations_count'] }}</div>
                            <div class="text-sm text-gray-500">Quotations</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">{{ $stats['invoices_count'] }}</div>
                            <div class="text-sm text-gray-500">Invoices</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-orange-600">{{ $stats['teams_count'] }}</div>
                            <div class="text-sm text-gray-500">Teams</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            @if($recentActivity->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-6">Recent Activity (Last 30 Days)</h3>
                        
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                @foreach($recentActivity as $index => $activity)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                                        @if($activity['type'] === 'lead') bg-blue-500
                                                        @elseif($activity['type'] === 'quotation') bg-green-500
                                                        @elseif($activity['type'] === 'invoice') bg-purple-500
                                                        @else bg-gray-400
                                                        @endif">
                                                        @if($activity['type'] === 'lead')
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                                            </svg>
                                                        @elseif($activity['type'] === 'quotation')
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z"/>
                                                            </svg>
                                                        @else
                                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"/>
                                                            </svg>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            {{ $activity['description'] }}
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        <time datetime="{{ $activity['date']->toISOString() }}">
                                                            {{ $activity['date']->diffForHumans() }}
                                                        </time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            @canany(['update', 'toggleStatus', 'delete'], $user)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                        
                        <div class="flex flex-wrap gap-3">
                            @can('update', $user)
                                <a href="{{ route('users.edit', $user) }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                                    Edit User
                                </a>
                            @endcan
                            
                            @can('toggleStatus', $user)
                                <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out"
                                            onclick="return confirm('Are you sure you want to {{ $user->email_verified_at ? 'deactivate' : 'activate' }} this user?')">
                                        {{ $user->email_verified_at ? 'Deactivate User' : 'Activate User' }}
                                    </button>
                                </form>
                            @endcan
                            
                            @can('delete', $user)
                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out"
                                            onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                        Delete User
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endcanany
        </div>
    </div>
</x-app-layout>