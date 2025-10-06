# Service Template Enhancement Plan
## Project Template System Integration

**Date:** 2025-10-06
**Status:** Planning Phase
**Complexity:** High
**Estimated Duration:** 8-12 hours (2-3 sessions)

---

## 📋 Executive Summary

This plan outlines the enhancement of the existing Service Template system to support full project templates with improved user experience for quotation and invoice builders. The enhancement will allow users to:

1. Load entire project templates with all sections and items
2. Pick individual sections from different templates
3. Sort and reorder sections dynamically
4. Create items manually with the new column structure
5. Override calculated amounts when needed

---

## 🎯 Current State Analysis

### Existing Architecture

**Service Template Structure (3-tier hierarchy):**
```
ServiceTemplate (Project Template)
  └─ ServiceTemplateSection (Section/Phase)
       └─ ServiceTemplateItem (Task/Step)
```

**Current Item Fields:**
- `description` (500 chars) - Task description
- `unit` (20 chars) - Unit of measurement (Nos, sqm, etc.)
- `default_quantity` (decimal) - Default quantity
- `default_unit_price` (decimal) - Default unit price/rate
- `cost_price` (decimal) - Cost for margin calculation
- `minimum_price` (decimal) - Minimum allowed price

**Current Capabilities:**
✅ Load entire templates into service builders
✅ Manual section/item creation
✅ Basic sorting by sort_order
❌ Pick individual sections from templates
❌ Drag-and-drop reordering
❌ Explicit "amount" field with override capability
❌ Advanced template selection interface

---

## 🎨 Enhanced User Experience Vision

### Desired Workflow

**Scenario 1: Load Full Project Template**
1. User clicks "Load Template" button
2. Template selection modal opens showing available templates
3. User selects a template (e.g., "Waterproofing Package A")
4. All sections and items are loaded into builder
5. User can edit quantities, rates, and amounts as needed

**Scenario 2: Build Custom Project from Sections**
1. User clicks "Add Section from Template"
2. Section picker modal opens showing all available sections
3. User selects multiple sections from different templates
4. Selected sections are added to builder
5. User can reorder sections via drag-and-drop
6. User can add manual sections/items alongside template sections

**Scenario 3: Fully Manual Creation**
1. User clicks "Add Section Manually"
2. Empty section is added
3. User fills in section details
4. User adds items one by one
5. All fields (details, unit, quantity, rate, amount) are editable

---

## 🏗️ Technical Architecture Plan

### Phase 1: Database Schema Enhancement

#### 1.1 Add Amount Override Field

**Migration:** `add_amount_override_to_service_template_items_table.php`

```php
Schema::table('service_template_items', function (Blueprint $table) {
    // Amount override - when set, overrides calculated amount
    $table->decimal('amount_override', 10, 2)->nullable()->after('minimum_price');

    // Track if amount was manually edited
    $table->boolean('amount_manually_edited')->default(false)->after('amount_override');
});
```

**Business Logic:**
- `amount` (calculated) = quantity × unit_price
- If `amount_override` is set → use `amount_override`
- If user edits amount → set `amount_override` and `amount_manually_edited = true`

#### 1.2 Model Enhancement: ServiceTemplateItem

```php
// Add to ServiceTemplateItem model

protected $appends = ['calculated_amount', 'final_amount'];

public function getCalculatedAmountAttribute(): float
{
    return $this->default_quantity * $this->default_unit_price;
}

public function getFinalAmountAttribute(): float
{
    // If amount is manually overridden, use override
    if ($this->amount_override !== null) {
        return $this->amount_override;
    }

    // Otherwise use calculated amount
    return $this->calculated_amount;
}

public function setAmountOverride(float $amount): void
{
    $this->amount_override = $amount;
    $this->amount_manually_edited = true;
    $this->save();
}

public function clearAmountOverride(): void
{
    $this->amount_override = null;
    $this->amount_manually_edited = false;
    $this->save();
}
```

---

### Phase 2: Enhanced Template Selection UI

#### 2.1 Template Selection Modal Enhancement

**Location:** `resources/views/quotations/service-builder.blade.php`

**Current Implementation:**
- Basic template card grid
- Load full template only

