@extends('layouts.app')

@section('title', 'Customer Segments')

@section('header')
<div class="bg-white border-b border-gray-200 px-6 py-4">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Customer Segments
        </h2>
        <button onclick="showAddSegmentModal()"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            Add Segment
        </button>
    </div>
</div>
@endsection

@section('content')

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Segments Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($segments as $segment)
                    <div class="bg-white rounded-lg shadow-md p-6 {{ !$segment->is_active ? 'opacity-60' : '' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center">
                                <div class="w-4 h-4 rounded-full mr-3 flex-shrink-0" 
                                     style="background-color: {{ $segment->color ?? '#6B7280' }}"></div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $segment->name }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">{{ $segment->description }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                @unless($segment->is_active)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactive
                                    </span>
                                @endunless
                            </div>
                        </div>

                        <div class="mt-6 space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Default Discount</span>
                                <span class="text-sm font-medium text-gray-900">{{ $segment->default_discount_percentage }}%</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Active Tiers</span>
                                <span class="text-sm font-medium text-gray-900">{{ $segment->active_tiers }}</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Created</span>
                                <span class="text-sm text-gray-500">{{ $segment->created_at->format('M d, Y') }}</span>
                            </div>

                            @if($segment->settings && isset($segment->settings['default_payment_terms']))
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Payment Terms</span>
                                    <span class="text-sm text-gray-500">
                                        {{ ucfirst(str_replace('_', ' ', $segment->settings['default_payment_terms'])) }}
                                    </span>
                                </div>
                            @endif

                            @if($segment->settings && isset($segment->settings['credit_limit']))
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Credit Limit</span>
                                    <span class="text-sm text-gray-500">
                                        @if($segment->settings['credit_limit'])
                                            RM {{ number_format($segment->settings['credit_limit']) }}
                                        @else
                                            No limit
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-6 flex justify-between items-center">
                            <div class="flex space-x-2">
                                <button onclick="editSegment({{ $segment->id }}, '{{ $segment->name }}', '{{ $segment->description }}', {{ $segment->default_discount_percentage }}, '{{ $segment->color }}')"
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Edit
                                </button>
                                
                                <form method="POST" action="{{ route('pricing.toggle-segment', $segment) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                           class="text-{{ $segment->is_active ? 'yellow' : 'green' }}-600 hover:text-{{ $segment->is_active ? 'yellow' : 'green' }}-800 text-sm font-medium">
                                        {{ $segment->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>

                            @if($segment->createdBy)
                                <span class="text-xs text-gray-400">
                                    by {{ $segment->createdBy->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <div class="text-gray-400 text-lg mb-4">No customer segments found</div>
                        <button onclick="showAddSegmentModal()" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                            Create Your First Segment
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Add/Edit Segment Modal -->
    <div id="segmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900 text-center mb-4">Add Customer Segment</h3>
                <form id="segmentForm" method="POST" action="{{ route('pricing.store-segment') }}">
                    @csrf
                    <div id="methodField"></div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Segment Name</label>
                            <input type="text" name="name" id="segment_name" required maxlength="100"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="segment_description" rows="3" maxlength="500"
                                     class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Default Discount Percentage</label>
                            <div class="mt-1 relative">
                                <input type="number" name="default_discount_percentage" id="segment_discount" 
                                       required min="0" max="100" step="0.01"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm pr-8">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <span class="text-gray-500 text-sm">%</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Color</label>
                            <div class="mt-1 flex items-center space-x-3">
                                <input type="color" name="color" id="segment_color" 
                                       class="h-10 w-20 border border-gray-300 rounded-md">
                                <span class="text-sm text-gray-500">Choose a color to identify this segment</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeSegmentModal()" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm">
                            Cancel
                        </button>
                        <button type="submit" id="submitButton"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                            Create Segment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showAddSegmentModal() {
            document.getElementById('modalTitle').textContent = 'Add Customer Segment';
            document.getElementById('segmentForm').action = "{{ route('pricing.store-segment') }}";
            document.getElementById('methodField').innerHTML = '';
            document.getElementById('submitButton').textContent = 'Create Segment';
            
            // Reset form
            document.getElementById('segmentForm').reset();
            document.getElementById('segment_color').value = '#3B82F6'; // Default blue
            
            document.getElementById('segmentModal').classList.remove('hidden');
        }

        function editSegment(id, name, description, discount, color) {
            document.getElementById('modalTitle').textContent = 'Edit Customer Segment';
            document.getElementById('segmentForm').action = `/pricing/segments/${id}`;
            document.getElementById('methodField').innerHTML = '@method("PATCH")';
            document.getElementById('submitButton').textContent = 'Update Segment';
            
            // Fill form
            document.getElementById('segment_name').value = name;
            document.getElementById('segment_description').value = description || '';
            document.getElementById('segment_discount').value = discount;
            document.getElementById('segment_color').value = color || '#3B82F6';
            
            document.getElementById('segmentModal').classList.remove('hidden');
        }

        function closeSegmentModal() {
            document.getElementById('segmentModal').classList.add('hidden');
            document.getElementById('segmentForm').reset();
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('segmentModal');
            if (event.target == modal) {
                closeSegmentModal();
            }
        }
    </script>
@endsection