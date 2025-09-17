<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Team Members: ') . $team->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('teams.show', $team) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    View Team
                </a>
                <a href="{{ route('teams.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Teams
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Team Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center space-x-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $team->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $team->description ?: 'No description' }}</p>
                        </div>
                        <div class="flex-1"></div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Current Members</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $team->users->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add New Members -->
            @if($availableUsers->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Members</h3>
                        
                        <form method="POST" action="{{ route('teams.assign-members', $team) }}">
                            @csrf
                            
                            <div class="space-y-4">
                                <p class="text-sm text-gray-600">Select users to add to this team:</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($availableUsers as $user)
                                        <label class="flex items-center space-x-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" 
                                                   name="user_ids[]" 
                                                   value="{{ $user->id }}"
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                                @if($user->title)
                                                    <div class="text-xs text-gray-400">{{ $user->title }}</div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="flex items-center justify-end space-x-4">
                                    <button type="submit" 
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Add Selected Members
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Current Team Members -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Current Team Members</h3>
                    
                    @if($team->users->count() > 0)
                        <div class="space-y-4">
                            @foreach($team->users as $user)
                                <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ substr($user->name, 0, 2) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2">
                                            <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                            @if($team->manager_id === $user->id)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Manager
                                                </span>
                                            @endif
                                            @if($team->coordinator_id === $user->id)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Coordinator
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                        @if($user->title)
                                            <p class="text-xs text-gray-400">{{ $user->title }}</p>
                                        @endif
                                        @if($user->phone)
                                            <p class="text-xs text-gray-400">{{ $user->phone }}</p>
                                        @endif
                                    </div>

                                    <div class="flex-shrink-0 flex items-center space-x-2">
                                        @if($user->is_active)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Inactive
                                            </span>
                                        @endif
                                        
                                        <!-- Don't allow removal of manager or coordinator -->
                                        @if($team->manager_id !== $user->id && $team->coordinator_id !== $user->id)
                                            <form method="POST" 
                                                  action="{{ route('teams.remove-member', [$team, $user]) }}" 
                                                  class="inline"
                                                  onsubmit="return confirm('Are you sure you want to remove {{ $user->name }} from this team?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900 text-sm">
                                                    Remove
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400">Can't remove</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            <h4 class="mt-2 text-sm font-medium text-gray-900">No team members yet</h4>
                            <p class="mt-1 text-sm text-gray-500">This team doesn't have any members assigned yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($availableUsers->count() === 0 && $team->users->count() > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">All available users assigned</h3>
                            <div class="mt-1 text-sm text-blue-700">
                                All users in your company are already assigned to teams. To add more members, you'll need to create new user accounts or remove users from other teams first.
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>