**Enhanced Features:**
```html
<!-- Template Selection Modal -->
<div x-show="showTemplateModal" class="...">
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-4">
        <nav class="-mb-px flex space-x-8">
            <button @click="templateTab = 'full'"
                    :class="templateTab === 'full' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500'"
                    class="py-4 px-1 border-b-2 font-medium text-sm">
                Full Templates
            </button>
            <button @click="templateTab = 'sections'"
                    :class="templateTab === 'sections' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500'"
                    class="py-4 px-1 border-b-2 font-medium text-sm">
                Individual Sections
            </button>
        </nav>
    </div>

    <!-- Full Template View (existing) -->
    <div x-show="templateTab === 'full'">
        <!-- Current template cards implementation -->
    </div>

    <!-- Section Picker View (NEW) -->
    <div x-show="templateTab === 'sections'">
        <!-- Section picker implementation -->
    </div>
</div>
```

#### 2.2 Section Picker Component (NEW)

```html
<!-- Section Picker Tab Content -->
<div x-show="templateTab === 'sections'" class="space-y-4">
    <!-- Template Filter -->
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Template</label>
        <select x-model="sectionFilterTemplate" class="w-full border-gray-300 rounded-md">
            <option value="">All Templates</option>
            <template x-for="template in serviceTemplates" :key="template.id">
                <option :value="template.id" x-text="template.name"></option>
            </template>
        </select>
    </div>

    <!-- Sections List with Checkboxes -->
    <div class="space-y-3 max-h-96 overflow-y-auto">
        <template x-for="section in filteredSections" :key="section.id">
            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                <label class="flex items-start cursor-pointer">
                    <input type="checkbox"
                           :value="section.id"
                           x-model="selectedSectionIds"
                           class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded">

                    <div class="ml-3 flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-medium text-gray-900" x-text="section.name"></h4>
                            <span class="text-xs text-gray-500" x-text="section.template_name"></span>
                        </div>

                        <p class="mt-1 text-xs text-gray-600" x-text="section.description"></p>

                        <!-- Item Preview -->
                        <div class="mt-2 flex items-center text-xs text-gray-500">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span x-text="section.items_count + ' items'"></span>
                            <span class="mx-2">•</span>
                            <span x-text="'Est. RM ' + section.estimated_total.toFixed(2)"></span>
                        </div>
                    </div>
                </label>
            </div>
        </template>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
        <div class="text-sm text-gray-600">
            <span x-text="selectedSectionIds.length"></span> sections selected
        </div>
        <div class="flex gap-2">
            <button @click="showTemplateModal = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                Cancel
            </button>
            <button @click="loadSelectedSections"
                    :disabled="selectedSectionIds.length === 0"
                    :class="selectedSectionIds.length > 0 ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                    class="px-4 py-2 text-sm font-medium text-white rounded-md">
                Add Selected Sections
            </button>
        </div>
    </div>
</div>
```

---

### Phase 3: Drag-and-Drop Reordering

#### 3.1 Alpine.js Sortable Integration

**Package:** Use Alpine.js Sortable (via CDN or npm)

```html
<!-- In service-builder.blade.php head section -->
<script src="https://cdn.jsdelivr.net/npm/@shopify/draggable@1.0.0-beta.11/lib/sortable.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs-sortable@latest/dist/alpine-sortable.min.js"></script>
```

#### 3.2 Sortable Sections Implementation

```html
<!-- Sections Container with Sortable -->
<div x-sortable="sections"
     @sortable:end="handleSectionReorder($event.detail)"
     class="space-y-4">

    <template x-for="(section, sectionIndex) in sections" :key="section.id">
        <div :data-section-id="section.id"
             class="bg-white border border-gray-200 rounded-lg overflow-hidden">

            <!-- Section Header with Drag Handle -->
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center">
                <!-- Drag Handle -->
                <div class="mr-3 cursor-move handle text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"></path>
                    </svg>
                </div>

                <!-- Section Number Badge -->
                <span class="flex-shrink-0 inline-flex items-center justify-center h-6 w-6 rounded-full bg-blue-100 text-blue-800 text-xs font-bold mr-3"
                      x-text="sectionIndex + 1"></span>

                <!-- Section Name (Editable) -->
                <input type="text"
                       x-model="section.name"
                       placeholder="Section name"
                       class="flex-1 border-0 bg-transparent text-sm font-semibold text-gray-900 placeholder-gray-400 focus:ring-0 p-0">

                <!-- Section Actions -->
                <div class="flex items-center gap-2 ml-4">
                    <button @click="duplicateSection(sectionIndex)"
                            type="button"
                            class="text-gray-400 hover:text-blue-600"
                            title="Duplicate Section">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                    <button @click="removeSection(sectionIndex)"
                            type="button"
                            class="text-gray-400 hover:text-red-600"
                            title="Remove Section">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Items Table (see Phase 4) -->
            <div class="p-4">
                <!-- Items table implementation -->
            </div>
        </div>
    </template>
</div>
```

