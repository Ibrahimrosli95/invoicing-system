<!-- Sidebar -->
<div class="flex flex-col h-full">
    <!-- Logo and company info -->
    <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
        <div class="flex items-center">
            @if(auth()->user()->company?->logo_path)
                <img class="h-8 w-auto" src="{{ asset(auth()->user()->company->logo_path) }}" alt="{{ auth()->user()->company->name }}">
            @else
                <div class="h-8 w-8 bg-primary rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-sm">{{ substr(auth()->user()->company?->name ?? 'S', 0, 1) }}</span>
                </div>
            @endif
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">{{ auth()->user()->company?->name ?? 'Sales System' }}</p>
            </div>
        </div>
        
        <!-- Close sidebar button for mobile -->
        <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-gray-600">
            <span class="sr-only">Close sidebar</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('dashboard') ? 'bg-primary text-white' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
            Dashboard
        </a>

        <!-- Leads -->
        <a href="{{ route('leads.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('leads.*') ? 'bg-primary text-white' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('leads.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            Leads
        </a>

        <!-- Customers -->
        <a href="{{ route('customers.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('customers.*') ? 'bg-primary text-white' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('customers.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Customers
        </a>

        <!-- Quotations -->
        <a href="{{ route('quotations.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('quotations.*') ? 'bg-primary text-white' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('quotations.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            Quotations
        </a>

        <!-- Invoices -->
        <a href="{{ route('invoices.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('invoices.*') ? 'bg-primary text-white' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('invoices.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25M8.25 21h8.25m-8.25 0H4.875c-.621 0-1.125-.504-1.125-1.125v-16.5c0-.621.504-1.125 1.125-1.125H8.25m0 16.5c0 .621.504 1.125 1.125 1.125h8.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621.504-1.125 1.125-1.125H21" />
            </svg>
            Invoices
        </a>

        <!-- Pricing Book -->
        @can('view_pricing')
        <a href="{{ route('pricing.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('pricing.*') ? 'bg-primary text-white' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('pricing.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Pricing Book
        </a>
        @endcan

        <!-- Service Templates -->
        @can('view_templates')
        <a href="{{ route('templates.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('templates.*') ? 'bg-primary text-white' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('templates.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            Templates
        </a>
        @endcan

        <!-- Divider -->
        <div class="border-t border-gray-200 my-4"></div>

        <!-- Reports -->
        @can('view_reports')
        <a href="{{ route('reports.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('reports.*') ? 'bg-primary text-white' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            Reports
        </a>
        @endcan

        <!-- Settings -->
        @can('manage_settings')
        <a href="{{ route('settings.index') }}" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md {{ request()->routeIs('settings.*') ? 'bg-primary text-white' : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50' }}">
            <svg class="mr-3 flex-shrink-0 h-5 w-5 {{ request()->routeIs('settings.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-500' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Settings
        </a>
        @endcan
    </nav>

    <!-- User menu -->
    <div class="flex-shrink-0 p-4 border-t border-gray-200">
        <div x-data="dropdown" class="relative">
            <button @click="toggle()" class="group w-full bg-white rounded-md px-3.5 py-2 text-left text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <span class="flex w-full justify-between items-center">
                    <span class="flex min-w-0 items-center justify-between space-x-3">
                        <div class="h-8 w-8 bg-gray-300 rounded-full flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-700">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <span class="flex-1 min-w-0">
                            <span class="text-gray-900 text-sm font-medium truncate">{{ auth()->user()->name }}</span>
                            <span class="text-gray-500 text-xs truncate">{{ auth()->user()->title ?? 'User' }}</span>
                        </span>
                    </span>
                    <svg class="flex-shrink-0 h-5 w-5 text-gray-400 group-hover:text-gray-500" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
            </button>
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-1"
                 @click.away="close()"
                 class="absolute bottom-full left-0 right-0 mb-1 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-10">
                <div class="py-1">
                    <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Your Profile</a>
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