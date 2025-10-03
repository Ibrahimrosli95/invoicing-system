# Next Steps: Module Builders Implementation Plan

## Current State Analysis

### ✅ What Exists

Based on the codebase analysis, here's what's currently implemented:

#### Invoices
1. **✅ Product Invoice Builder** (`invoices/builder.blade.php`) - ~2,500 lines
   - Document-style WYSIWYG interface
   - Complete PDF generation and preview
   - Logo Bank integration
   - Template management (Notes, Terms, Payment Instructions)
   - Signature system (User, Company, Customer)
   - Real-time calculations
   - **FULLY DOCUMENTED** ✅

2. **✅ Service Invoice Creator** (`invoices/create-service.blade.php`)
   - Section-based structure
   - Manual section and item creation
   - **Uses old "create" approach, NOT the new "builder" approach**
   - Does NOT use Service Template selection

3. **✅ Old Product Invoice Creator** (`invoices/create-product.blade.php`)
   - Traditional form-based approach
   - Being replaced by the new builder

#### Quotations
1. **✅ Product Quotation Creator** (`quotations/create-product.blade.php`)
   - Similar to old invoice creator
   - Traditional form-based approach

2. **✅ Service Quotation Creator** (`quotations/create-service.blade.php`)
   - Section-based structure similar to service invoice
   - Manual section creation

### 🎯 What Needs to Be Built

According to CLAUDE.md, the **Service Template Manager** system is already completed with:
- ServiceTemplate model
- ServiceTemplateSection model
- ServiceTemplateItem model
- Template-to-quotation conversion workflow

**However**, none of the current builders actually USE the Service Template selection system. They all use manual section/item creation.

---

## 🚀 Recommended Implementation Roadmap

### Phase 1: Service Invoice Builder (Priority 1)
**Goal**: Create a new `invoices/service-builder.blade.php` matching the product builder quality

#### Why This First?
1. We already have the product invoice builder as a perfect template
2. Service Template system already exists in the backend
3. Completing invoice module first makes business sense (revenue documents)

#### What to Build:
```
invoices/service-builder.blade.php (~2,500 lines)
```

**Key Differences from Product Builder:**
- Replace line items section with **Service Template Selection**
- Add section-based display (sections → items within sections)
- Section subtotals + grand total
- Service template browser modal
- Auto-populate sections/items from selected template

**Reuse from Product Builder (90% similarity):**
- ✅ PDF generation and preview system
- ✅ Logo Bank integration
- ✅ Template management (Notes, Terms, Payment)
- ✅ Signature system
- ✅ Customer search and selection
- ✅ Financial calculations (discount, tax, round-off)
- ✅ Mobile-responsive design
- ✅ Real-time preview

**New Components to Build:**
1. **Service Template Selection Modal** (~200 lines)
   - Browse available service templates
   - Template preview with sections/items
   - One-click template selection
   - Auto-populate invoice from template

2. **Section-Based Line Items Display** (~300 lines)
   - Section headers with descriptions
   - Items within each section
   - Section subtotals
   - Collapsible sections for long invoices

3. **Service Template Browser** (~150 lines)
   - Grid/list view of templates
   - Template categories
   - Search and filter
   - Template statistics

**API Endpoints Needed:**
```php
// Already exists from ServiceTemplate system
GET  /api/service-templates           // List templates
GET  /api/service-templates/{id}      // Get template with sections/items
POST /api/invoices/from-template      // Create invoice from template
```

**Estimated Effort**: 2-3 days
**Complexity**: Medium (template selection logic + section display)

---

### Phase 2: Product Quotation Builder (Priority 2)
**Goal**: Create `quotations/product-builder.blade.php`

#### Why This Second?
1. Can reuse 95% of product invoice builder code
2. Quotations feed into invoices (logical workflow)
3. Product quotations are simpler than service quotations

#### What to Build:
```
quotations/product-builder.blade.php (~2,400 lines)
```

