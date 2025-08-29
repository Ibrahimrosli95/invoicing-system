<!-- Top navigation header -->
<div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
    <!-- Left side - Mobile menu button and breadcrumbs -->
    <div class="flex items-center">
        <!-- Mobile menu button -->
        <button @click="sidebarOpen = !sidebarOpen" 
                class="lg:hidden -ml-0.5 -mt-0.5 h-12 w-12 inline-flex items-center justify-center rounded-md text-gray-500 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary">
            <span class="sr-only">Open sidebar</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
        
        <!-- Breadcrumbs -->
        <nav class="ml-4 flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-4">
                @hasSection('breadcrumbs')
                    @yield('breadcrumbs')
                @else
                    <li>
                        <div class="flex">
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                                Dashboard
                            </a>
                        </div>
                    </li>
                    @if(!request()->routeIs('dashboard'))
                        <li>
                            <div class="flex items-center">
                                <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="ml-4 text-sm font-medium text-gray-900">
                                    @yield('page-title', 'Page')
                                </span>
                            </div>
                        </li>
                    @endif
                @endif
            </ol>
        </nav>
    </div>

    <!-- Right side - Search, notifications, and user menu -->
    <div class="flex items-center space-x-4">
        <!-- Global search -->
        <div class="hidden md:block">
            <div x-data="search({ searchUrl: '{{ route('api.search') }}' })" class="relative">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input x-model="query" 
                           @keydown="handleKeydown($event)"
                           type="text" 
                           placeholder="Search leads, quotes, invoices..." 
                           class="block w-full rounded-md border-gray-300 bg-gray-50 pl-10 pr-3 py-2 placeholder-gray-400 focus:border-primary focus:bg-white focus:ring-primary sm:text-sm">
                </div>
                
                <!-- Search results dropdown -->
                <div x-show="open" 
                     x-transition
                     class="absolute top-full left-0 right-0 mt-2 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                    <div class="max-h-64 overflow-y-auto">
                        <template x-for="(result, index) in results" :key="result.id">
                            <a @click="selectResult(result, index)" 
                               :class="{ 'bg-primary text-white': selectedIndex === index, 'text-gray-900': selectedIndex !== index }"
                               class="block px-4 py-2 text-sm hover:bg-gray-50 cursor-pointer">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium" x-text="result.title"></p>
                                        <p class="text-xs" :class="{ 'text-gray-200': selectedIndex === index, 'text-gray-500': selectedIndex !== index }" x-text="result.subtitle"></p>
                                    </div>
                                    <span class="text-xs px-2 py-1 rounded-full" :class="{ 'bg-white text-primary': selectedIndex === index, 'bg-gray-100 text-gray-600': selectedIndex !== index }" x-text="result.type"></span>
                                </div>
                            </a>
                        </template>
                        
                        <div x-show="loading" class="px-4 py-2 text-sm text-gray-500">
                            Searching...
                        </div>
                        
                        <div x-show="!loading && results.length === 0 && query.length >= minQueryLength" class="px-4 py-2 text-sm text-gray-500">
                            No results found
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div x-data="dropdown" class="relative">
            <button @click="toggle()" 
                    class="relative rounded-full bg-white p-1 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <span class="sr-only">View notifications</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <!-- Notification badge -->
                <span x-show="$store.app.notifications.length > 0" 
                      class="absolute -top-0.5 -right-0.5 h-4 w-4 rounded-full bg-error text-xs text-white flex items-center justify-center">
                    <span x-text="$store.app.notifications.length"></span>
                </span>
            </button>

            <!-- Notifications dropdown -->
            <div x-show="open" 
                 x-transition
                 @click.away="close()"
                 class="absolute right-0 top-full mt-2 w-80 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Notifications</h3>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <template x-for="notification in $store.app.notifications" :key="notification.id">
                        <div class="px-4 py-3 border-b border-gray-100 last:border-b-0">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-2 w-2 rounded-full" 
                                         :class="{
                                             'bg-success': notification.type === 'success',
                                             'bg-error': notification.type === 'error',
                                             'bg-warning': notification.type === 'warning',
                                             'bg-primary': notification.type === 'info'
                                         }"></div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900" x-text="notification.message"></p>
                                    <p class="text-xs text-gray-500" x-text="new Date().toLocaleTimeString()"></p>
                                </div>
                                <button @click="$store.app.removeNotification(notification.id)" 
                                        class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                    
                    <div x-show="$store.app.notifications.length === 0" class="px-4 py-8 text-center text-gray-500">
                        No notifications
                    </div>
                </div>
            </div>
        </div>

        <!-- User menu -->
        <div x-data="dropdown" class="relative">
            <button @click="toggle()" 
                    class="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <span class="sr-only">Open user menu</span>
                @if(auth()->user()->avatar_path)
                    <img class="h-8 w-8 rounded-full" src="{{ asset(auth()->user()->avatar_path) }}" alt="{{ auth()->user()->name }}">
                @else
                    <div class="h-8 w-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-medium">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                @endif
            </button>

            <!-- User dropdown menu -->
            <div x-show="open" 
                 x-transition
                 @click.away="close()"
                 class="absolute right-0 top-full mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                <div class="py-1">
                    <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-100">
                        <p class="font-medium">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Your Profile</a>
                    <a href="{{ route('settings.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Settings</a>
                    <div class="border-t border-gray-100"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Sign out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>