---

### Phase 4: Enhanced Items Table with Amount Column

#### 4.1 New Table Structure

**Columns:**
1. **#** - Row number (auto)
2. **Details** - Item description (was: description)
3. **Unit** - Unit of measurement (existing)
4. **Quantity** - Quantity (existing)
5. **Rate (RM)** - Unit price (was: unit_price)
6. **Amount (RM)** - Calculated or manual (NEW)
7. **Actions** - Edit/Delete

```html
<!-- Enhanced Items Table -->
<div class="overflow-x-auto">
    <table class="w-full">
        <thead>
            <tr class="bg-gray-50 text-xs font-medium text-gray-700 uppercase tracking-wider">
                <th class="w-12 px-3 py-3 text-center">#</th>
                <th class="px-3 py-3 text-left">Details</th>
                <th class="w-24 px-3 py-3 text-left">Unit</th>
                <th class="w-28 px-3 py-3 text-right">Quantity</th>
                <th class="w-32 px-3 py-3 text-right">Rate (RM)</th>
                <th class="w-32 px-3 py-3 text-right">Amount (RM)</th>
                <th class="w-20 px-3 py-3 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(item, itemIndex) in section.items" :key="item.id">
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <!-- Row Number -->
                    <td class="px-3 py-2 text-center text-sm text-gray-500" x-text="itemIndex + 1"></td>

                    <!-- Details (Description) -->
                    <td class="px-3 py-2">
                        <textarea x-model="item.description"
                                  @input="recalculateItemAmount(sectionIndex, itemIndex)"
                                  placeholder="Item details..."
                                  rows="1"
                                  class="w-full text-sm border-0 bg-transparent focus:ring-0 p-0 resize-none"></textarea>
                    </td>

                    <!-- Unit -->
                    <td class="px-3 py-2">
                        <select x-model="item.unit"
                                class="w-full text-sm border-0 bg-transparent focus:ring-0 p-0">
                            <option value="Nos">Nos</option>
                            <option value="sqm">sqm</option>
                            <option value="sqft">sqft</option>
                            <option value="m">m</option>
                            <option value="ft">ft</option>
                            <option value="kg">kg</option>
                            <option value="ltr">ltr</option>
                            <option value="set">set</option>
                            <option value="unit">unit</option>
                            <option value="pcs">pcs</option>
                            <option value="box">box</option>
                            <option value="roll">roll</option>
                            <option value="hrs">hrs</option>
                            <option value="days">days</option>
                        </select>
                    </td>

                    <!-- Quantity -->
                    <td class="px-3 py-2">
                        <input type="number"
                               x-model.number="item.quantity"
                               @input="recalculateItemAmount(sectionIndex, itemIndex)"
                               step="0.01"
                               min="0"
                               class="w-full text-sm text-right border-0 bg-transparent focus:ring-0 p-0">
                    </td>

                    <!-- Rate (Unit Price) -->
                    <td class="px-3 py-2">
                        <input type="number"
                               x-model.number="item.unit_price"
                               @input="recalculateItemAmount(sectionIndex, itemIndex)"
                               step="0.01"
                               min="0"
                               class="w-full text-sm text-right border-0 bg-transparent focus:ring-0 p-0">
                    </td>

                    <!-- Amount (NEW - Calculated or Manual Override) -->
                    <td class="px-3 py-2 relative">
                        <input type="number"
                               x-model.number="item.amount"
                               @input="handleAmountOverride(sectionIndex, itemIndex, $event.target.value)"
                               @focus="item.amount_focus = true"
                               @blur="item.amount_focus = false"
                               step="0.01"
                               min="0"
                               :class="item.amount_manually_edited ? 'text-amber-700 font-medium' : 'text-gray-900'"
                               class="w-full text-sm text-right border-0 bg-transparent focus:ring-0 p-0">

                        <!-- Manual Override Indicator -->
                        <div x-show="item.amount_manually_edited"
                             class="absolute -right-1 top-1/2 -translate-y-1/2"
                             title="Amount manually adjusted">
                            <svg class="h-3 w-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </td>

                    <!-- Actions -->
                    <td class="px-3 py-2 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <!-- Reset Amount Override -->
                            <button x-show="item.amount_manually_edited"
                                    @click="resetAmountOverride(sectionIndex, itemIndex)"
                                    type="button"
                                    class="text-amber-600 hover:text-amber-800"
                                    title="Reset to calculated amount">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                            </button>

                            <!-- Duplicate Item -->
                            <button @click="duplicateItem(sectionIndex, itemIndex)"
                                    type="button"
                                    class="text-gray-400 hover:text-blue-600"
                                    title="Duplicate item">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>

                            <!-- Delete Item -->
                            <button @click="removeItem(sectionIndex, itemIndex)"
                                    type="button"
                                    class="text-gray-400 hover:text-red-600"
                                    title="Delete item">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            </template>

            <!-- Add Item Button Row -->
            <tr>
                <td colspan="7" class="px-3 py-2">
                    <button @click="addItemToSection(sectionIndex)"
                            type="button"
                            class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-dashed border-blue-300 rounded hover:bg-blue-100">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Item
                    </button>
                </td>
            </tr>

            <!-- Section Subtotal Row -->
            <tr class="bg-gray-50 font-medium">
                <td colspan="5" class="px-3 py-3 text-right text-sm text-gray-700">
                    Section Subtotal:
                </td>
                <td class="px-3 py-3 text-right text-sm text-gray-900" x-text="'RM ' + getSectionSubtotal(section).toFixed(2)"></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>
```

