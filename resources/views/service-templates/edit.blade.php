@extends('layouts.app')

@section('title', 'Edit Service Template')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Service Template</h1>
                <p class="mt-1 text-sm text-gray-600">Update the service template: {{ $template->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('service-templates.show', $template) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View Template
                </a>
                <a href="{{ route('service-templates.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                    Back to Templates
                </a>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('service-templates.update', $template) }}" x-data="serviceTemplateForm()">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $template->name) }}" required
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                   placeholder="e.g., Standard Installation Package">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category" id="category" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select Category</option>
                                @foreach($categories ?? [] as $key => $label)
                                    <option value="{{ $key }}" {{ old('category', $template->category) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" id="description" rows="3"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                      placeholder="Describe what this template includes...">{{ old('description', $template->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="estimated_hours" class="block text-sm font-medium text-gray-700 mb-1">Estimated Hours</label>
                            <input type="number" name="estimated_hours" id="estimated_hours" value="{{ old('estimated_hours', $template->estimated_hours) }}" step="0.5" min="0"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                   placeholder="8.0">
                            @error('estimated_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="base_price" class="block text-sm font-medium text-gray-700 mb-1">Base Price (RM)</label>
                            <input type="number" name="base_price" id="base_price" value="{{ old('base_price', $template->base_price) }}" step="0.01" min="0"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                   placeholder="1500.00">
                            @error('base_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Template Sections -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-medium text-gray-900">Template Sections</h2>
                        <button type="button" @click="addSection()"
                                class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Section
                        </button>
                    </div>

                    <div id="sections-container" class="space-y-4">
                        <template x-for="(section, sectionIndex) in sections" :key="section.id">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-medium text-gray-900" x-text="'Section ' + (sectionIndex + 1)"></h3>
                                    <button type="button" @click="removeSection(sectionIndex)"
                                            class="text-red-600 hover:text-red-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Hidden ID field for existing sections -->
                                <input type="hidden" :name="'sections[' + sectionIndex + '][id]'" x-model="section.id" x-show="section.existing">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Section Name</label>
                                        <input type="text"
                                               :name="'sections[' + sectionIndex + '][name]'"
                                               x-model="section.name"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                               placeholder="e.g., Materials">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                        <input type="number"
                                               :name="'sections[' + sectionIndex + '][sort_order]'"
                                               x-model="section.sort_order"
                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                               min="1">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea :name="'sections[' + sectionIndex + '][description]'"
                                              x-model="section.description"
                                              rows="2"
                                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                              placeholder="Describe this section..."></textarea>
                                </div>

                                <!-- Section Items -->
                                <div class="border-t border-gray-100 pt-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-sm font-medium text-gray-700">Section Items</h4>
                                        <button type="button" @click="addItem(sectionIndex)"
                                                class="inline-flex items-center px-2 py-1 bg-green-600 border border-transparent rounded text-xs font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add Item
                                        </button>
                                    </div>

                                    <div class="space-y-3">
                                        <template x-for="(item, itemIndex) in section.items" :key="item.id">
                                            <div class="bg-gray-50 rounded p-3">
                                                <!-- Hidden ID field for existing items -->
                                                <input type="hidden" :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][id]'" x-model="item.id" x-show="item.existing">

                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Item Name</label>
                                                        <input type="text"
                                                               :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][name]'"
                                                               x-model="item.name"
                                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                               placeholder="Item name">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
                                                        <input type="number"
                                                               :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][quantity]'"
                                                               x-model="item.quantity"
                                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                               min="1">
                                                    </div>
                                                    <div class="flex items-end">
                                                        <div class="flex-1">
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Unit Price (RM)</label>
                                                            <input type="number"
                                                                   :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][unit_price]'"
                                                                   x-model="item.unit_price"
                                                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                                   step="0.01" min="0">
                                                        </div>
                                                        <button type="button" @click="removeItem(sectionIndex, itemIndex)"
                                                                class="ml-2 text-red-600 hover:text-red-900">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mt-2">
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                                                    <textarea :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][description]'"
                                                              x-model="item.description"
                                                              rows="1"
                                                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                              placeholder="Optional item description..."></textarea>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Settings -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Settings</h2>

                    <div class="space-y-4">
                        <div>
                            <label for="applicable_teams" class="block text-sm font-medium text-gray-700 mb-1">Applicable Teams</label>
                            <select name="applicable_teams[]" id="applicable_teams" multiple
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                @foreach($teams ?? [] as $team)
                                    <option value="{{ $team->id }}"
                                            {{ (collect(old('applicable_teams', $template->applicable_teams ?? []))->contains($team->id)) ? 'selected' : '' }}>
                                        {{ $team->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple teams</p>
                            @error('applicable_teams')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active Template
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="hidden" name="requires_approval" value="0">
                            <input type="checkbox" name="requires_approval" id="requires_approval" value="1"
                                   {{ old('requires_approval', $template->requires_approval) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="requires_approval" class="ml-2 block text-sm text-gray-900">
                                Requires Approval
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Template Statistics -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Template Statistics</h2>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Times Used</span>
                            <span class="text-sm font-medium text-gray-900">{{ $template->usage_count ?? 0 }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Created</span>
                            <span class="text-sm font-medium text-gray-900">{{ $template->created_at->format('M j, Y') }}</span>
                        </div>

                        @if($template->last_used_at)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Last Used</span>
                                <span class="text-sm font-medium text-gray-900">{{ $template->last_used_at->diffForHumans() }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Actions</h2>

                    <div class="space-y-3">
                        <button type="submit"
                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Update Template
                        </button>

                        <a href="{{ route('service-templates.show', $template) }}"
                           class="w-full bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium text-center hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 block">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function serviceTemplateForm() {
    return {
        sections: @json($template->sections->map(function($section) {
            return [
                'id' => $section->id,
                'name' => $section->name,
                'description' => $section->description,
                'sort_order' => $section->sort_order,
                'existing' => true,
                'items' => $section->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'existing' => true
                    ];
                })->toArray()
            ];
        })->toArray()),
        sectionIdCounter: {{ $template->sections->max('id') + 1 ?? 1 }},
        itemIdCounter: {{ $template->sections->flatMap->items->max('id') + 1 ?? 1 }},

        addSection() {
            this.sections.push({
                id: 'new_' + this.sectionIdCounter++,
                name: '',
                description: '',
                sort_order: this.sections.length + 1,
                existing: false,
                items: []
            });
        },

        removeSection(index) {
            this.sections.splice(index, 1);
        },

        addItem(sectionIndex) {
            this.sections[sectionIndex].items.push({
                id: 'new_' + this.itemIdCounter++,
                name: '',
                description: '',
                quantity: 1,
                unit_price: 0,
                existing: false
            });
        },

        removeItem(sectionIndex, itemIndex) {
            this.sections[sectionIndex].items.splice(itemIndex, 1);
        }
    }
}
</script>
@endsection