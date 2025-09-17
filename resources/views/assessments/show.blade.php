<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Assessment {{ $assessment->assessment_code }}
                </h2>
                <div class="mt-1 flex items-center space-x-2">
                    @php
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-800',
                            'scheduled' => 'bg-blue-100 text-blue-800',
                            'in_progress' => 'bg-yellow-100 text-yellow-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ $statusColors[$assessment->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ str_replace('_', ' ', ucfirst($assessment->status)) }}
                    </span>
                    @if($assessment->urgency_level === 'emergency')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        Emergency
                    </span>
                    @endif
                </div>
            </div>
            <div class="mt-2 sm:mt-0 flex flex-wrap gap-2">
                <a href="{{ route('assessments.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>

                @can('update', $assessment)
                <a href="{{ route('assessments.edit', $assessment) }}" class="inline-flex items-center px-3 py-2 border border-green-300 shadow-sm text-sm leading-4 font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
                @endcan

                @can('generatePdf', $assessment)
                <a href="{{ route('assessments.pdf', $assessment) }}" target="_blank" class="inline-flex items-center px-3 py-2 border border-purple-300 shadow-sm text-sm leading-4 font-medium rounded-md text-purple-700 bg-purple-50 hover:bg-purple-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    PDF Report
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Assessment Overview -->
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Assessment Details</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Service Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $assessment->service_type)) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Property Type</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ ucwords($assessment->property_type) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Assessment Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $assessment->assessment_date?->format('d M Y') ?? 'Not scheduled' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Estimated Duration</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $assessment->estimated_duration }} minutes</dd>
                                </div>
                                @if($assessment->total_area)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total Area</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ number_format($assessment->total_area, 2) }} {{ $assessment->area_unit }}</dd>
                                </div>
                                @endif
                                @if($assessment->overall_risk_score)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Risk Score</dt>
                                    <dd class="mt-1">
                                        <div class="flex items-center">
                                            <span class="text-sm text-gray-900">{{ $assessment->overall_risk_score }}/10</span>
                                            @php
                                                $riskColor = $assessment->overall_risk_score >= 7 ? 'bg-red-500' : ($assessment->overall_risk_score >= 4 ? 'bg-yellow-500' : 'bg-green-500');
                                            @endphp
                                            <div class="ml-2 w-20 bg-gray-200 rounded-full h-2">
                                                <div class="{{ $riskColor }} h-2 rounded-full" style="width: {{ ($assessment->overall_risk_score / 10) * 100 }}%"></div>
                                            </div>
                                        </div>
                                    </dd>
                                </div>
                                @endif
                            </div>

                            @if($assessment->notes)
                            <div class="mt-6">
                                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900 bg-gray-50 rounded-md p-3">{{ $assessment->notes }}</dd>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Client Information -->
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Client Information</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Client Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $assessment->client_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="tel:{{ $assessment->client_phone }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $assessment->client_phone }}
                                        </a>
                                    </dd>
                                </div>
                                @if($assessment->client_email)
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="mailto:{{ $assessment->client_email }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $assessment->client_email }}
                                        </a>
                                    </dd>
                                </div>
                                @endif
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Location</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $assessment->location_address }}<br>
                                        {{ $assessment->location_city }}, {{ $assessment->location_state }} {{ $assessment->location_postal_code }}
                                        @if($assessment->location_coordinates)
                                        <a href="https://maps.google.com/?q={{ $assessment->location_coordinates }}" target="_blank" class="ml-2 text-blue-600 hover:text-blue-800 text-xs">
                                            View on Map
                                        </a>
                                        @endif
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assessment Sections -->
                    @if($assessment->sections->isNotEmpty())
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Assessment Sections</h3>
                            <div class="space-y-4">
                                @foreach($assessment->sections->sortBy('sort_order') as $section)
                                <div class="border border-gray-200 rounded-lg">
                                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                        <div class="flex justify-between items-center">
                                            <h4 class="text-sm font-medium text-gray-900">{{ $section->name }}</h4>
                                            <div class="flex items-center space-x-2">
                                                @if($section->current_score && $section->max_score)
                                                <span class="text-sm text-gray-600">
                                                    {{ $section->current_score }}/{{ $section->max_score }}
                                                    ({{ round(($section->current_score / $section->max_score) * 100, 1) }}%)
                                                </span>
                                                @endif
                                                @if($section->status)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$section->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ ucfirst($section->status) }}
                                                </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-4 py-3">
                                        @if($section->description)
                                        <p class="text-sm text-gray-600 mb-3">{{ $section->description }}</p>
                                        @endif

                                        @if($section->quality_rating || $section->risk_level)
                                        <div class="flex space-x-4 mb-3">
                                            @if($section->quality_rating)
                                            <div class="text-sm">
                                                <span class="font-medium text-gray-500">Quality:</span>
                                                <span class="text-gray-900">{{ ucfirst($section->quality_rating) }}</span>
                                            </div>
                                            @endif
                                            @if($section->risk_level)
                                            <div class="text-sm">
                                                <span class="font-medium text-gray-500">Risk:</span>
                                                @php
                                                    $riskColors = [
                                                        'low' => 'text-green-600',
                                                        'medium' => 'text-yellow-600',
                                                        'high' => 'text-red-600',
                                                        'critical' => 'text-purple-600',
                                                    ];
                                                @endphp
                                                <span class="{{ $riskColors[$section->risk_level] ?? 'text-gray-900' }}">
                                                    {{ ucfirst($section->risk_level) }}
                                                </span>
                                            </div>
                                            @endif
                                        </div>
                                        @endif

                                        @if($section->items->isNotEmpty())
                                        <div class="space-y-2">
                                            <h5 class="text-sm font-medium text-gray-700">Items:</h5>
                                            @foreach($section->items->sortBy('sort_order') as $item)
                                            <div class="pl-4 border-l-2 border-gray-200">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-900">{{ $item->name }}</p>
                                                        @if($item->description)
                                                        <p class="text-sm text-gray-600">{{ $item->description }}</p>
                                                        @endif
                                                    </div>
                                                    @if($item->current_score && $item->max_score)
                                                    <span class="text-sm text-gray-500">
                                                        {{ $item->current_score }}/{{ $item->max_score }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif

                                        @if($section->notes)
                                        <div class="mt-3 p-3 bg-blue-50 rounded-md">
                                            <p class="text-sm text-blue-900"><strong>Notes:</strong> {{ $section->notes }}</p>
                                        </div>
                                        @endif

                                        @if($section->recommendations)
                                        <div class="mt-3 p-3 bg-yellow-50 rounded-md">
                                            <p class="text-sm text-yellow-900"><strong>Recommendations:</strong> {{ $section->recommendations }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Photos Section -->
                    @if($assessment->photos->isNotEmpty())
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Assessment Photos</h3>
                            @php
                                $photosByType = $assessment->photos()
                                    ->where('processing_status', 'completed')
                                    ->orderBy('photo_type')
                                    ->orderBy('sort_order')
                                    ->get()
                                    ->groupBy('photo_type');
                            @endphp

                            @foreach($photosByType as $photoType => $photos)
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">{{ ucwords(str_replace('_', ' ', $photoType)) }}</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                                    @foreach($photos as $photo)
                                    <div class="group relative">
                                        <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-gray-200">
                                            <img src="{{ asset('storage/' . $photo->file_path) }}"
                                                 alt="{{ $photo->description }}"
                                                 class="h-32 w-full object-cover group-hover:opacity-75 cursor-pointer"
                                                 onclick="openPhotoModal('{{ asset('storage/' . $photo->file_path) }}', '{{ addslashes($photo->description) }}')">
                                        </div>
                                        @if($photo->description)
                                        <p class="mt-1 text-xs text-gray-500">{{ $photo->description }}</p>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Progress Summary -->
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Progress Summary</h3>

                            @if($assessment->completion_percentage)
                            <div class="mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium text-gray-700">Completion</span>
                                    <span class="text-gray-900">{{ $assessment->completion_percentage }}%</span>
                                </div>
                                <div class="mt-1 w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $assessment->completion_percentage }}%"></div>
                                </div>
                            </div>
                            @endif

                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Total Sections</span>
                                    <span class="text-gray-900">{{ $assessment->sections->count() }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Completed</span>
                                    <span class="text-gray-900">{{ $assessment->sections->where('status', 'completed')->count() }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Photos</span>
                                    <span class="text-gray-900">{{ $assessment->photos->where('processing_status', 'completed')->count() }}</span>
                                </div>
                                @if($assessment->estimated_duration)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Estimated Time</span>
                                    <span class="text-gray-900">{{ $assessment->estimated_duration }} min</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Info -->
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Assignment</h3>
                            <div class="space-y-3">
                                @if($assessment->assignedTo)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $assessment->assignedTo->name }}</dd>
                                </div>
                                @endif
                                @if($assessment->lead)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Related Lead</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a href="{{ route('leads.show', $assessment->lead) }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $assessment->lead->customer_name }}
                                        </a>
                                    </dd>
                                </div>
                                @endif
                                @if($assessment->serviceTemplate)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Template Used</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $assessment->serviceTemplate->name }}</dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $assessment->created_at->format('d M Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Updated</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $assessment->updated_at->diffForHumans() }}</dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white shadow-sm sm:rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                @can('changeStatus', $assessment)
                                @if($assessment->status === 'scheduled')
                                <button type="button"
                                        onclick="changeStatus('in_progress')"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    Start Assessment
                                </button>
                                @elseif($assessment->status === 'in_progress')
                                <button type="button"
                                        onclick="changeStatus('completed')"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Mark Complete
                                </button>
                                @endif
                                @endcan

                                @can('uploadPhotos', $assessment)
                                <button type="button"
                                        onclick="openPhotoUpload()"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Upload Photos
                                </button>
                                @endcan

                                @if($assessment->status === 'completed')
                                <a href="{{ route('quotations.create', ['assessment_id' => $assessment->id]) }}"
                                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Create Quotation
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Modal -->
    <div id="photoModal" class="fixed inset-0 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closePhotoModal()"></div>
            <div class="inline-block max-w-4xl mx-auto mt-8 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:align-middle">
                <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900" id="photoModalTitle">Photo</h3>
                        <button type="button" onclick="closePhotoModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <img id="photoModalImage" src="" alt="" class="w-full h-auto rounded-lg">
                </div>
            </div>
        </div>
    </div>

    <script>
        function openPhotoModal(src, description) {
            document.getElementById('photoModalImage').src = src;
            document.getElementById('photoModalTitle').textContent = description || 'Photo';
            document.getElementById('photoModal').classList.remove('hidden');
        }

        function closePhotoModal() {
            document.getElementById('photoModal').classList.add('hidden');
        }

        function changeStatus(newStatus) {
            if (confirm('Are you sure you want to change the assessment status?')) {
                // Implementation would involve AJAX call to update status
                window.location.reload();
            }
        }

        function openPhotoUpload() {
            // Implementation would open photo upload modal or redirect to upload page
            alert('Photo upload functionality would be implemented here');
        }
    </script>
</x-app-layout>