# Company Brands System - Implementation Plan

## 📋 Overview

### Business Problem
Different services require different company letterheads with:
- Different company names (trading names)
- Different addresses and contact details
- Different logos
- Different branding elements

### Solution
Implement a **Company Brands/Divisions** system that allows a single company to operate under multiple brand identities, each with its own letterhead configuration.

### Benefits
- ✅ Professional multi-brand document generation
- ✅ Flexible brand assignment per document (not locked to service type)
- ✅ Scalable for future business expansion
- ✅ Unified reporting across all brands
- ✅ Proper legal entity representation

---

## 🗄️ Database Schema Design

### 1. New Table: `company_brands`

```sql
CREATE TABLE company_brands (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,

    -- Brand Identity
    name VARCHAR(255) NOT NULL,                    -- Trading name: "Bina Waterproofing Services"
    legal_name VARCHAR(255) NULL,                  -- Legal entity name if different
    registration_number VARCHAR(100) NULL,         -- SSM/Business registration
    logo_path VARCHAR(255) NULL,                   -- Path to brand logo

    -- Contact Details
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    website VARCHAR(255) NULL,

    -- Bank Details (optional - can inherit from company or have own)
    bank_name VARCHAR(255) NULL,
    bank_account_name VARCHAR(255) NULL,
    bank_account_number VARCHAR(100) NULL,

    -- Settings
    is_default BOOLEAN DEFAULT FALSE,              -- Default brand for new documents
    is_active BOOLEAN DEFAULT TRUE,                -- Active/inactive toggle
    tagline TEXT NULL,                             -- Brand tagline/slogan
    color_primary VARCHAR(7) NULL,                 -- Primary brand color (hex)
    color_secondary VARCHAR(7) NULL,               -- Secondary brand color (hex)

    -- Metadata
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    -- Foreign Keys
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,

    -- Indexes
    INDEX idx_company_brands_company_active (company_id, is_active),
    INDEX idx_company_brands_default (is_default),
    INDEX idx_company_brands_name (name)
);
```

### 2. Update Existing Tables

**Add to `quotations` table:**
```sql
ALTER TABLE quotations
ADD COLUMN company_brand_id BIGINT UNSIGNED NULL AFTER company_id,
ADD FOREIGN KEY (company_brand_id) REFERENCES company_brands(id) ON DELETE SET NULL,
ADD INDEX idx_quotations_brand (company_brand_id);
```

**Add to `invoices` table:**
```sql
ALTER TABLE invoices
ADD COLUMN company_brand_id BIGINT UNSIGNED NULL AFTER company_id,
ADD FOREIGN KEY (company_brand_id) REFERENCES company_brands(id) ON DELETE SET NULL,
ADD INDEX idx_invoices_brand (company_brand_id);
```

---

## 🏗️ Model Architecture

### 1. CompanyBrand Model

**File:** `app/Models/CompanyBrand.php`

**Key Features:**
- Multi-tenant scoping (belongs to company)
- Active/inactive status management
- Default brand logic
- Logo file management
- Bank details management

**Relationships:**
```php
- belongsTo(Company::class)
- hasMany(Quotation::class)
- hasMany(Invoice::class)
```

**Scopes:**
```php
- scopeActive($query)           // Only active brands
- scopeForCompany($query, $id)  // Company-specific brands
- scopeDefault($query)           // Get default brand
```

**Methods:**
```php
- setAsDefault()                // Make this brand the default
- getLogoUrl()                  // Get full logo URL
- hasOwnBankDetails()           // Check if has custom bank details
- getBankDetails()              // Get brand or company bank details
```

### 2. Update Existing Models

**Quotation Model:**
```php
// Add relationship
public function companyBrand()
{
    return $this->belongsTo(CompanyBrand::class);
}

// Helper method
public function getLetterhead()
{
    return $this->companyBrand ?? $this->company;
}

public function getLetterheadLogo()
{
    if ($this->companyBrand && $this->companyBrand->logo_path) {
        return $this->companyBrand->getLogoUrl();
    }
    return $this->company->getLogoUrl();
}
```

**Invoice Model:**
```php
// Same as Quotation
public function companyBrand()
{
    return $this->belongsTo(CompanyBrand::class);
}

public function getLetterhead()
{
    return $this->companyBrand ?? $this->company;
}
```