**Differences from Product Invoice Builder:**
- Document type: Quotation vs Invoice
- Numbering: QTN-YYYY-NNN vs INV-YYYY-NNN
- Status workflow: DRAFT → SENT → VIEWED → ACCEPTED/REJECTED vs DRAFT → SENT → PAID
- Additional fields:
  - Validity period (e.g., "Valid for 30 days")
  - Acceptance buttons for customer
- No payment-related fields (due date, payment terms)

**Reuse from Product Invoice Builder (95%):**
- ✅ All PDF, Logo, Template, Signature systems
- ✅ Customer search
- ✅ Line items management
- ✅ Financial calculations
- ✅ Pricing Book integration

**New Components:**
1. **Quotation-Specific Fields** (~50 lines)
   - Validity period dropdown
   - Quote expiry date
   - Quote status indicators

2. **Quotation Actions** (~100 lines)
   - Convert to Invoice button
   - Accept/Reject workflow (if customer portal)
   - Clone quotation

**API Endpoints:**
```php
POST /api/quotations              // Create quotation (similar to invoice)
POST /api/quotations/{id}/convert // Convert to invoice
```

**Estimated Effort**: 1-2 days
**Complexity**: Low (mostly copy-paste with field changes)

---

### Phase 3: Service Quotation Builder (Priority 3)
**Goal**: Create `quotations/service-builder.blade.php`

#### Why This Third?
1. Combines learnings from Service Invoice + Product Quotation
2. Most complex module (service templates + quotation workflow)

#### What to Build:
```
quotations/service-builder.blade.php (~2,600 lines)
```

**Combines:**
- Service Template Selection (from Service Invoice Builder)
- Quotation-specific fields (from Product Quotation Builder)
- Section-based structure

**Reuse:**
- Service template selection modal (from Service Invoice Builder)
- Section display logic (from Service Invoice Builder)
- Quotation workflow (from Product Quotation Builder)

**New Components:**
- Convert service quotation → service invoice workflow

**Estimated Effort**: 2 days
**Complexity**: Medium (combines two previous builders)

---

### Phase 4: Backend Integration & Routes (Priority 4)
**Goal**: Ensure all builders have proper backend support

#### InvoiceController Updates:
```php
// Add new method
public function serviceBuilder(Request $request): View
{
    $this->authorize('create', Invoice::class);

    // Load service templates instead of default templates
    $serviceTemplates = ServiceTemplate::forCompany()
        ->active()
        ->orderBy('name')
        ->get();

    $defaultTemplates = [
        'notes' => InvoiceNoteTemplate::getDefaultForType('notes'),
        'terms' => InvoiceNoteTemplate::getDefaultForType('terms'),
        'payment_instructions' => InvoiceNoteTemplate::getDefaultForType('payment_instructions'),
    ];

    return view('invoices.service-builder', compact('serviceTemplates', 'defaultTemplates'));
}

// Add API endpoint for template-based invoice creation
public function storeFromTemplate(Request $request): JsonResponse
{
    $this->authorize('create', Invoice::class);

    $validated = $request->validate([
        'service_template_id' => 'required|exists:service_templates,id',
        'customer_name' => 'required|string|max:100',
        // ... other fields
    ]);

    $template = ServiceTemplate::with(['sections.items'])->findOrFail($validated['service_template_id']);

    // Create invoice from template
    $invoice = Invoice::create([
        'type' => Invoice::TYPE_SERVICE,
        'service_template_id' => $template->id,
        // ... populate from template
    ]);

    // Create sections and items from template
    foreach ($template->sections as $section) {
        $invoiceSection = $invoice->sections()->create([
            'name' => $section->name,
            'description' => $section->description,
            'sort_order' => $section->sort_order,
        ]);

        foreach ($section->items as $item) {
            $invoiceSection->items()->create([
                'description' => $item->description,
                'quantity' => $item->default_quantity,
                'unit_price' => $item->unit_price,
                // ... other fields
            ]);
        }
    }

    return response()->json(['success' => true, 'invoice' => $invoice]);
}
```

