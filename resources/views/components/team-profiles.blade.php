@if($users->isNotEmpty())
<div class="team-profiles-container" x-data="teamProfiles()">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                    </svg>
                    @if($team)
                        {{ $team->name }} Team
                    @else
                        Our Team
                    @endif
                    @if($featuredOnly)
                        <span class="ml-2 text-xs text-yellow-600 bg-yellow-100 px-2 py-1 rounded-full">
                            ⭐ Featured Members
                        </span>
                    @endif
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $users->count() }} team member{{ $users->count() !== 1 ? 's' : '' }}
                    @if($team && $team->description)
                        • {{ $team->description }}
                    @endif
                </p>
            </div>

            <!-- Layout Toggle -->
            @if(in_array($layout, ['grid', 'row', 'card']))
            <div class="flex bg-gray-100 rounded-lg p-1">
                <button @click="layout = 'grid'" 
                        :class="layout === 'grid' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'"
                        class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                        title="Grid View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
                <button @click="layout = 'row'" 
                        :class="layout === 'row' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'"
                        class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                        title="Row View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </button>
                <button @click="layout = 'card'" 
                        :class="layout === 'card' ? 'bg-white shadow-sm' : 'hover:bg-gray-200'"
                        class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                        title="Card View">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Grid Layout -->
    <div x-show="layout === 'grid'" 
         class="{{ $layout === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6' : 'hidden' }}">
        @foreach($users as $user)
        @php
            $stats = $this->getUserStats($user);
            $roleColor = $this->getRoleColor($user);
        @endphp
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow group relative">
            <!-- Featured Badge -->
            @if($user->is_featured)
                <div class="absolute -top-2 -right-2">
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded-full border border-yellow-300">
                        ⭐ Featured
                    </span>
                </div>
            @endif

            <!-- Avatar -->
            <div class="text-center mb-4">
                <div class="relative inline-block">
                    @if($this->getAvatarUrl($user))
                        <img src="{{ $this->getAvatarUrl($user) }}" 
                             alt="{{ $user->name }}"
                             class="w-20 h-20 rounded-full object-cover border-4 border-gray-100">
                    @else
                        <div class="w-20 h-20 bg-{{ $roleColor }}-100 rounded-full flex items-center justify-center border-4 border-gray-100">
                            <span class="text-2xl font-bold text-{{ $roleColor }}-600">
                                {{ $this->getUserInitials($user) }}
                            </span>
                        </div>
                    @endif
                    
                    <!-- Online Status -->
                    @if($user->last_seen_at && $user->last_seen_at->diffInMinutes() < 15)
                        <div class="absolute -bottom-1 -right-1">
                            <span class="w-6 h-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                                <span class="w-2 h-2 bg-white rounded-full"></span>
                            </span>
                        </div>
                    @endif
                </div>
                
                <h4 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                    {{ $user->name }}
                </h4>
                
                <div class="mt-1">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $roleColor }}-100 text-{{ $roleColor }}-800">
                        {{ $this->getRoleDisplayName($user) }}
                    </span>
                </div>

                @if($this->getPrimaryTeam($user) && !$team)
                    <p class="text-sm text-gray-600 mt-1">{{ $this->getPrimaryTeam($user)->name }}</p>
                @endif
            </div>

            <!-- Contact Info -->
            @if($showContact)
                <div class="space-y-2 mb-4 text-sm text-gray-600">
                    @if($user->email)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <a href="mailto:{{ $user->email }}" class="hover:text-blue-600 truncate">{{ $user->email }}</a>
                        </div>
                    @endif
                    
                    @if($user->phone)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <a href="tel:{{ $user->phone }}" class="hover:text-blue-600">{{ $this->formatPhone($user->phone) }}</a>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Performance Stats -->
            @if($showStats && !empty($stats))
                <div class="border-t border-gray-100 pt-4">
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ $stats['leads'] }}</div>
                            <div class="text-xs text-gray-500">Leads</div>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900">{{ $stats['quotations'] }}</div>
                            <div class="text-xs text-gray-500">Quotes</div>
                        </div>
                    </div>
                    
                    @if($stats['conversion_rate'] > 0)
                        <div class="mt-3 text-center">
                            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $stats['conversion_rate'] }}% Success Rate
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Row Layout -->
    <div x-show="layout === 'row'" 
         class="{{ $layout === 'row' ? 'space-y-4' : 'hidden' }}">
        @foreach($users as $user)
        @php
            $stats = $this->getUserStats($user);
            $roleColor = $this->getRoleColor($user);
        @endphp
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow group">
            <div class="flex items-center space-x-4">
                <!-- Avatar -->
                <div class="relative flex-shrink-0">
                    @if($this->getAvatarUrl($user))
                        <img src="{{ $this->getAvatarUrl($user) }}" 
                             alt="{{ $user->name }}"
                             class="w-16 h-16 rounded-full object-cover border-2 border-gray-100">
                    @else
                        <div class="w-16 h-16 bg-{{ $roleColor }}-100 rounded-full flex items-center justify-center border-2 border-gray-100">
                            <span class="text-lg font-bold text-{{ $roleColor }}-600">
                                {{ $this->getUserInitials($user) }}
                            </span>
                        </div>
                    @endif
                    
                    @if($user->is_featured)
                        <div class="absolute -top-1 -right-1">
                            <span class="text-yellow-500 text-lg">⭐</span>
                        </div>
                    @endif
                </div>

                <!-- User Info -->
                <div class="flex-grow">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                {{ $user->name }}
                            </h4>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $roleColor }}-100 text-{{ $roleColor }}-800">
                                    {{ $this->getRoleDisplayName($user) }}
                                </span>
                                @if($this->getPrimaryTeam($user) && !$team)
                                    <span class="text-sm text-gray-500">• {{ $this->getPrimaryTeam($user)->name }}</span>
                                @endif
                            </div>
                            
                            @if($showContact)
                                <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
                                    @if($user->email)
                                        <a href="mailto:{{ $user->email }}" class="hover:text-blue-600 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $user->email }}
                                        </a>
                                    @endif
                                    @if($user->phone)
                                        <a href="tel:{{ $user->phone }}" class="hover:text-blue-600 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            {{ $this->formatPhone($user->phone) }}
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <!-- Stats -->
                        @if($showStats && !empty($stats))
                            <div class="flex items-center space-x-6 text-center">
                                <div>
                                    <div class="text-lg font-bold text-gray-900">{{ $stats['leads'] }}</div>
                                    <div class="text-xs text-gray-500">Leads</div>
                                </div>
                                <div>
                                    <div class="text-lg font-bold text-gray-900">{{ $stats['quotations'] }}</div>
                                    <div class="text-xs text-gray-500">Quotes</div>
                                </div>
                                @if($stats['conversion_rate'] > 0)
                                    <div>
                                        <div class="text-lg font-bold text-green-600">{{ $stats['conversion_rate'] }}%</div>
                                        <div class="text-xs text-gray-500">Success</div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Card Layout -->
    <div x-show="layout === 'card'" 
         class="{{ $layout === 'card' ? 'grid grid-cols-1 md:grid-cols-2 gap-6' : 'hidden' }}">
        @foreach($users as $user)
        @php
            $stats = $this->getUserStats($user);
            $roleColor = $this->getRoleColor($user);
        @endphp
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group">
            <!-- Header -->
            <div class="bg-gradient-to-r from-{{ $roleColor }}-50 to-{{ $roleColor }}-100 px-6 py-4 relative">
                @if($user->is_featured)
                    <div class="absolute top-2 right-2">
                        <span class="text-yellow-500 text-lg">⭐</span>
                    </div>
                @endif
                
                <div class="flex items-center space-x-4">
                    @if($this->getAvatarUrl($user))
                        <img src="{{ $this->getAvatarUrl($user) }}" 
                             alt="{{ $user->name }}"
                             class="w-16 h-16 rounded-full object-cover border-2 border-white">
                    @else
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center border-2 border-{{ $roleColor }}-200">
                            <span class="text-xl font-bold text-{{ $roleColor }}-600">
                                {{ $this->getUserInitials($user) }}
                            </span>
                        </div>
                    @endif
                    
                    <div>
                        <h4 class="font-semibold text-gray-900 text-lg group-hover:text-blue-600 transition-colors">
                            {{ $user->name }}
                        </h4>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-white text-{{ $roleColor }}-800 border border-{{ $roleColor }}-200">
                            {{ $this->getRoleDisplayName($user) }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Content -->
            <div class="px-6 py-4">
                @if($this->getPrimaryTeam($user) && !$team)
                    <div class="mb-3">
                        <span class="text-sm font-medium text-gray-700">Team: </span>
                        <span class="text-sm text-gray-600">{{ $this->getPrimaryTeam($user)->name }}</span>
                    </div>
                @endif
                
                @if($showContact)
                    <div class="space-y-2 mb-4">
                        @if($user->email)
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <a href="mailto:{{ $user->email }}" class="text-gray-600 hover:text-blue-600 truncate">{{ $user->email }}</a>
                            </div>
                        @endif
                        
                        @if($user->phone)
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <a href="tel:{{ $user->phone }}" class="text-gray-600 hover:text-blue-600">{{ $this->formatPhone($user->phone) }}</a>
                            </div>
                        @endif
                    </div>
                @endif
                
                @if($showStats && !empty($stats))
                    <div class="grid grid-cols-3 gap-4 text-center border-t border-gray-100 pt-4">
                        <div>
                            <div class="text-lg font-bold text-gray-900">{{ $stats['leads'] }}</div>
                            <div class="text-xs text-gray-500">Leads</div>
                        </div>
                        <div>
                            <div class="text-lg font-bold text-gray-900">{{ $stats['quotations'] }}</div>
                            <div class="text-xs text-gray-500">Quotes</div>
                        </div>
                        <div>
                            @if($stats['conversion_rate'] > 0)
                                <div class="text-lg font-bold text-green-600">{{ $stats['conversion_rate'] }}%</div>
                                <div class="text-xs text-gray-500">Success</div>
                            @else
                                <div class="text-lg font-bold text-gray-400">—</div>
                                <div class="text-xs text-gray-500">Success</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Compact Layout -->
    @if($layout === 'compact')
    <div class="flex flex-wrap gap-3">
        @foreach($users as $user)
        @php $roleColor = $this->getRoleColor($user); @endphp
        
        <div class="inline-flex items-center bg-white border border-gray-200 rounded-full px-4 py-2 hover:shadow-sm transition-shadow group">
            @if($this->getAvatarUrl($user))
                <img src="{{ $this->getAvatarUrl($user) }}" 
                     alt="{{ $user->name }}"
                     class="w-8 h-8 rounded-full object-cover mr-3">
            @else
                <div class="w-8 h-8 bg-{{ $roleColor }}-100 rounded-full flex items-center justify-center mr-3">
                    <span class="text-sm font-bold text-{{ $roleColor }}-600">
                        {{ $this->getUserInitials($user) }}
                    </span>
                </div>
            @endif
            
            <div>
                <span class="font-medium text-gray-700 group-hover:text-blue-600">{{ $user->name }}</span>
                @if($user->is_featured)
                    <span class="ml-1 text-yellow-500">⭐</span>
                @endif
                <div class="text-xs text-gray-500">{{ $this->getRoleDisplayName($user) }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<script>
function teamProfiles() {
    return {
        layout: '{{ $layout }}',
        
        init() {
            // Initialize any interactive features
        }
    }
}
</script>

@else
<div class="max-w-4xl mx-auto bg-gray-50 rounded-lg shadow p-8 text-center">
    <div class="text-gray-500">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No Team Members Available</h3>
        <p class="text-gray-600">Add some active team members to display their profiles here.</p>
    </div>
</div>
@endif