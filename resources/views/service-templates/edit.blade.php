@extends('layouts.app')

@section('title', 'Edit Service Template')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Service Template</h1>
                <p class="mt-1 text-sm text-gray-600">Update the service template: {{ $serviceTemplate->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('service-templates.show', $serviceTemplate) }}"
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

    <form method="POST" action="{{ route('service-templates.update', $serviceTemplate) }}" x-data="serviceTemplateForm()">
        @csrf
        @method('PUT')

        <!-- Error Summary -->
        @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were {{ $errors->count() }} error(s) with your submission:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $serviceTemplate->name) }}" required
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                   placeholder="e.g., Standard Installation Package">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <div class="flex gap-2">
                                <select name="category_id" id="category_id" required
                                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <option value="">Select Category</option>
                                    @foreach($categories ?? [] as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $serviceTemplate->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @can('create', App\Models\ServiceCategory::class)
                                <button type="button" onclick="openQuickAddModal()"
                                        class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                                @endcan
                            </div>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" id="description" rows="3"
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                      placeholder="Describe what this template includes...">{{ old('description', $serviceTemplate->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="estimated_hours" class="block text-sm font-medium text-gray-700 mb-1">Estimated Hours</label>
                            <input type="number" name="estimated_hours" id="estimated_hours" value="{{ old('estimated_hours', $serviceTemplate->estimated_hours) }}" step="0.5" min="0"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                   placeholder="8.0">
                            @error('estimated_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="base_price" class="block text-sm font-medium text-gray-700 mb-1">Base Price (RM)</label>
                            <input type="number" name="base_price" id="base_price" value="{{ old('base_price', $serviceTemplate->base_price) }}" step="0.01" min="0"
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

                                                <!-- Row 1: Description and Details -->
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                                                        <input type="text"
                                                               :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][description]'"
                                                               x-model="item.description"
                                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                               placeholder="Item description"
                                                               required>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Details</label>
                                                        <input type="text"
                                                               :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][details]'"
                                                               x-model="item.details"
                                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                               placeholder="Additional details">
                                                    </div>
                                                </div>

                                                <!-- Row 2: Unit, Quantity, Unit Price -->
                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Unit</label>
                                                        <input type="text"
                                                               :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][unit]'"
                                                               x-model="item.unit"
                                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                               placeholder="e.g., sqft, kg">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Default Qty</label>
                                                        <input type="number"
                                                               :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][default_quantity]'"
                                                               x-model="item.default_quantity"
                                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                               min="0.01" step="0.01">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Default Unit Price (RM)</label>
                                                        <input type="number"
                                                               :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][default_unit_price]'"
                                                               x-model="item.default_unit_price"
                                                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                                               step="0.01" min="0">
                                                    </div>
                                                    <div class="flex items-end">
                                                        <div class="flex-1">
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                                                Calculated Amount
                                                            </label>
                                                            <div class="w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-sm text-gray-700"
                                                                 x-text="'RM ' + (parseFloat(item.default_quantity || 0) * parseFloat(item.default_unit_price || 0)).toFixed(2)">
                                                            </div>
                                                        </div>
                                                        <button type="button" @click="removeItem(sectionIndex, itemIndex)"
                                                                class="ml-2 text-red-600 hover:text-red-900">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Optional Amount Override -->
                                                <div class="mt-3 pt-3 border-t border-gray-200">
                                                    <div class="flex items-center gap-3">
                                                        <label class="flex items-center">
                                                            <input type="checkbox"
                                                                   x-model="item.amount_manually_edited"
                                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                            <span class="ml-2 text-xs text-gray-700">Override calculated amount</span>
                                                        </label>
                                                        <div x-show="item.amount_manually_edited" class="flex-1">
                                                            <input type="number"
                                                                   :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][amount_override]'"
                                                                   x-model="item.amount_override"
                                                                   class="w-full max-w-xs rounded-md border-amber-400 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm"
                                                                   placeholder="Override amount"
                                                                   step="0.01" min="0">
                                                        </div>
                                                    </div>
                                                    <!-- Hidden field for amount_manually_edited -->
                                                    <input type="hidden"
                                                           :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][amount_manually_edited]'"
                                                           :value="item.amount_manually_edited ? '1' : '0'">
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
                                            {{ (collect(old('applicable_teams', $serviceTemplate->applicable_teams ?? []))->contains($team->id)) ? 'selected' : '' }}>
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
                                   {{ old('is_active', $serviceTemplate->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                Active Template
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="hidden" name="requires_approval" value="0">
                            <input type="checkbox" name="requires_approval" id="requires_approval" value="1"
                                   {{ old('requires_approval', $serviceTemplate->requires_approval) ? 'checked' : '' }}
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
                            <span class="text-sm font-medium text-gray-900">{{ $serviceTemplate->usage_count ?? 0 }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Created</span>
                            <span class="text-sm font-medium text-gray-900">{{ $serviceTemplate->created_at->format('M j, Y') }}</span>
                        </div>

                        @if($serviceTemplate->last_used_at)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Last Used</span>
                                <span class="text-sm font-medium text-gray-900">{{ $serviceTemplate->last_used_at->diffForHumans() }}</span>
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

                        <a href="{{ route('service-templates.show', $serviceTemplate) }}"
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
        sections: {!! json_encode($serviceTemplate->sections->map(function($section) {
            return [
                'id' => $section->id,
                'name' => $section->name,
                'description' => $section->description,
                'sort_order' => $section->sort_order,
                'existing' => true,
                'items' => $section->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'description' => $item->description ?? '',
                        'unit' => $item->unit ?? '',
                        'default_quantity' => $item->default_quantity ?? 1,
                        'default_unit_price' => $item->default_unit_price ?? 0,
                        'existing' => true
                    ];
                })->toArray()
            ];
        })->toArray()) !!},
        sectionIdCounter: {{ $serviceTemplate->sections->max('id') + 1 ?? 1 }},
        itemIdCounter: {{ $serviceTemplate->sections->flatMap->items->max('id') + 1 ?? 1 }},

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
                description: '',
                details: '',
                unit: '',
                default_quantity: 1,
                default_unit_price: 0,
                amount_override: null,
                amount_manually_edited: false,
                existing: false
            });
        },

        removeItem(sectionIndex, itemIndex) {
            this.sections[sectionIndex].items.splice(itemIndex, 1);
        }
    }
}