#### QuotationController Updates:
Similar structure to InvoiceController with quotation-specific logic.

#### Routes to Add:
```php
// Service Invoice Builder
Route::get('/invoices/service-builder', [InvoiceController::class, 'serviceBuilder'])
    ->name('invoices.service-builder');
Route::post('/api/invoices/from-template', [InvoiceController::class, 'storeFromTemplate'])
    ->name('invoices.from-template');

// Product Quotation Builder
Route::get('/quotations/product-builder', [QuotationController::class, 'productBuilder'])
    ->name('quotations.product-builder');

// Service Quotation Builder
Route::get('/quotations/service-builder', [QuotationController::class, 'serviceBuilder'])
    ->name('quotations.service-builder');
Route::post('/api/quotations/from-template', [QuotationController::class, 'storeFromTemplate'])
    ->name('quotations.from-template');
```

**Estimated Effort**: 1 day
**Complexity**: Low (standard Laravel controller/route work)

---

### Phase 5: Documentation (Priority 5)
**Goal**: Create comprehensive documentation for all builders

#### Documents to Create:
1. **service_invoice_builder.md** (~40,000 characters)
   - Similar structure to product_invoice_builder_COMPLETE.md
   - Focus on service template selection
   - Section-based architecture

2. **product_quotation_builder.md** (~35,000 characters)
   - Quotation-specific workflows
   - Differences from invoice builder

3. **service_quotation_builder.md** (~42,000 characters)
   - Combines service and quotation concepts
   - Complete reference

4. **BUILDER_SYSTEM_OVERVIEW.md** (~10,000 characters)
   - High-level architecture
   - Shared components
   - When to use which builder

**Estimated Effort**: 2 days
**Complexity**: Low (documentation after implementation)

---

## 📊 Total Project Timeline

### Summary of Phases:
| Phase | Module | Effort | Complexity |
|-------|--------|--------|------------|
| 1 | Service Invoice Builder | 2-3 days | Medium |
| 2 | Product Quotation Builder | 1-2 days | Low |
| 3 | Service Quotation Builder | 2 days | Medium |
| 4 | Backend Integration | 1 day | Low |
| 5 | Documentation | 2 days | Low |

**Total Estimated Effort**: 8-10 days

---

## 🎯 Recommended Next Action

### Start with Phase 1: Service Invoice Builder

**Step-by-step approach:**

1. **Copy Product Invoice Builder** as starting point
   ```bash
   cp resources/views/invoices/builder.blade.php resources/views/invoices/service-builder.blade.php
   ```

2. **Replace Line Items Section** with Service Template Selection
   - Remove pricing book integration
   - Add service template browser modal
   - Add section-based display

3. **Update Alpine.js Component**
   - Replace `lineItems` array with `sections` array
   - Each section has `items` array
   - Update `calculateTotals()` to sum sections

4. **Add Service Template Selection Logic**
   ```javascript
   selectServiceTemplate(template) {
       // Clear existing sections
       this.sections = [];

       // Populate from template
       template.sections.forEach(section => {
           this.sections.push({
               id: section.id,
               name: section.name,
               description: section.description,
               items: section.items.map(item => ({
                   id: item.id,
                   description: item.description,
                   quantity: item.default_quantity,
                   unit_price: item.unit_price,
               }))
           });
       });

       this.calculateTotals();
   }
   ```

5. **Test & Iterate**
   - Test template selection
   - Test PDF generation
   - Test all existing features (logo, templates, signatures)

6. **Document** as you build (create service_invoice_builder.md)

---

## 🔄 Migration Strategy

### For Existing Users:

**Option 1: Gradual Migration**
- Keep old `create-service.blade.php` and `create-product.blade.php`
- Add new builders alongside
- Feature flag to enable new builders
- Migrate users over time