---

### Phase 5: Alpine.js Enhanced Data Model

#### 5.1 Extended Alpine.js Component

```javascript
function quotationBuilder() {
    return {
        // Template Selection State
        showTemplateModal: false,
        templateTab: 'full', // 'full' or 'sections'
        serviceTemplates: [],
        selectedTemplate: null,

        // Section Picker State
        sectionFilterTemplate: '',
        selectedSectionIds: [],
        allSections: [], // Flattened list of all sections from all templates

        // Builder State
        sections: [],
        sectionIdCounter: 1,
        itemIdCounter: 1,
        manualMode: false,

        // ... existing state ...

        init() {
            this.loadServiceTemplates();
            this.loadAllSections();
            // ... existing init code ...
        },

        // ============================================
        // Template & Section Loading
        // ============================================

        async loadServiceTemplates() {
            try {
                const response = await fetch('/api/service-templates');
                this.serviceTemplates = await response.json();
            } catch (error) {
                console.error('Failed to load templates:', error);
            }
        },

        async loadAllSections() {
            try {
                const response = await fetch('/api/service-template-sections');
                const data = await response.json();

                // Flatten sections with template info
                this.allSections = data.map(section => ({
                    ...section,
                    template_name: section.template.name,
                    items_count: section.items.length,
                    estimated_total: section.items.reduce((sum, item) =>
                        sum + (item.default_quantity * item.default_unit_price), 0
                    )
                }));
            } catch (error) {
                console.error('Failed to load sections:', error);
            }
        },

        get filteredSections() {
            if (!this.sectionFilterTemplate) {
                return this.allSections;
            }
            return this.allSections.filter(s =>
                s.service_template_id == this.sectionFilterTemplate
            );
        },

        // ============================================
        // Load Full Template
        // ============================================

        selectServiceTemplate(template) {
            this.selectedTemplate = template;
            this.sections = [];

            // Load all sections from template
            template.sections.forEach(section => {
                this.addSectionFromTemplate(section);
            });

            this.showTemplateModal = false;
            this.manualMode = false;
            this.calculateTotals();
        },

        // ============================================
        // Load Selected Sections
        // ============================================

        async loadSelectedSections() {
            for (const sectionId of this.selectedSectionIds) {
                const section = this.allSections.find(s => s.id === sectionId);
                if (section) {
                    this.addSectionFromTemplate(section);
                }
            }

            this.selectedSectionIds = [];
            this.showTemplateModal = false;
            this.calculateTotals();
        },

        addSectionFromTemplate(templateSection) {
            const newSection = {
                id: this.sectionIdCounter++,
                template_section_id: templateSection.id,
                name: templateSection.name,
                description: templateSection.description || '',
                items: []
            };

            // Add items from template
            templateSection.items.forEach(item => {
                newSection.items.push({
                    id: this.itemIdCounter++,
                    template_item_id: item.id,
                    description: item.description,
                    unit: item.unit,
                    quantity: item.default_quantity || 1,
                    unit_price: item.default_unit_price || 0,
                    amount: null, // Will be calculated
                    amount_manually_edited: false,
                    cost_price: item.cost_price,
                    minimum_price: item.minimum_price
                });
            });

            this.sections.push(newSection);

            // Calculate amounts for all items
            newSection.items.forEach((item, index) => {
                this.recalculateItemAmount(this.sections.length - 1, index);
            });
        },

        // ============================================
        // Manual Section/Item Management
        // ============================================

        addSection() {
            this.sections.push({
                id: this.sectionIdCounter++,
                template_section_id: null, // Manually created
                name: '',
                description: '',
                items: []
            });
        },

        duplicateSection(sectionIndex) {
            const section = this.sections[sectionIndex];
            const duplicate = {
                id: this.sectionIdCounter++,
                template_section_id: section.template_section_id,
                name: section.name + ' (Copy)',
                description: section.description,
                items: section.items.map(item => ({
                    id: this.itemIdCounter++,
                    template_item_id: item.template_item_id,
                    description: item.description,
                    unit: item.unit,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    amount: item.amount,
                    amount_manually_edited: item.amount_manually_edited,
                    cost_price: item.cost_price,
                    minimum_price: item.minimum_price
                }))
            };

            this.sections.splice(sectionIndex + 1, 0, duplicate);
            this.calculateTotals();
        },

        removeSection(index) {
            this.sections.splice(index, 1);
            this.calculateTotals();
        },

        addItemToSection(sectionIndex) {
            const newItem = {
                id: this.itemIdCounter++,
                template_item_id: null, // Manually created
                description: '',
                unit: 'Nos',
                quantity: 1,
                unit_price: 0,
                amount: 0,
                amount_manually_edited: false,
                cost_price: null,
                minimum_price: null
            };

            this.sections[sectionIndex].items.push(newItem);
        },

        duplicateItem(sectionIndex, itemIndex) {
            const item = this.sections[sectionIndex].items[itemIndex];
            const duplicate = {
                id: this.itemIdCounter++,
                template_item_id: item.template_item_id,
                description: item.description,
                unit: item.unit,
                quantity: item.quantity,
                unit_price: item.unit_price,
                amount: item.amount,
                amount_manually_edited: item.amount_manually_edited,
                cost_price: item.cost_price,
                minimum_price: item.minimum_price
            };

            this.sections[sectionIndex].items.splice(itemIndex + 1, 0, duplicate);
            this.calculateTotals();
        },

        removeItem(sectionIndex, itemIndex) {
            this.sections[sectionIndex].items.splice(itemIndex, 1);
            this.calculateTotals();
        },

        // ============================================
        // Amount Calculation & Override
        // ============================================

        recalculateItemAmount(sectionIndex, itemIndex) {
            const item = this.sections[sectionIndex].items[itemIndex];

            // Only recalculate if amount is not manually overridden
            if (!item.amount_manually_edited) {
                item.amount = parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0);
            }

            this.calculateTotals();
        },

        handleAmountOverride(sectionIndex, itemIndex, newAmount) {
            const item = this.sections[sectionIndex].items[itemIndex];
            const calculatedAmount = parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0);
            const overrideAmount = parseFloat(newAmount);

            // Check if amount differs from calculated
            if (Math.abs(overrideAmount - calculatedAmount) > 0.01) {
                item.amount_manually_edited = true;
                item.amount = overrideAmount;
            } else {
                item.amount_manually_edited = false;
                item.amount = calculatedAmount;
            }

            this.calculateTotals();
        },

        resetAmountOverride(sectionIndex, itemIndex) {
            const item = this.sections[sectionIndex].items[itemIndex];
            item.amount_manually_edited = false;
            item.amount = parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0);
            this.calculateTotals();
        },

        // ============================================
        // Drag-and-Drop Reordering
        // ============================================

        handleSectionReorder(event) {
            // event.detail contains the new order
            const { oldIndex, newIndex } = event.detail;

            // Move section to new position
            const movedSection = this.sections.splice(oldIndex, 1)[0];
            this.sections.splice(newIndex, 0, movedSection);

            // Recalculate totals after reordering
            this.calculateTotals();
        },

        // ============================================
        // Financial Calculations
        // ============================================

        getSectionSubtotal(section) {
            return section.items.reduce((total, item) => {
                return total + parseFloat(item.amount || 0);
            }, 0);
        },

        calculateTotals() {
            // Calculate subtotal from all sections
            this.subtotal = this.sections.reduce((total, section) => {
                return total + this.getSectionSubtotal(section);
            }, 0);

            // Calculate discount
            if (this.discountPercentage > 0) {
                this.discountAmount = (this.subtotal * parseFloat(this.discountPercentage)) / 100;
            }

            const afterDiscount = this.subtotal - this.discountAmount;
            this.taxAmount = (afterDiscount * parseFloat(this.taxPercentage || 0)) / 100;
            this.total = afterDiscount + this.taxAmount;
        },

        // ... rest of existing methods ...
    }
}
```

