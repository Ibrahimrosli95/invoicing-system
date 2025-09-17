<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Pricing Book
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('pricing.segments') }}" 
                   class="bg-purple-100 hover:bg-purple-200 text-purple-800 px-4 py-2 rounded-lg text-sm font-medium">
                    Manage Segments
                </a>
                <a href="{{ route('pricing.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Add Item
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-500">Total Items</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_items'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-500">Active Items</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['active_items'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-500">Featured</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['featured_items'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-500">Categories</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_categories'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L4.316 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5">
                            <p class="text-sm font-medium text-gray-500">Need Review</p>
                            <p class="text-2xl font-semibold text-gray-900">{{ $stats['needs_price_review'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="{{ route('pricing.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Item name, code, description..."
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <select name="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">All Items</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="featured" {{ request('status') == 'featured' ? 'selected' : '' }}>Featured</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price Range</label>
                            <div class="flex space-x-2">
                                <input type="number" name="price_min" value="{{ request('price_min') }}" 
                                       placeholder="Min" step="0.01"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <input type="number" name="price_max" value="{{ request('price_max') }}" 
                                       placeholder="Max" step="0.01"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>

                        <div class="flex items-end space-x-2">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                Filter
                            </button>
                            <a href="{{ route('pricing.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg text-sm font-medium">
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Items Grid/List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        Pricing Items ({{ $items->total() }})
                    </h3>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('pricing.export') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Export CSV
                        </a>
                        <div class="flex border border-gray-300 rounded-lg">
                            <button onclick="setViewMode('grid')" 
                                   class="px-3 py-1 text-sm {{ $viewMode == 'grid' ? 'bg-blue-100 text-blue-600' : 'text-gray-600' }} rounded-l-lg">
                                Grid
                            </button>
                            <button onclick="setViewMode('list')" 
                                   class="px-3 py-1 text-sm {{ $viewMode == 'list' ? 'bg-blue-100 text-blue-600' : 'text-gray-600' }} rounded-r-lg border-l border-gray-300">
                                List
                            </button>
                        </div>
                    </div>
                </div>

                @if($items->count() > 0)
                    @if($viewMode == 'grid')
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 p-6">
                            @foreach($items as $item)
                                <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                    @if($item->hasImage())
                                        <div class="w-full h-32 bg-gray-200 rounded-lg mb-4 flex items-center justify-center overflow-hidden">
                                            <img src="{{ $item->getImageUrl() }}" alt="{{ $item->name }}" class="max-h-full max-w-full object-contain">
                                        </div>
                                    @endif
                                    
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-start">
                                            <h4 class="font-medium text-gray-900 text-sm">{{ $item->name }}</h4>
                                            @if($item->hasTierPricing())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    Tiered
                                                </span>
                                            @endif
                                        </div>
                                        
                                        @if($item->item_code)
                                            <p class="text-xs text-gray-500">{{ $item->item_code }}</p>
                                        @endif
                                        
                                        <p class="text-sm font-semibold text-gray-900">RM {{ number_format($item->unit_price, 2) }}</p>
                                        
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs text-gray-500">{{ $item->category->name ?? 'Uncategorized' }}</span>
                                            <div class="flex space-x-2">
                                                <a href="{{ route('pricing.show', $item) }}" 
                                                   class="text-blue-600 hover:text-blue-800 text-xs">View</a>
                                                @if($item->hasTierPricing())
                                                    <a href="{{ route('pricing.manage-tiers', $item) }}" 
                                                       class="text-purple-600 hover:text-purple-800 text-xs">Tiers</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tier Pricing</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($items as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($item->hasImage())
                                                        <div class="w-10 h-10 bg-gray-200 rounded-lg mr-4 overflow-hidden flex-shrink-0">
                                                            <img src="{{ $item->getImageUrl() }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                                        @if($item->item_code)
                                                            <div class="text-sm text-gray-500">{{ $item->item_code }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $item->category->name ?? 'Uncategorized' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                RM {{ number_format($item->unit_price, 2) }}
                                                <div class="text-xs text-gray-500">per {{ $item->unit }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($item->hasTierPricing())
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                        {{ $item->getSegmentsWithTiers()->count() }} segments
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-400">No tiers</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($item->is_active)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex space-x-3">
                                                    <a href="{{ route('pricing.show', $item) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                                    <a href="{{ route('pricing.manage-tiers', $item) }}" class="text-purple-600 hover:text-purple-900">Tiers</a>
                                                    <a href="{{ route('pricing.edit', $item) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $items->withQueryString()->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 text-lg mb-4">No pricing items found</div>
                        <a href="{{ route('pricing.create') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                            Add Your First Item
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function setViewMode(mode) {
            const url = new URL(window.location);
            url.searchParams.set('view', mode);
            window.location.href = url.toString();
        }
    </script>
</x-app-layout>