**Option 2: Hard Cutover**
- Replace old forms with new builders
- Redirect old routes to new routes
- One-time migration

**Recommended**: Option 1 (gradual migration with feature flag)

```php
// In routes/web.php
if (config('features.invoice_builder_v2', false)) {
    Route::get('/invoices/create', [InvoiceController::class, 'builder'])
        ->name('invoices.create');
} else {
    Route::get('/invoices/create', [InvoiceController::class, 'create'])
        ->name('invoices.create');
}
```

---

## 💡 Key Architectural Decisions

### 1. Service Template Selection vs Manual Entry

**Decision**: Support BOTH modes
- Primary: Service Template selection (recommended workflow)
- Fallback: Manual section/item creation (for custom work)

**Implementation**:
```javascript
// In service-builder.blade.php
optionalModes: {
    use_template: true,  // Default to template mode
    allow_manual: true,  // Allow switching to manual
}

toggleManualMode() {
    this.optionalModes.use_template = false;
    // Show manual section creation UI
}
```

### 2. Shared Components Architecture

**Extract shared components** to avoid duplication:

```
resources/views/components/builders/
├── customer-search.blade.php      (reusable across all builders)
├── financial-summary.blade.php    (reusable)
├── logo-selector.blade.php        (reusable)
├── template-selector.blade.php    (reusable)
├── signature-section.blade.php    (reusable)
├── pdf-preview.blade.php          (reusable)
└── sticky-header.blade.php        (reusable)
```

### 3. API Consistency

**Maintain consistent API patterns:**
```
POST /api/invoices              (product invoice)
POST /api/invoices/from-template (service invoice)
POST /api/quotations            (product quotation)
POST /api/quotations/from-template (service quotation)
```

---

## ✅ Success Criteria

### Phase 1 Complete When:
- [ ] Service invoice builder renders correctly
- [ ] Service template selection works
- [ ] Sections and items display properly
- [ ] PDF generation includes all sections
- [ ] All shared features work (logo, templates, signatures)
- [ ] Mobile responsive
- [ ] Documentation created

### All Phases Complete When:
- [ ] All 4 builders functional (product/service × invoice/quotation)
- [ ] All backend APIs implemented
- [ ] All routes configured
- [ ] Complete documentation for all builders
- [ ] Feature flag system in place
- [ ] User migration plan documented

---

## 📝 Next Immediate Steps

1. **Review this plan** with stakeholders
2. **Get approval** for Phase 1 (Service Invoice Builder)
3. **Create feature branch**: `feature/service-invoice-builder`
4. **Start implementation** following Phase 1 steps above
5. **Daily progress updates** in development session log

**Estimated Start Date**: Today
**Estimated Completion**: 8-10 business days from start

---

## 🎨 Design Consistency

All new builders MUST match the quality and design of the product invoice builder:
- ✅ Document-style WYSIWYG interface
- ✅ Real-time preview
- ✅ Professional PDF generation
- ✅ Mobile-first responsive design
- ✅ Clean, minimalist UI
- ✅ Comprehensive error handling
- ✅ Performance optimized (debouncing, caching)

---

## Questions to Answer Before Starting

1. **Service Template Selection**: Should it be REQUIRED or OPTIONAL?
   - **Recommendation**: Optional with manual fallback

2. **Template Modification**: Can users modify template sections/items after selection?
   - **Recommendation**: Yes, full editing capability

3. **Template Versioning**: What happens when a template is updated?
   - **Recommendation**: Existing invoices use snapshot, new ones use latest

4. **Migration Timeline**: When to deprecate old forms?
   - **Recommendation**: 3 months after new builders stable

5. **Feature Flag**: Enable for all users or gradual rollout?
   - **Recommendation**: Gradual rollout by company

---

This document provides a complete roadmap for implementing all builder modules. The next step is to begin Phase 1: Service Invoice Builder.