---

### Phase 6: Backend API Endpoints

#### 6.1 ServiceTemplateController API Methods

```php
// Add to ServiceTemplateController

/**
 * Get all sections from all templates (for section picker)
 *
 * @return \Illuminate\Http\JsonResponse
 */
public function getAllSections()
{
    $sections = ServiceTemplateSection::with(['template:id,name', 'items'])
        ->whereHas('template', function ($query) {
            $query->where('company_id', auth()->user()->company_id)
                  ->where('is_active', true);
        })
        ->where('is_active', true)
        ->orderBy('service_template_id')
        ->orderBy('sort_order')
        ->get();

    return response()->json($sections);
}

/**
 * Get templates for dropdown/selection
 *
 * @return \Illuminate\Http\JsonResponse
 */
public function getTemplatesForSelection()
{
    $templates = ServiceTemplate::with(['sections.items'])
        ->where('company_id', auth()->user()->company_id)
        ->where('is_active', true)
        ->select('id', 'name', 'description', 'category', 'estimated_hours', 'base_price')
        ->orderBy('name')
        ->get();

    return response()->json($templates);
}
```

#### 6.2 Routes Addition

```php
// Add to routes/web.php or routes/api.php

Route::middleware(['auth'])->group(function () {
    // Service Template API endpoints
    Route::get('/api/service-templates', [ServiceTemplateController::class, 'getTemplatesForSelection']);
    Route::get('/api/service-template-sections', [ServiceTemplateController::class, 'getAllSections']);
});
```

