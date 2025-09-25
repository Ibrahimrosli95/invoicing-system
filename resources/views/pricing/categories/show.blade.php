@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center space-x-4">
            <a href="{{ route('pricing.categories.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div class="flex-1">
                <div class="flex items-center space-x-3">
                    @if($category->color)
                        <div class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $category->color }}"></div>
                    @endif

                    @if($category->icon)
                        <span class="text-gray-400">
                            <i class="{{ $category->icon }}"></i>
                        </span>
                    @endif

                    <h1 class="text-3xl font-bold text-gray-900">{{ $category->name }}</h1>

                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                @if($category->description)
                    <p class="mt-1 text-lg text-gray-600">{{ $category->description }}</p>
                @endif

                @if($category->code)
                    <p class="mt-1 text-sm text-gray-500">Code: <span class="font-mono">{{ $category->code }}</span></p>
                @endif
            </div>

            <div class="flex items-center space-x-3">
                @can('update', $category)
                    <form method="POST" action="{{ route('pricing.categories.toggle-status', $category) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                onclick="return confirm('Are you sure you want to {{ $category->is_active ? 'deactivate' : 'activate' }} this category?')">
                            {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                @endcan

                @can('create', App\Models\PricingCategory::class)
                    <a href="{{ route('pricing.categories.duplicate', $category) }}"
                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                       onclick="return confirm('Are you sure you want to duplicate this category?')">
                        Duplicate
                    </a>
                @endcan

                @can('update', $category)
                    <a href="{{ route('pricing.categories.edit', $category) }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Edit Category
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Category Statistics -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Category Statistics</h3>
                    <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Items</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_items'] ?? 0 }}</dd>
                        </div>
                        <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Items</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['active_items'] ?? 0 }}</dd>
                        </div>
                        <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Subcategories</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['subcategories'] ?? 0 }}</dd>
                        </div>
                        <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Subcategories</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['active_subcategories'] ?? 0 }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Category Items -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Items in this Category</h3>
                        <a href="{{ route('pricing.index', ['category_id' => $category->id]) }}"
                           class="text-sm font-medium text-blue-600 hover:text-blue-500">
                            View All Items
                        </a>
                    </div>
                </div>

                @if($items->count() > 0)
                    <div class="overflow-hidden">
                        <ul class="divide-y divide-gray-200">
                            @foreach($items as $item)
                                <li class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center space-x-3">
                                                <h4 class="text-sm font-medium text-gray-900">{{ $item->name }}</h4>
                                                @if($item->code)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ $item->code }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($item->description)
                                                <p class="mt-1 text-sm text-gray-600">{{ Str::limit($item->description, 100) }}</p>
                                            @endif

                                            <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                                <span>Base Price: RM {{ number_format($item->selling_price, 2) }}</span>
                                                <span>Created {{ $item->created_at->diffForHumans() }}</span>
                                                @if($item->created_by)
                                                    <span>by {{ $item->createdBy->name ?? 'System' }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $item->is_active ? 'Active' : 'Inactive' }}
                                            </span>

                                            @can('view', $item)
                                                <a href="{{ route('pricing.show', $item) }}"
                                                   class="text-sm text-blue-600 hover:text-blue-900">
                                                    View
                                                </a>
                                            @endcan
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        @if($items->hasPages())
                            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                                {{ $items->links() }}
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No items in this category</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by adding items to this category.</p>
                        @can('create', App\Models\PricingItem::class)
                            <div class="mt-6">
                                <a href="{{ route('pricing.create', ['category_id' => $category->id]) }}"
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Add Item
                                </a>
                            </div>
                        @endcan
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Category Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Category Information</h3>
                    <dl class="space-y-4">
                        @if($category->parent)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Parent Category</dt>
                                <dd class="mt-1">
                                    <a href="{{ route('pricing.categories.show', $category->parent) }}"
                                       class="text-sm text-blue-600 hover:text-blue-900">
                                        {{ $category->parent->name }}
                                    </a>
                                </dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $category->sort_order }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $category->created_at->format('M j, Y \a\t g:i A') }}</dd>
                        </div>

                        @if($category->createdBy)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created By</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $category->createdBy->name }}</dd>
                            </div>
                        @endif

                        @if($category->updated_at != $category->created_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $category->updated_at->format('M j, Y \a\t g:i A') }}</dd>
                            </div>

                            @if($category->updatedBy)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Updated By</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $category->updatedBy->name }}</dd>
                                </div>
                            @endif
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Subcategories -->
            @if($category->children->count() > 0)
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Subcategories</h3>
                        <ul class="space-y-3">
                            @foreach($category->children as $child)
                                <li class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        @if($child->color)
                                            <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $child->color }}"></div>
                                        @endif

                                        <a href="{{ route('pricing.categories.show', $child) }}"
                                           class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                            {{ $child->name }}
                                        </a>

                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $child->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $child->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>

                                    <span class="text-xs text-gray-500">
                                        {{ $child->activeItems()->count() }} items
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        @can('create', App\Models\PricingItem::class)
                            <a href="{{ route('pricing.create', ['category_id' => $category->id]) }}"
                               class="w-full flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Add Item to Category
                            </a>
                        @endcan

                        <a href="{{ route('pricing.index', ['category_id' => $category->id]) }}"
                           class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            View All Items
                        </a>

                        @can('create', App\Models\PricingCategory::class)
                            <a href="{{ route('pricing.categories.create', ['parent_id' => $category->id]) }}"
                               class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Add Subcategory
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection