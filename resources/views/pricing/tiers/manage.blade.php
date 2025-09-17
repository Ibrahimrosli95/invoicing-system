<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Tier Pricing - {{ $item->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Base Price: <span class="font-medium">RM {{ number_format($item->unit_price, 2) }}</span>
                    @if($item->item_code)
                        • Code: {{ $item->item_code }}
                    @endif
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('pricing.index') }}" 
                   class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm font-medium">
                    ← Back to Pricing
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Analytics Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                @foreach($analytics as $segmentName => $data)
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-3" style="background-color: {{ $data['segment_color'] ?? '#6B7280' }}"></div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $segmentName }}</h3>
                        </div>
                        <div class="mt-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Default Discount:</span>
                                <span class="font-medium">{{ $data['default_discount'] }}%</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tier Count:</span>
                                <span class="font-medium">{{ $data['tier_count'] }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Price (Qty 1):</span>
                                <span class="font-medium">RM {{ number_format($data['price_for_qty_1']['unit_price'], 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Price (Qty 100):</span>
                                <span class="font-medium">RM {{ number_format($data['price_for_qty_100']['unit_price'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Tier Management Tabs -->
            <div class="bg-white rounded-lg shadow">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6" x-data="{ activeTab: '{{ $segments->first()->id ?? '' }}' }">
                        @foreach($segments as $segment)
                            <button @click="activeTab = '{{ $segment->id }}'"
                                   :class="activeTab === '{{ $segment->id }}' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                   class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 rounded-full mr-2" style="background-color: {{ $segment->color }}"></div>
                                    {{ $segment->name }}
                                    @if($tiersBySegment->has($segment->id))
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $tiersBySegment->get($segment->id)->count() }}
                                        </span>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </nav>
                </div>

                @foreach($segments as $segment)
                    <div x-show="activeTab === '{{ $segment->id }}'" class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ $segment->name }} Pricing Tiers
                                <span class="ml-2 text-sm text-gray-500">
                                    (Default {{ $segment->default_discount_percentage }}% discount)
                                </span>
                            </h3>
                            <div class="flex space-x-3">
                                <button onclick="generateSuggestedTiers({{ $item->id }}, {{ $segment->id }})"
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Generate Suggested Tiers
                                </button>
                                <button onclick="showAddTierModal({{ $segment->id }})"
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                    Add Tier
                                </button>
                            </div>
                        </div>

                        <!-- Existing Tiers -->
                        @if($tiersBySegment->has($segment->id) && $tiersBySegment->get($segment->id)->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Range</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Savings vs Base</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($tiersBySegment->get($segment->id) as $tier)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $tier->getQuantityRange() }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    RM {{ number_format($tier->unit_price, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    @if($tier->discount_percentage)
                                                        {{ $tier->discount_percentage }}%
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    @php
                                                        $savings = max(0, $item->unit_price - $tier->unit_price);
                                                        $savingsPercent = $item->unit_price > 0 ? ($savings / $item->unit_price) * 100 : 0;
                                                    @endphp
                                                    @if($savings > 0)
                                                        <span class="text-green-600 font-medium">
                                                            RM {{ number_format($savings, 2) }} 
                                                            ({{ number_format($savingsPercent, 1) }}%)
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex space-x-3">
                                                        <button onclick="editTier({{ $tier->id }})" 
                                                               class="text-blue-600 hover:text-blue-900">Edit</button>
                                                        <form method="POST" action="{{ route('pricing.destroy-tier', [$item, $tier]) }}" 
                                                              class="inline" onsubmit="return confirm('Delete this tier?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <div class="text-gray-400 text-sm">
                                    No pricing tiers defined for {{ $segment->name }}
                                </div>
                                <button onclick="generateSuggestedTiers({{ $item->id }}, {{ $segment->id }})"
                                       class="mt-4 text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Generate Suggested Tiers
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Add Tier Modal -->
    <div id="addTierModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 text-center mb-4">Add Pricing Tier</h3>
                <form id="addTierForm" method="POST" action="{{ route('pricing.store-tier', $item) }}">
                    @csrf
                    <input type="hidden" id="modal_segment_id" name="customer_segment_id">
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Min Quantity</label>
                                <input type="number" name="min_quantity" required min="1" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Max Quantity</label>
                                <input type="number" name="max_quantity" min="1"
                                       placeholder="Leave empty for unlimited"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit Price (RM)</label>
                            <input type="number" name="unit_price" required min="0.01" step="0.01"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Discount Percentage (Optional)</label>
                            <input type="number" name="discount_percentage" min="0" max="100" step="0.01"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                            <textarea name="notes" rows="3"
                                     class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeAddTierModal()" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm">
                            Cancel
                        </button>
                        <button type="submit" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                            Add Tier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Create Suggested Tiers Modal -->
    <div id="suggestedTiersModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-10 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 text-center mb-4">Suggested Pricing Tiers</h3>
                <div id="suggestedTiersContent"></div>
            </div>
        </div>
    </div>

    <script>
        function showAddTierModal(segmentId) {
            document.getElementById('modal_segment_id').value = segmentId;
            document.getElementById('addTierModal').classList.remove('hidden');
        }

        function closeAddTierModal() {
            document.getElementById('addTierModal').classList.add('hidden');
            document.getElementById('addTierForm').reset();
        }

        function generateSuggestedTiers(itemId, segmentId) {
            fetch(`{{ route('pricing.generate-suggested-tiers', $item) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    customer_segment_id: segmentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuggestedTiersModal(data.tiers, itemId, segmentId);
                }
            });
        }

        function showSuggestedTiersModal(tiers, itemId, segmentId) {
            let html = `
                <form method="POST" action="{{ route('pricing.bulk-create-tiers', $item) }}">
                    @csrf
                    <input type="hidden" name="customer_segment_id" value="${segmentId}">
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <input type="checkbox" id="selectAll" onchange="toggleAll(this)"> Select
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Range</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">`;
            
            tiers.forEach((tier, index) => {
                const range = tier.max_quantity ? `${tier.min_quantity}-${tier.max_quantity}` : `${tier.min_quantity}+`;
                html += `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="selected_tiers[]" value="${index}" class="tier-checkbox">
                            <input type="hidden" name="tiers[${index}][min_quantity]" value="${tier.min_quantity}">
                            <input type="hidden" name="tiers[${index}][max_quantity]" value="${tier.max_quantity || ''}">
                            <input type="hidden" name="tiers[${index}][unit_price]" value="${tier.unit_price}">
                            <input type="hidden" name="tiers[${index}][discount_percentage]" value="${tier.discount_percentage}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${range}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">RM ${parseFloat(tier.unit_price).toFixed(2)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tier.discount_percentage}%</td>
                        <td class="px-6 py-4 text-sm text-gray-500">${tier.description}</td>
                    </tr>`;
            });
            
            html += `
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeSuggestedTiersModal()" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg text-sm">
                            Cancel
                        </button>
                        <button type="submit" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                            Create Selected Tiers
                        </button>
                    </div>
                </form>`;
            
            document.getElementById('suggestedTiersContent').innerHTML = html;
            document.getElementById('suggestedTiersModal').classList.remove('hidden');
        }

        function closeSuggestedTiersModal() {
            document.getElementById('suggestedTiersModal').classList.add('hidden');
        }

        function toggleAll(checkbox) {
            const tierCheckboxes = document.querySelectorAll('.tier-checkbox');
            tierCheckboxes.forEach(cb => cb.checked = checkbox.checked);
        }

        // Close modals on outside click
        window.onclick = function(event) {
            const addModal = document.getElementById('addTierModal');
            const suggestedModal = document.getElementById('suggestedTiersModal');
            
            if (event.target == addModal) {
                closeAddTierModal();
            }
            if (event.target == suggestedModal) {
                closeSuggestedTiersModal();
            }
        }
    </script>
</x-app-layout>