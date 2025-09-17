<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Report Builder') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Create custom {{ ucfirst($reportType) }} reports with advanced filtering and visualization
                </p>
            </div>
            <a href="{{ route('reports.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Reports
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form id="reportForm" method="POST" action="{{ route('reports.generate') }}" class="space-y-8">
                @csrf
                <input type="hidden" name="report_type" value="{{ $reportType }}">
                
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <!-- Configuration Panel -->
                    <div class="lg:col-span-1">
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200 sticky top-4">
                            <!-- Report Type -->
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Report Configuration</h3>
                                
                                <!-- Report Type Selector -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                                    <select name="report_type" id="reportTypeSelect" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" onchange="changeReportType()">
                                        <option value="leads" {{ $reportType == 'leads' ? 'selected' : '' }}>Lead Reports</option>
                                        <option value="quotations" {{ $reportType == 'quotations' ? 'selected' : '' }}>Quotation Reports</option>
                                        <option value="invoices" {{ $reportType == 'invoices' ? 'selected' : '' }}>Invoice Reports</option>
                                        <option value="payments" {{ $reportType == 'payments' ? 'selected' : '' }}>Payment Reports</option>
                                        <option value="sales_performance" {{ $reportType == 'sales_performance' ? 'selected' : '' }}>Sales Performance</option>
                                        <option value="financial" {{ $reportType == 'financial' ? 'selected' : '' }}>Financial Reports</option>
                                    </select>
                                </div>

                                <!-- Visualization Type -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Display Type</label>
                                    <select name="chart_type" id="chartType" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        @foreach($chartOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Date Range -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                                    <select id="dateRangeSelect" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 mb-3" onchange="toggleCustomDateRange()">
                                        <option value="this_month">This Month</option>
                                        <option value="last_month">Last Month</option>
                                        <option value="this_quarter">This Quarter</option>
                                        <option value="this_year">This Year</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                    
                                    <div id="customDateRange" class="hidden space-y-2">
                                        <input type="date" name="date_range[from]" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="From Date">
                                        <input type="date" name="date_range[to]" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="To Date">
                                    </div>
                                </div>

                                <!-- Limit -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Record Limit</label>
                                    <select name="limit" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="100">100 records</option>
                                        <option value="500">500 records</option>
                                        <option value="1000" selected>1000 records</option>
                                        <option value="5000">5000 records</option>
                                        <option value="10000">10000 records</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="p-6">
                                <div class="space-y-3">
                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Generate Report
                                    </button>
                                    
                                    <button type="button" onclick="saveTemplate()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-md transition duration-150">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                        </svg>
                                        Save as Template
                                    </button>
                                    
                                    <button type="button" onclick="resetForm()" class="w-full border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150">
                                        Reset Form
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Configuration Area -->
                    <div class="lg:col-span-3">
                        <!-- Field Selection -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-8">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Select Fields to Include</h3>
                                <p class="text-sm text-gray-600 mt-1">Choose which data fields to include in your report</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($availableFields as $fieldKey => $fieldLabel)
                                    <label class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-150">
                                        <input type="checkbox" name="fields[]" value="{{ $fieldKey }}" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="text-sm font-medium text-gray-900">{{ $fieldLabel }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                
                                <div class="mt-4 flex space-x-2">
                                    <button type="button" onclick="selectAllFields()" class="text-sm bg-blue-100 hover:bg-blue-200 text-blue-800 font-medium py-1 px-3 rounded">
                                        Select All
                                    </button>
                                    <button type="button" onclick="deselectAllFields()" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-1 px-3 rounded">
                                        Deselect All
                                    </button>
                                    <button type="button" onclick="selectCommonFields()" class="text-sm bg-green-100 hover:bg-green-200 text-green-800 font-medium py-1 px-3 rounded">
                                        Common Fields
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-8">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Filters & Conditions</h3>
                                <p class="text-sm text-gray-600 mt-1">Apply filters to narrow down your data</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach($filterOptions as $filterKey => $filter)
                                        @if($filter['type'] === 'select')
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ $filter['label'] }}</label>
                                                <select name="filters[{{ $filterKey }}]" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                    <option value="">All {{ $filter['label'] }}</option>
                                                    @foreach($filter['options'] as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @elseif($filter['type'] === 'date_range')
                                            <!-- Date range handled in configuration panel -->
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Sorting & Grouping -->
                        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Sorting & Grouping</h3>
                                <p class="text-sm text-gray-600 mt-1">Configure how your data should be organized</p>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                                        <select name="sort_by" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="created_at">Created Date</option>
                                            @foreach($availableFields as $fieldKey => $fieldLabel)
                                                <option value="{{ $fieldKey }}">{{ $fieldLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort Direction</label>
                                        <select name="sort_direction" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="desc">Newest First</option>
                                            <option value="asc">Oldest First</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Group By (Optional)</label>
                                    <select name="group_by" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">No Grouping</option>
                                        <option value="status">Status</option>
                                        <option value="team_name">Team</option>
                                        <option value="created_at">Date</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Save Template Modal -->
    <div id="saveTemplateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Save Report Template</h3>
                <form id="saveTemplateForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Template Name</label>
                        <input type="text" id="templateName" name="name" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                        <textarea id="templateDescription" name="description" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" rows="3"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeSaveTemplateModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                            Save Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function changeReportType() {
            const reportType = document.getElementById('reportTypeSelect').value;
            const params = new URLSearchParams();
            params.set('type', reportType);
            window.location.href = `/reports/builder?${params.toString()}`;
        }

        function toggleCustomDateRange() {
            const select = document.getElementById('dateRangeSelect');
            const customDiv = document.getElementById('customDateRange');
            
            if (select.value === 'custom') {
                customDiv.classList.remove('hidden');
            } else {
                customDiv.classList.add('hidden');
            }
        }

        function selectAllFields() {
            const checkboxes = document.querySelectorAll('input[name="fields[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = true);
        }

        function deselectAllFields() {
            const checkboxes = document.querySelectorAll('input[name="fields[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = false);
        }

        function selectCommonFields() {
            deselectAllFields();
            const commonFields = ['company_name', 'contact_person', 'status', 'created_at']; // Adjust based on report type
            const checkboxes = document.querySelectorAll('input[name="fields[]"]');
            
            checkboxes.forEach(checkbox => {
                if (commonFields.includes(checkbox.value)) {
                    checkbox.checked = true;
                }
            });
        }

        function resetForm() {
            document.getElementById('reportForm').reset();
            deselectAllFields();
        }

        function saveTemplate() {
            document.getElementById('saveTemplateModal').classList.remove('hidden');
        }

        function closeSaveTemplateModal() {
            document.getElementById('saveTemplateModal').classList.add('hidden');
        }

        document.getElementById('saveTemplateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(document.getElementById('reportForm'));
            const reportConfig = {};
            
            // Collect form data
            for (let [key, value] of formData.entries()) {
                if (key.endsWith('[]')) {
                    const arrayKey = key.slice(0, -2);
                    if (!reportConfig[arrayKey]) reportConfig[arrayKey] = [];
                    reportConfig[arrayKey].push(value);
                } else {
                    reportConfig[key] = value;
                }
            }
            
            const templateData = {
                name: document.getElementById('templateName').value,
                description: document.getElementById('templateDescription').value,
                report_config: reportConfig,
                _token: '{{ csrf_token() }}'
            };
            
            fetch('{{ route("reports.save-template") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(templateData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Template saved successfully!');
                    closeSaveTemplateModal();
                } else {
                    alert('Error saving template');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving template');
            });
        });

        // Form validation
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            const selectedFields = document.querySelectorAll('input[name="fields[]"]:checked');
            if (selectedFields.length === 0) {
                e.preventDefault();
                alert('Please select at least one field to include in the report.');
                return false;
            }
        });
    </script>
</x-app-layout>