// Quick-add Category Modal Functions
function openQuickAddModal() {
    document.getElementById('quickAddModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeQuickAddModal() {
    document.getElementById('quickAddModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('quickAddForm').reset();
    document.getElementById('quickAddError').classList.add('hidden');
}

function submitQuickAddCategory(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const errorDiv = document.getElementById('quickAddError');

    // Disable submit button
    submitButton.disabled = true;
    submitButton.innerHTML = '<svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Creating...';

    // Hide previous errors
    errorDiv.classList.add('hidden');

    fetch('/api/service-categories/quick-add', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new category to dropdown
            const categorySelect = document.getElementById('category_id');
            const newOption = new Option(data.category.name, data.category.id, false, true);
            categorySelect.add(newOption);

            // Show success message
            const successDiv = document.createElement('div');
            successDiv.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50';
            successDiv.innerHTML = `
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>${data.message}</span>
                </div>
            `;
            document.body.appendChild(successDiv);
            setTimeout(() => successDiv.remove(), 3000);

            // Close modal
            closeQuickAddModal();
        } else {
            throw new Error(data.message || 'Failed to create category');
        }
    })
    .catch(error => {
        errorDiv.textContent = error.message || 'An error occurred while creating the category';
        errorDiv.classList.remove('hidden');
    })
    .finally(() => {
        // Re-enable submit button
        submitButton.disabled = false;
        submitButton.innerHTML = 'Create Category';
    });
}
</script>

<!-- Quick-add Category Modal -->
<div id="quickAddModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Quick Add Category</h3>
            <button type="button" onclick="closeQuickAddModal()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="quickAddForm" onsubmit="submitQuickAddCategory(event)">
            @csrf

            <div class="space-y-4">
                <!-- Error Message -->
                <div id="quickAddError" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm"></div>

                <!-- Category Name -->
                <div>
                    <label for="quickAddName" class="block text-sm font-medium text-gray-700 mb-1">
                        Category Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="quickAddName" required
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                           placeholder="e.g., Waterproofing">
                </div>

                <!-- Category Color -->
                <div>
                    <label for="quickAddColor" class="block text-sm font-medium text-gray-700 mb-1">
                        Category Color
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="color" id="quickAddColor" value="#3B82F6"
                               class="h-10 w-20 rounded border border-gray-300 cursor-pointer">
                        <input type="text" id="quickAddColorText" value="#3B82F6"
                               class="w-28 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                               readonly>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" onclick="closeQuickAddModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Cancel
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Create Category
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Sync color picker with text input for quick-add modal
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('quickAddColor');
    const colorText = document.getElementById('quickAddColorText');

    if (colorInput && colorText) {
        colorInput.addEventListener('input', function() {
            colorText.value = this.value.toUpperCase();
        });
    }

    // Close modal when clicking outside
    document.getElementById('quickAddModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeQuickAddModal();
        }
    });
});
</script>
@endsection