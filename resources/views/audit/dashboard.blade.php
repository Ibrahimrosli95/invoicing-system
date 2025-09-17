<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Audit Dashboard') }}
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('audit.index') }}"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    <i class="fas fa-list mr-2"></i>View Logs
                </a>
                <div class="relative">
                    <select id="dayFilter" onchange="changeDayFilter()"
                            class="bg-white border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 days</option>
                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 days</option>
                        <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 days</option>
                        <option value="365" {{ $days == 365 ? 'selected' : '' }}>Last year</option>
                    </select>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Overview Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-list-alt text-indigo-600 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Activities</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_activities']) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-green-600 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['users_active'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-database text-blue-600 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Models Affected</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $stats['models_affected'] }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Daily Average</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ number_format($stats['total_activities'] / max($days, 1)) }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Events Breakdown Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Events Breakdown</h3>
                        <div class="h-64">
                            <canvas id="eventsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Daily Activity Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Daily Activity Trend</h3>
                        <div class="h-64">
                            <canvas id="dailyActivityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Actions Breakdown Chart -->
                @if(count($stats['actions_breakdown']) > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Business Actions</h3>
                        <div class="h-64">
                            <canvas id="actionsChart"></canvas>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Top Users by Activity -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Most Active Users</h3>
                        <div class="space-y-3">
                            @forelse($topUsers as $index => $user)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                            {{ $index + 1 }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $user->user_name ?: 'Unknown User' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ number_format($user->activity_count) }} activities
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No user activity data available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Most Modified Models -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Most Modified Models</h3>
                        <div class="space-y-3">
                            @forelse($topModels as $index => $model)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                            {{ $index + 1 }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $model['model'] }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ number_format($model['count']) }} changes
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500 text-center py-4">No model modification data available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Critical Activities -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Critical Activities</h3>
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @forelse($criticalActivities as $activity)
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-md">
                                    <div class="flex items-center space-x-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $activity->event === 'deleted' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $activity->event === 'failed_login' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $activity->event === 'permission_denied' ? 'bg-orange-100 text-orange-800' : '' }}">
                                            {{ $activity->getEventDisplayName() }}
                                        </span>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $activity->getUserDisplayName() }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ class_basename($activity->auditable_type) }} #{{ $activity->auditable_id }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <i class="fas fa-shield-alt text-green-400 text-3xl mb-2"></i>
                                    <p class="text-gray-500">No critical activities detected.</p>
                                    <p class="text-xs text-gray-400">Your system security is good!</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('audit.index', ['event' => 'failed_login']) }}"
                           class="block p-4 border border-gray-200 rounded-md hover:bg-gray-50">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-yellow-500 text-xl mr-3"></i>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">Failed Login Attempts</div>
                                    <div class="text-xs text-gray-500">Security monitoring</div>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('audit.index', ['event' => 'deleted']) }}"
                           class="block p-4 border border-gray-200 rounded-md hover:bg-gray-50">
                            <div class="flex items-center">
                                <i class="fas fa-trash text-red-500 text-xl mr-3"></i>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">Deleted Records</div>
                                    <div class="text-xs text-gray-500">Data loss tracking</div>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('audit.index', ['event' => 'permission_denied']) }}"
                           class="block p-4 border border-gray-200 rounded-md hover:bg-gray-50">
                            <div class="flex items-center">
                                <i class="fas fa-ban text-orange-500 text-xl mr-3"></i>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">Permission Denials</div>
                                    <div class="text-xs text-gray-500">Access control monitoring</div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Events Breakdown Chart
        const eventsCtx = document.getElementById('eventsChart').getContext('2d');
        new Chart(eventsCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_map('ucfirst', array_keys($stats['events_breakdown']))) !!},
                datasets: [{
                    data: {!! json_encode(array_values($stats['events_breakdown'])) !!},
                    backgroundColor: [
                        '#10B981', // green for created
                        '#3B82F6', // blue for updated
                        '#EF4444', // red for deleted
                        '#8B5CF6', // purple for login
                        '#F59E0B', // amber for others
                        '#6B7280'  // gray for additional
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Daily Activity Chart
        const dailyActivityCtx = document.getElementById('dailyActivityChart').getContext('2d');
        const dailyData = {!! json_encode($stats['daily_activity']) !!};

        new Chart(dailyActivityCtx, {
            type: 'line',
            data: {
                labels: Object.keys(dailyData),
                datasets: [{
                    label: 'Activities',
                    data: Object.values(dailyData),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Actions Breakdown Chart (if data exists)
        @if(count($stats['actions_breakdown']) > 0)
        const actionsCtx = document.getElementById('actionsChart').getContext('2d');
        new Chart(actionsCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_map(function($action) {
                    return ucfirst(str_replace('_', ' ', $action));
                }, array_keys($stats['actions_breakdown']))) !!},
                datasets: [{
                    label: 'Count',
                    data: {!! json_encode(array_values($stats['actions_breakdown'])) !!},
                    backgroundColor: '#8B5CF6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        @endif

        function changeDayFilter() {
            const days = document.getElementById('dayFilter').value;
            window.location.href = `{{ route('audit.dashboard') }}?days=${days}`;
        }
    </script>
    @endpush
</x-app-layout>