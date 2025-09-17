<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Assessments') }}
            </h2>
            @can('create', \App\Models\Assessment::class)
            <div class="mt-2 sm:mt-0">
                <a href="{{ route('assessments.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Assessment
                </a>
            </div>
            @endcan
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Total</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">In Progress</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['in_progress'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Completed</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ $stats['completed'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-500">Avg. Score</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['avg_score'] ?? 0, 1) }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="bg-white shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" action="{{ route('assessments.index') }}" class="space-y-4 lg:space-y-0 lg:grid lg:grid-cols-6 lg:gap-4">
                        <!-- Search -->
                        <div class="lg:col-span-2">
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Search assessments..."
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <select name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">All Statuses</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <!-- Service Type Filter -->
                        <div>
                            <select name="service_type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">All Services</option>
                                <option value="waterproofing" {{ request('service_type') === 'waterproofing' ? 'selected' : '' }}>Waterproofing</option>
                                <option value="painting" {{ request('service_type') === 'painting' ? 'selected' : '' }}>Painting</option>
                                <option value="sports_court" {{ request('service_type') === 'sports_court' ? 'selected' : '' }}>Sports Court</option>
                                <option value="industrial" {{ request('service_type') === 'industrial' ? 'selected' : '' }}>Industrial</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <input type="date"
                                   name="date_from"
                                   value="{{ request('date_from') }}"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                   placeholder="From Date">
                        </div>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Filter
                            </button>
                            <a href="{{ route('assessments.index') }}" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium text-center hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Assessment List -->
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <!-- Mobile-friendly list view -->
                    <div class="block lg:hidden space-y-4">
                        @forelse($assessments as $assessment)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">
                                        <a href="{{ route('assessments.show', $assessment) }}" class="hover:text-blue-600">
                                            {{ $assessment->assessment_code }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-gray-500">{{ $assessment->client_name }}</p>
                                </div>
                                <div class="flex flex-col items-end space-y-1">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'scheduled' => 'bg-blue-100 text-blue-800',
                                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$assessment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ str_replace('_', ' ', ucfirst($assessment->status)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-1 text-xs text-gray-600">
                                <div class="flex justify-between">
                                    <span>Service:</span>
                                    <span>{{ ucwords(str_replace('_', ' ', $assessment->service_type)) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Date:</span>
                                    <span>{{ $assessment->assessment_date?->format('d M Y') ?? 'Not scheduled' }}</span>
                                </div>
                                @if($assessment->completion_percentage)
                                <div class="flex justify-between">
                                    <span>Progress:</span>
                                    <span>{{ $assessment->completion_percentage }}%</span>
                                </div>
                                @endif
                            </div>

                            <div class="flex justify-between items-center mt-3">
                                <div class="text-xs text-gray-500">
                                    {{ $assessment->location_city }}, {{ $assessment->location_state }}
                                </div>
                                <div class="flex space-x-2">
                                    @can('view', $assessment)
                                    <a href="{{ route('assessments.show', $assessment) }}" class="text-blue-600 hover:text-blue-800 text-xs">View</a>
                                    @endcan
                                    @can('update', $assessment)
                                    <a href="{{ route('assessments.edit', $assessment) }}" class="text-green-600 hover:text-green-800 text-xs">Edit</a>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No assessments found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new assessment.</p>
                            @can('create', \App\Models\Assessment::class)
                            <div class="mt-6">
                                <a href="{{ route('assessments.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    New Assessment
                                </a>
                            </div>
                            @endcan
                        </div>
                        @endforelse
                    </div>

                    <!-- Desktop table view -->
                    <div class="hidden lg:block">
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Assessment
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Client
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Service
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Progress
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Actions</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($assessments as $assessment)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <a href="{{ route('assessments.show', $assessment) }}" class="hover:text-blue-600">
                                                        {{ $assessment->assessment_code }}
                                                    </a>
                                                </div>
                                                <div class="text-sm text-gray-500">{{ $assessment->location_city }}, {{ $assessment->location_state }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $assessment->client_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $assessment->client_phone }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $assessment->service_type)) }}</div>
                                            <div class="text-sm text-gray-500">{{ ucwords($assessment->property_type) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $assessment->assessment_date?->format('d M Y') ?? 'Not scheduled' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$assessment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ str_replace('_', ' ', ucfirst($assessment->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($assessment->completion_percentage)
                                            <div class="flex items-center">
                                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $assessment->completion_percentage }}%"></div>
                                                </div>
                                                <span class="ml-2 text-sm text-gray-900">{{ $assessment->completion_percentage }}%</span>
                                            </div>
                                            @else
                                            <span class="text-sm text-gray-500">Not started</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center space-x-2">
                                                @can('view', $assessment)
                                                <a href="{{ route('assessments.show', $assessment) }}" class="text-blue-600 hover:text-blue-900">
                                                    View
                                                </a>
                                                @endcan

                                                @can('update', $assessment)
                                                <a href="{{ route('assessments.edit', $assessment) }}" class="text-green-600 hover:text-green-900">
                                                    Edit
                                                </a>
                                                @endcan

                                                @can('generatePdf', $assessment)
                                                <a href="{{ route('assessments.pdf', $assessment) }}" class="text-purple-600 hover:text-purple-900" target="_blank">
                                                    PDF
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium text-gray-900">No assessments found</h3>
                                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new assessment.</p>
                                            @can('create', \App\Models\Assessment::class)
                                            <div class="mt-6">
                                                <a href="{{ route('assessments.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                    </svg>
                                                    New Assessment
                                                </a>
                                            </div>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($assessments->hasPages())
                    <div class="mt-6">
                        {{ $assessments->withQueryString()->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>