---

### Phase 7: Service Template Creation UI Update

#### 7.1 Update Create Template Form

**File:** `resources/views/service-templates/create.blade.php`

**Changes:**
- Update field labels: "Item Name" → "Details"
- Update field labels: "Unit Price (RM)" → "Rate (RM)"
- Add "Amount Override" field (optional)
- Add "Amount Manually Edited" checkbox

```html
<!-- Enhanced Item Row in Template Creation -->
<div class="grid grid-cols-12 gap-3">
    <!-- Details (was: Item Name) -->
    <div class="col-span-4">
        <label class="block text-xs font-medium text-gray-700 mb-1">Details</label>
        <textarea :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][description]'"
                  x-model="item.description"
                  rows="2"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                  placeholder="Item details..."></textarea>
    </div>

    <!-- Unit -->
    <div class="col-span-2">
        <label class="block text-xs font-medium text-gray-700 mb-1">Unit</label>
        <select :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][unit]'"
                x-model="item.unit"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            <option value="Nos">Nos</option>
            <option value="sqm">sqm</option>
            <option value="sqft">sqft</option>
            <!-- ... other units ... -->
        </select>
    </div>

    <!-- Quantity -->
    <div class="col-span-2">
        <label class="block text-xs font-medium text-gray-700 mb-1">Quantity</label>
        <input type="number"
               :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][default_quantity]'"
               x-model="item.quantity"
               @input="calculateItemAmount(sectionIndex, itemIndex)"
               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
               min="0" step="0.01" value="1">
    </div>

    <!-- Rate (was: Unit Price) -->
    <div class="col-span-2">
        <label class="block text-xs font-medium text-gray-700 mb-1">Rate (RM)</label>
        <input type="number"
               :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][default_unit_price]'"
               x-model="item.unit_price"
               @input="calculateItemAmount(sectionIndex, itemIndex)"
               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
               step="0.01" min="0">
    </div>

    <!-- Amount (Calculated) - Display Only -->
    <div class="col-span-2">
        <label class="block text-xs font-medium text-gray-700 mb-1">Amount (RM)</label>
        <input type="number"
               x-model="item.calculated_amount"
               class="w-full rounded-md border-gray-300 bg-gray-50 shadow-sm text-sm text-gray-600"
               readonly>
    </div>
</div>

<!-- Advanced Options (Collapsible) -->
<div x-data="{ showAdvanced: false }" class="mt-2">
    <button @click="showAdvanced = !showAdvanced" type="button" class="text-xs text-blue-600 hover:text-blue-800">
        <span x-show="!showAdvanced">+ Show advanced options</span>
        <span x-show="showAdvanced">- Hide advanced options</span>
    </button>

    <div x-show="showAdvanced" class="mt-3 grid grid-cols-3 gap-3 p-3 bg-gray-50 rounded">
        <!-- Cost Price -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Cost Price (RM)</label>
            <input type="number"
                   :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][cost_price]'"
                   x-model="item.cost_price"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                   step="0.01" min="0">
        </div>

        <!-- Minimum Price -->
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Min Price (RM)</label>
            <input type="number"
                   :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][minimum_price]'"
                   x-model="item.minimum_price"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                   step="0.01" min="0">
        </div>

        <!-- Allow Amount Override -->
        <div class="flex items-center pt-6">
            <input type="hidden" :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][allow_amount_override]'" value="0">
            <input type="checkbox"
                   :name="'sections[' + sectionIndex + '][items][' + itemIndex + '][allow_amount_override]'"
                   x-model="item.allow_amount_override"
                   class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                   value="1">
            <label class="ml-2 text-xs text-gray-700">Allow manual amount adjustment</label>
        </div>
    </div>
</div>
```