**Company Model:**
```php
// Add relationship
public function brands()
{
    return $this->hasMany(CompanyBrand::class);
}

public function defaultBrand()
{
    return $this->hasOne(CompanyBrand::class)->where('is_default', true);
}

public function activeBrands()
{
    return $this->hasMany(CompanyBrand::class)->where('is_active', true);
}
```

---

## 🎮 Controller Logic

### 1. CompanyBrandController

**File:** `app/Http/Controllers/CompanyBrandController.php`

**Actions:**
```php
- index()           // List all brands for company
- create()          // Show create form
- store()           // Save new brand (with logo upload)
- show($id)         // View brand details
- edit($id)         // Show edit form
- update($id)       // Update brand (with logo upload)
- destroy($id)      // Delete brand (prevent if used in documents)
- setDefault($id)   // Set as default brand
- toggleStatus($id) // Activate/deactivate brand
```

**Key Logic:**
```php
// In store/update - Logo Upload
if ($request->hasFile('logo')) {
    $path = $request->file('logo')->store('brands/logos', 'public');
    $brand->logo_path = $path;
}

// In setDefault - Ensure only one default
CompanyBrand::where('company_id', $companyId)
    ->where('id', '!=', $brandId)
    ->update(['is_default' => false]);

$brand->update(['is_default' => true]);

// In destroy - Check usage
if ($brand->quotations()->exists() || $brand->invoices()->exists()) {
    return back()->withErrors([
        'brand' => 'Cannot delete brand that is used in quotations or invoices.'
    ]);
}
```

### 2. Update QuotationController

**Add to create() method:**
```php
$companyBrands = CompanyBrand::forCompany(auth()->user()->company_id)
    ->active()
    ->orderBy('is_default', 'desc')
    ->orderBy('name')
    ->get();

return view('quotations.create', compact('companyBrands', ...));
```

**Add to store() method:**
```php
$quotation->company_brand_id = $request->input('company_brand_id');
```

### 3. Update InvoiceController

Same as QuotationController - add brand selection to forms.

---

## 🎨 UI/UX Design

### 1. Brand Management Interface

**Location:** Settings → Company Brands

**Brand List View (index.blade.php):**
```
┌─────────────────────────────────────────────────┐
│ Company Brands                    [+ New Brand] │
├─────────────────────────────────────────────────┤
│ ┌─────────────────────────────────────────────┐ │
│ │ 🏢 Bina Group (Main)          [Default] ✓   │ │
│ │    123 Main St, KL                          │ │
│ │    [Edit] [Toggle]                          │ │
│ └─────────────────────────────────────────────┘ │
│                                                 │
│ ┌─────────────────────────────────────────────┐ │
│ │ 💧 Bina Waterproofing Services             │ │
│ │    456 Industrial Rd, Selangor             │ │
│ │    [Edit] [Set Default] [Toggle] [Delete]  │ │
│ └─────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘
```

**Brand Form (create/edit.blade.php):**
```
Brand Information
├── Trading Name *
├── Legal Name
├── Registration Number
├── Logo Upload (recommended: 300x100px)
│
Contact Details
├── Address *
├── City *
├── State *
├── Postal Code *
├── Phone *
├── Email *
├── Website
│
Bank Details (Optional)
├── Bank Name
├── Account Name
├── Account Number
│
Settings
├── Is Default Brand
├── Brand Tagline
├── Primary Color
└── Secondary Color
```

### 2. Document Forms Enhancement

**Quotation/Invoice Create/Edit Forms:**

Add brand selector after company info:
```blade
<div class="mb-6">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Company Letterhead
    </label>
    <select name="company_brand_id" class="w-full border-gray-300 rounded-md">
        <option value="">Default ({{ auth()->user()->company->name }})</option>
        @foreach($companyBrands as $brand)
            <option value="{{ $brand->id }}"
                {{ old('company_brand_id', $quotation->company_brand_id) == $brand->id ? 'selected' : '' }}>
                {{ $brand->name }}
                @if($brand->is_default) (Default) @endif
            </option>
        @endforeach
    </select>
    <p class="mt-1 text-sm text-gray-500">
        Select which company brand letterhead to use for this document
    </p>
</div>
```

### 3. Document Display

