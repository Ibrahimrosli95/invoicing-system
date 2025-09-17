<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ ucfirst($validated['report_type']) }} Report Results
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ count($reportData) }} records found • Generated {{ now()->format('M d, Y H:i') }}
                </p>
            </div>
            <div class="flex space-x-3">
                <!-- Export Buttons -->
                <div class="relative inline-block text-left">
                    <button type="button" id="exportDropdown" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            onclick="toggleExportMenu()">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <div id="exportMenu" class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <a href="{{ route('reports.export', ['format' => 'csv']) }}" 
                               class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 mr-3 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Export as CSV
                            </a>
                            <a href="{{ route('reports.export', ['format' => 'excel']) }}" 
                               class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 mr-3 text-green-400 group-hover:text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                                Export as Excel
                            </a>
                            <a href="{{ route('reports.export', ['format' => 'pdf']) }}" 
                               class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 mr-3 text-red-400 group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                Export as PDF
                            </a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('reports.builder', ['type' => $validated['report_type']]) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modify Report
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Report Summary -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ count($reportData) }}</div>
                            <div class="text-sm text-gray-500">Total Records</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ count($validated['fields']) }}</div>
                            <div class="text-sm text-gray-500">Fields Selected</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ ucfirst($validated['report_type']) }}</div>
                            <div class="text-sm text-gray-500">Report Type</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-orange-600">{{ $validated['chart_type'] ?? 'table' }}</div>
                            <div class="text-sm text-gray-500">Display Type</div>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($chartData) && $validated['chart_type'] !== 'table')
            <!-- Chart Display -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Chart Visualization</h3>
                </div>
                <div class="p-6">
                    <div class="relative h-96">
                        <canvas id="reportChart"></canvas>
                    </div>
                </div>
            </div>
            @endif

            <!-- Data Table -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Report Data</h3>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-500">
                            Showing {{ count($reportData) }} of {{ count($reportData) }} records
                        </div>
                        <div class="flex items-center space-x-2">
                            <label class="text-sm text-gray-700">Show:</label>
                            <select id="pageSize" onchange="updatePageSize()" class="text-sm border-gray-300 rounded">
                                <option value="25">25</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                                <option value="all">All</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                @if(count($reportData) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200" id="reportTable">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach($validated['fields'] as $fieldKey => $fieldValue)
                                    @php
                                        $fieldName = is_numeric($fieldKey) ? $fieldValue : $fieldKey;
                                        $availableFields = app('App\Http\Controllers\ReportController')->getAvailableFields($validated['report_type'], auth()->user());
                                        $fieldLabel = $availableFields[$fieldName] ?? ucfirst(str_replace('_', ' ', $fieldName));
                                    @endphp
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="sortTable('{{ $fieldName }}')">
                                        <div class="flex items-center space-x-1">
                                            <span>{{ $fieldLabel }}</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                                            </svg>
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="reportTableBody">
                            @foreach($reportData->take(50) as $index => $record)
                            <tr class="hover:bg-gray-50 {{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                @foreach($validated['fields'] as $fieldKey => $fieldValue)
                                    @php
                                        $fieldName = is_numeric($fieldKey) ? $fieldValue : $fieldKey;
                                        $value = $record->{$fieldName} ?? '';
                                        
                                        // Format specific field types
                                        if (str_contains($fieldName, '_at') && $value) {
                                            $value = \Carbon\Carbon::parse($value)->format('M d, Y H:i');
                                        } elseif (in_array($fieldName, ['total', 'subtotal', 'amount', 'estimated_value', 'paid_amount', 'balance']) && is_numeric($value)) {
                                            $value = 'RM ' . number_format($value, 2);
                                        } elseif ($fieldName === 'status') {
                                            $statusColors = [
                                                'NEW' => 'bg-blue-100 text-blue-800',
                                                'CONTACTED' => 'bg-yellow-100 text-yellow-800',
                                                'QUOTED' => 'bg-purple-100 text-purple-800',
                                                'WON' => 'bg-green-100 text-green-800',
                                                'LOST' => 'bg-red-100 text-red-800',
                                                'DRAFT' => 'bg-gray-100 text-gray-800',
                                                'SENT' => 'bg-blue-100 text-blue-800',
                                                'ACCEPTED' => 'bg-green-100 text-green-800',
                                                'REJECTED' => 'bg-red-100 text-red-800',
                                                'PAID' => 'bg-green-100 text-green-800',
                                                'OVERDUE' => 'bg-red-100 text-red-800',
                                            ];
                                            $colorClass = $statusColors[$value] ?? 'bg-gray-100 text-gray-800';
                                        }
                                    @endphp
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($fieldName === 'status' && isset($colorClass))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                                {{ $value }}
                                            </span>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(count($reportData) > 50)
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing first 50 of {{ count($reportData) }} records
                        </div>
                        <div class="flex space-x-2">
                            <button id="loadMoreBtn" onclick="loadMoreRecords()" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-md transition duration-150">
                                Load More Records
                            </button>
                            <button onclick="loadAllRecords()" class="bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium py-2 px-4 rounded-md transition duration-150">
                                Show All
                            </button>
                        </div>
                    </div>
                </div>
                @endif
                @else
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No data found</h3>
                    <p class="mt-1 text-sm text-gray-500">No records match your current filters.</p>
                    <div class="mt-6">
                        <a href="{{ route('reports.builder', ['type' => $validated['report_type']]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Modify Filters
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if(isset($chartData) && $validated['chart_type'] !== 'table')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart configuration would go here
        const ctx = document.getElementById('reportChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: '{{ $validated['chart_type'] }}',
            data: {!! json_encode($chartData) !!},
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: '{{ ucfirst($validated['report_type']) }} Report Chart'
                    }
                }
            }
        });
    </script>
    @endif

    <script>
        let currentPage = 1;
        let recordsPerPage = 50;
        let allRecords = {!! json_encode($reportData) !!};
        let currentSort = { field: null, direction: 'asc' };

        function toggleExportMenu() {
            const menu = document.getElementById('exportMenu');
            menu.classList.toggle('hidden');
        }

        // Close export menu when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('exportDropdown');
            const menu = document.getElementById('exportMenu');
            if (!dropdown.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });

        function updatePageSize() {
            const pageSize = document.getElementById('pageSize').value;
            recordsPerPage = pageSize === 'all' ? allRecords.length : parseInt(pageSize);
            currentPage = 1;
            renderTable();
        }

        function loadMoreRecords() {
            currentPage++;
            renderTable(true); // append mode
        }

        function loadAllRecords() {
            recordsPerPage = allRecords.length;
            currentPage = 1;
            document.getElementById('pageSize').value = 'all';
            renderTable();
        }

        function sortTable(field) {
            if (currentSort.field === field) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.field = field;
                currentSort.direction = 'asc';
            }

            allRecords.sort((a, b) => {
                let aVal = a[field] || '';
                let bVal = b[field] || '';
                
                // Convert to strings for comparison
                aVal = String(aVal).toLowerCase();
                bVal = String(bVal).toLowerCase();
                
                if (currentSort.direction === 'asc') {
                    return aVal < bVal ? -1 : (aVal > bVal ? 1 : 0);
                } else {
                    return aVal > bVal ? -1 : (aVal < bVal ? 1 : 0);
                }
            });

            currentPage = 1;
            renderTable();
        }

        function renderTable(append = false) {
            const tbody = document.getElementById('reportTableBody');
            const startIndex = append ? (currentPage - 1) * recordsPerPage : 0;
            const endIndex = currentPage * recordsPerPage;
            const recordsToShow = allRecords.slice(startIndex, endIndex);

            if (!append) {
                tbody.innerHTML = '';
            }

            // This would need to be implemented based on the actual field structure
            // For now, just hide the load more button if we've shown all records
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            if (loadMoreBtn && endIndex >= allRecords.length) {
                loadMoreBtn.style.display = 'none';
            }
        }

        // Initialize table
        renderTable();
    </script>
</x-app-layout>