---

## 📊 Implementation Phases Summary

### Phase 1: Database & Models (2 hours)
- [ ] Create migration for `amount_override` and `amount_manually_edited`
- [ ] Update `ServiceTemplateItem` model with computed properties
- [ ] Add business logic methods for amount handling
- [ ] Run migration and test model changes

### Phase 2: Template Selection UI (3 hours)
- [ ] Add tab navigation to template modal
- [ ] Create section picker component
- [ ] Implement section filtering
- [ ] Add multi-select checkbox functionality
- [ ] Test full template vs. section loading

### Phase 3: Drag-and-Drop (2 hours)
- [ ] Integrate Alpine.js Sortable library
- [ ] Add drag handles to sections
- [ ] Implement reorder event handlers
- [ ] Test section reordering
- [ ] Add visual feedback during drag

### Phase 4: Enhanced Items Table (3 hours)
- [ ] Update table structure with new columns
- [ ] Implement amount calculation logic
- [ ] Add amount override functionality
- [ ] Add visual indicators for manual amounts
- [ ] Add reset amount override button
- [ ] Implement item duplication
- [ ] Test all item operations

### Phase 5: Alpine.js Enhancement (2 hours)
- [ ] Update Alpine.js data model
- [ ] Implement template/section loading methods
- [ ] Add amount calculation methods
- [ ] Add section/item management methods
- [ ] Test all interactive features

### Phase 6: Backend API (1 hour)
- [ ] Add API endpoints to ServiceTemplateController
- [ ] Create routes for API endpoints
- [ ] Test API responses
- [ ] Verify multi-tenant security

### Phase 7: Template Creation UI (1 hour)
- [ ] Update field labels in create form
- [ ] Add advanced options section
- [ ] Update JavaScript for amount calculation
- [ ] Test template creation with new fields

### Phase 8: Invoice Builder Integration (1 hour)
- [ ] Apply all changes to invoice service-builder
- [ ] Ensure consistency between quotation and invoice
- [ ] Test invoice builder functionality

---

## ✅ Testing Checklist