**Show Views:**
Display which brand is used:
```blade
@if($quotation->companyBrand)
    <div class="text-sm text-gray-600">
        Letterhead: <span class="font-medium">{{ $quotation->companyBrand->name }}</span>
    </div>
@endif
```

---

## 📄 PDF Integration

### Update PDFService

**Current Logic:**
```php
// OLD
$company = $quotation->company;
```

**New Logic:**
```php
// NEW - Use brand if assigned, otherwise company
$letterhead = $quotation->companyBrand ?? $quotation->company;

$data = [
    'quotation' => $quotation,
    'company' => $letterhead,  // This can be Brand or Company
    'logo' => $letterhead->logo_path
        ? Storage::url($letterhead->logo_path)
        : ($quotation->company->logo_path ? Storage::url($quotation->company->logo_path) : null),
    'brandColors' => [
        'primary' => $letterhead->color_primary ?? '#2563EB',
        'secondary' => $letterhead->color_secondary ?? '#1E40AF',
    ]
];
```

### Update PDF Templates

**quotation.blade.php / invoice.blade.php:**

```blade
{{-- Company Header --}}
<div class="company-header">
    @if($logo)
        <img src="{{ $logo }}" alt="Logo" style="max-height: 80px;">
    @endif
    <div>
        <h1 style="color: {{ $brandColors['primary'] }}">{{ $company->name }}</h1>
        @if(isset($company->tagline))
            <p class="tagline">{{ $company->tagline }}</p>
        @endif
    </div>
</div>

{{-- Company Details --}}
<div class="company-details">
    <p>{{ $company->address }}</p>
    <p>{{ $company->city }}, {{ $company->state }} {{ $company->postal_code }}</p>
    <p>Tel: {{ $company->phone }} | Email: {{ $company->email }}</p>
    @if(isset($company->website))
        <p>{{ $company->website }}</p>
    @endif
    @if(isset($company->registration_number))
        <p>Reg No: {{ $company->registration_number }}</p>
    @endif
</div>

{{-- Bank Details (if available) --}}
@if(method_exists($company, 'hasOwnBankDetails') && $company->hasOwnBankDetails())
    <div class="bank-details">
        <strong>Bank Details:</strong>
        {{ $company->bank_name }} | {{ $company->bank_account_number }}
    </div>
@endif
```

---

## 🔄 Migration Strategy

### Phase 1: Database Setup
1. Create `company_brands` table migration
2. Add `company_brand_id` to `quotations` table
3. Add `company_brand_id` to `invoices` table
4. Run migrations

### Phase 2: Data Migration (Optional)
Create seeder to convert existing companies to default brands:
```php
// CompanyBrandSeeder
foreach (Company::all() as $company) {
    CompanyBrand::create([
        'company_id' => $company->id,
        'name' => $company->name,
        'address' => $company->address,
        'city' => $company->city,
        'state' => $company->state,
        'postal_code' => $company->postal_code,
        'phone' => $company->phone,
        'email' => $company->email,
        'logo_path' => $company->logo_path,
        'is_default' => true,
        'is_active' => true,
    ]);
}
```

### Phase 3: Backward Compatibility
- Documents without `company_brand_id` will use parent company details
- PDFService falls back to company if no brand assigned
- All existing documents continue to work

---

## 🛡️ Policy & Authorization

### CompanyBrandPolicy

**File:** `app/Policies/CompanyBrandPolicy.php`

**Rules:**
```php
- viewAny: company_manager+
- view: company_manager+ (same company)
- create: company_manager+
- update: company_manager+ (same company)
- delete: company_manager+ (same company, not used in documents)
- setDefault: company_manager+ (same company)
```

**Multi-tenant Security:**
```php
public function view(User $user, CompanyBrand $brand)
{
    return $user->company_id === $brand->company_id
        && $user->can('manage company settings');
}
```

---

## 🧪 Testing Considerations

### Manual Testing Checklist

**Brand Management:**
- [ ] Create new brand with all fields
- [ ] Upload brand logo
- [ ] Set brand as default
- [ ] Toggle brand active/inactive
- [ ] Update brand details
- [ ] Delete unused brand
- [ ] Prevent deletion of used brand

**Document Creation:**
- [ ] Create quotation with default brand
- [ ] Create quotation with specific brand
- [ ] Create quotation without brand (uses company)
- [ ] Brand selector shows only active brands
- [ ] Default brand is pre-selected

