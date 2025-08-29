<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', config('app.name', 'Sales System'))</title>
    
    <!-- Meta tags for Alpine.js data -->
    @auth
        <meta name="user-data" content="{{ json_encode([
            'id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'avatar' => auth()->user()->avatar_path,
            'permissions' => auth()->user()->getAllPermissions()->pluck('name'),
            'roles' => auth()->user()->getRoleNames(),
            'preferences' => auth()->user()->preferences ?? new stdClass(),
        ]) }}">
        
        @if(auth()->user()->company)
            <meta name="company-data" content="{{ json_encode([
                'id' => auth()->user()->company->id,
                'name' => auth()->user()->company->name,
                'logo' => auth()->user()->company->logo_path,
                'settings' => auth()->user()->company->settings ?? new stdClass(),
                'timezone' => auth()->user()->company->timezone,
                'currency' => auth()->user()->company->currency,
            ]) }}">
        @endif
    @endauth
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Additional head content -->
    @stack('head')
</head>
<body class="h-full bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: false }" x-init="$store.app.init()">
    <!-- Skip to content for accessibility -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-primary text-white px-4 py-2 rounded-md z-50">
        Skip to content
    </a>
    
    @auth
        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"
             @click="sidebarOpen = false">
        </div>

        <!-- Sidebar -->
        <aside x-show="sidebarOpen || $screen('lg')" 
               x-transition:enter="transition ease-in-out duration-300 transform"
               x-transition:enter-start="-translate-x-full" 
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in-out duration-300 transform"
               x-transition:leave-start="translate-x-0" 
               x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 flex flex-col w-64 bg-white border-r border-gray-200 z-50 lg:static lg:inset-0">
            @include('layouts.partials.sidebar')
        </aside>

        <!-- Main content area -->
        <div class="flex flex-1 flex-col lg:ml-0">
            <!-- Top navigation -->
            <header class="bg-white border-b border-gray-200">
                @include('layouts.partials.header')
            </header>

            <!-- Page content -->
            <main id="main-content" class="flex-1">
                @yield('content')
            </main>
        </div>
    @else
        <!-- Guest layout -->
        <main id="main-content" class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            @yield('content')
        </main>
    @endauth

    <!-- Global notification area -->
    <div x-data="notification" 
         class="fixed top-0 right-0 z-50 p-4 space-y-4 pointer-events-none">
        <template x-for="notification in $store.app.notifications" :key="notification.id">
            <div x-show="true"
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                @include('components.notification-item')
            </div>
        </template>
    </div>

    <!-- Loading overlay -->
    <div x-show="$store.app.loading" 
         x-transition
         class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-900">Loading...</span>
        </div>
    </div>

    <!-- Scripts -->
    @stack('scripts')
</body>
</html>