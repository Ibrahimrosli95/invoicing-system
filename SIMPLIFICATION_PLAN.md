# Invoice Settings Simplification Plan

## Current Problem
- Too many settings fields causing validation errors
- Complex nested structure difficult to maintain
- Many fields not directly used in PDF generation

## Essential Settings for PDF Generation

### 1. Appearance (Colors) - KEEP
```php
'appearance' => [
    'accent_color' => '#0b57d0',           // Table header, separator
    'accent_text_color' => '#ffffff',      // Table header text
    'text_color' => '#000000',             // Main text
    'muted_text_color' => '#4b5563',       // Secondary text
    'heading_color' => '#000000',          // Headings
    'border_color' => '#d0d5dd',           // Borders, lines
]
```

### 2. Columns Configuration - KEEP
```php
'columns' => [
    ['key' => 'sl', 'label' => 'Sl.', 'visible' => true, 'order' => 1],
    ['key' => 'description', 'label' => 'Description', 'visible' => true, 'order' => 2],
    ['key' => 'quantity', 'label' => 'Qty', 'visible' => true, 'order' => 3],
    ['key' => 'rate', 'label' => 'Rate', 'visible' => true, 'order' => 4],
    ['key' => 'amount', 'label' => 'Amount', 'visible' => true, 'order' => 5],
]
```

### 3. Visibility Toggles - KEEP
```php
'sections' => [
    'show_company_logo' => true,
    'show_payment_instructions' => true,
    'show_signatures' => true,
]
```

### 4. Payment Instructions - KEEP (used in PDF)
```php
'payment_instructions' => [
    'bank_name' => '',
    'account_number' => '',
    'account_holder' => '',
    'additional_info' => '',
]
```

## What to REMOVE (Not Used in Current PDF)

### Remove from Service
- `layout` (header_style, color_scheme, font_size, margins)
- `defaults` (payment_terms, late_fee_percentage, tax_percentage, etc.)
- `content.default_terms` and `content.default_notes`
- `content.signature_blocks` (can use simple boolean)
- `logo` position and size (use defaults)

### Remove from Frontend
- Layout customization section
- Default terms/notes textareas
- Payment terms, late fees, tax config
- Logo positioning controls

## Simplified Structure

```php
[
    'appearance' => [...],     // 6 colors
    'columns' => [...],        // 5 column configs
    'sections' => [            // 3 toggles
        'show_company_logo' => true,
        'show_payment_instructions' => true,
        'show_signatures' => true,
    ],
    'payment_instructions' => [...],  // Bank details for PDF
]
```

## Implementation Steps

1. ✅ Create this plan
2. Update InvoiceSettingsService defaults
3. Update validation to match new structure
4. Simplify frontend UI (remove unnecessary sections)
5. Update controller methods
6. Test PDF generation
7. Clean up unused code