**PDF Generation:**
- [ ] PDF shows correct brand letterhead
- [ ] PDF shows correct logo
- [ ] PDF shows correct contact details
- [ ] PDF uses brand colors (if set)
- [ ] Fallback to company works correctly

**Multi-tenant Security:**
- [ ] Users can only see their company's brands
- [ ] Users cannot access other company's brands
- [ ] Proper authorization checks on all actions

---

## 📊 Implementation Phases

### Phase 1: Core Infrastructure (Day 1)
**Files to Create:**
- [ ] Migration: `create_company_brands_table.php`
- [ ] Migration: `add_company_brand_to_quotations.php`
- [ ] Migration: `add_company_brand_to_invoices.php`
- [ ] Model: `app/Models/CompanyBrand.php`
- [ ] Policy: `app/Policies/CompanyBrandPolicy.php`

**Files to Update:**
- [ ] `app/Models/Company.php` (add relationships)
- [ ] `app/Models/Quotation.php` (add brand relationship)
- [ ] `app/Models/Invoice.php` (add brand relationship)

### Phase 2: Management Interface (Day 1-2)
**Files to Create:**
- [ ] Controller: `app/Http/Controllers/CompanyBrandController.php`
- [ ] View: `resources/views/company-brands/index.blade.php`
- [ ] View: `resources/views/company-brands/create.blade.php`
- [ ] View: `resources/views/company-brands/edit.blade.php`
- [ ] View: `resources/views/company-brands/show.blade.php`
- [ ] Routes: Add brand management routes

### Phase 3: Document Integration (Day 2)
**Files to Update:**
- [ ] `app/Http/Controllers/QuotationController.php`
- [ ] `app/Http/Controllers/InvoiceController.php`
- [ ] `resources/views/quotations/create.blade.php`
- [ ] `resources/views/quotations/edit.blade.php`
- [ ] `resources/views/invoices/create.blade.php`
- [ ] `resources/views/invoices/edit.blade.php`

### Phase 4: PDF Enhancement (Day 2-3)
**Files to Update:**
- [ ] `app/Services/PDFService.php`
- [ ] `resources/views/pdf/quotation.blade.php`
- [ ] `resources/views/pdf/invoice.blade.php`

### Phase 5: Navigation & UI Polish (Day 3)
**Files to Update:**
- [ ] `resources/views/layouts/sidebar-navigation.blade.php` (add Brands menu)
- [ ] Add success/error messages
- [ ] Add confirmation dialogs
- [ ] Test all workflows

---

## 🎯 Success Criteria

### Functional Requirements
✅ Company managers can create multiple brands
✅ Each brand has independent contact details and logo
✅ Documents can be assigned to specific brands
✅ PDFs use correct brand letterhead
✅ Default brand system works correctly
✅ Multi-tenant security is maintained

### Non-Functional Requirements
✅ No breaking changes to existing documents
✅ Performance impact is minimal
✅ UI is intuitive and user-friendly
✅ Code follows existing patterns and standards

---

## 📝 Notes & Considerations

### Business Logic
1. **Default Brand**: Only one brand per company can be default
2. **Brand Deletion**: Cannot delete brand if used in any documents
3. **Inactive Brands**: Hidden from selectors but still work in existing documents
4. **Logo Management**: Old logos should be deleted when uploading new ones

### Technical Decisions
1. **NULL brand_id**: Documents with NULL use parent company details (backward compatible)
2. **Cascade Delete**: If company deleted, all brands deleted (safe - can't happen in multi-tenant)
3. **Soft Delete**: Not needed - use `is_active` flag instead
4. **File Storage**: Store brand logos in `storage/app/public/brands/logos/`

### Future Enhancements
- [ ] Brand-specific email templates
- [ ] Brand-specific terms & conditions
- [ ] Brand performance analytics
- [ ] Brand-specific pricing rules
- [ ] Export/Import brand configurations

---

## 🚀 Ready to Proceed?

All planning is complete. Implementation can begin following the phases outlined above.

**Estimated Implementation Time:** 2-3 days
**Complexity:** Medium
**Risk Level:** Low (backward compatible)

Would you like to proceed with Phase 1: Core Infrastructure?