### Functional Testing
- [ ] Load full template - all sections and items appear
- [ ] Load individual sections - only selected sections appear
- [ ] Mix template and manual sections
- [ ] Drag-and-drop reorder sections
- [ ] Amount auto-calculates when quantity/rate changes
- [ ] Manual amount override works correctly
- [ ] Reset amount override returns to calculated value
- [ ] Visual indicator shows when amount is manually adjusted
- [ ] Section subtotals calculate correctly
- [ ] Overall totals calculate correctly
- [ ] Item duplication works
- [ ] Section duplication works
- [ ] Delete items and sections
- [ ] Save quotation with mixed sections
- [ ] Load saved quotation preserves all data

### UI/UX Testing
- [ ] Template modal tabs switch smoothly
- [ ] Section picker filters work
- [ ] Drag handles are visible and intuitive
- [ ] Amount field color changes when overridden
- [ ] Mobile responsiveness maintained
- [ ] Loading states show appropriately
- [ ] Error messages are clear
- [ ] Tooltips provide helpful context

### Data Integrity Testing
- [ ] Amount overrides save to database
- [ ] Template section IDs are preserved
- [ ] Multi-tenant isolation maintained
- [ ] Validation prevents negative amounts
- [ ] Minimum price validation works
- [ ] Subtotals match individual amounts
- [ ] PDF generation reflects all changes

---

## 🎯 Success Criteria

1. **User can load entire project templates** with one click
2. **User can pick individual sections** from different templates
3. **User can reorder sections** via drag-and-drop
4. **User can manually create** sections and items
5. **Amount column** displays calculated or manual values correctly
6. **Visual indicators** clearly show when amounts are overridden
7. **All calculations** (section subtotals, totals) are accurate
8. **Data persists** correctly when saving/loading quotations
9. **Invoice builder** has identical functionality
10. **No regression** in existing features

---

## 🚀 Deployment Checklist

- [ ] Database migration completed successfully
- [ ] All model changes tested
- [ ] Frontend changes deployed
- [ ] JavaScript bundled and minified
- [ ] API endpoints functional
- [ ] Multi-tenant security verified
- [ ] User documentation updated
- [ ] Training materials prepared
- [ ] Rollback plan ready
- [ ] Production testing completed

---

## 📝 Notes & Considerations

### Architecture Decisions

**Amount Field Strategy:**
We're implementing **Option C: Amount Override** approach:
- Default: Calculate amount as quantity × rate
- Allow manual override when needed
- Store override in `amount_override` field
- Track override status in `amount_manually_edited` boolean
- Visual indicator (amber color) when manually edited
- One-click reset to calculated value

**Rationale:**
- Provides maximum flexibility for users
- Maintains data integrity with clear override tracking
- Allows for special pricing scenarios (bulk discounts, special deals)
- Doesn't complicate the common case (auto-calculation)

### Performance Considerations

- **Template Loading**: Use eager loading for sections and items to avoid N+1 queries
- **Alpine.js Reactivity**: Debounce amount calculations to prevent excessive re-renders
- **Drag-and-Drop**: Use CSS transforms for smooth animations
- **Large Templates**: Implement virtual scrolling if templates have 100+ items

### Future Enhancements

1. **Template Favorites**: Allow users to mark frequently used sections
2. **Section Templates**: Save custom section configurations
3. **Item Library**: Global item library for quick addition
4. **Copy from Previous Quote**: Load sections from existing quotations
5. **Smart Suggestions**: AI-powered section recommendations based on customer
6. **Version Control**: Track template changes over time
7. **Approval Workflow**: Require approval for manual amount overrides above threshold
8. **Margin Warnings**: Alert when manual amounts drop below cost price

---

## 📚 Related Documentation

- **Service Template System**: See Session 5 notes in CLAUDE.md
- **Alpine.js Documentation**: https://alpinejs.dev
- **Tailwind CSS**: https://tailwindcss.com
- **Laravel Validation**: https://laravel.com/docs/validation
- **Multi-tenant Architecture**: See authentication notes in CLAUDE.md

---

**End of Service Template Enhancement Plan**

This plan provides a comprehensive, production-ready approach to enhancing the service template system with the exact user experience you requested. The implementation is broken down into manageable phases with clear success criteria and testing requirements.

Would you like me to proceed with implementation, or would you like to review and adjust any aspects of this plan first?
