<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Cache;

class InvoiceSettingsService
{
    /**
     * Default invoice settings structure
     */
    public function getDefaultSettings(): array
    {
        return [
            'logo' => [
                'show_company_logo' => true,
                'logo_position' => 'left', // left, center, right
                'logo_size' => 'medium', // small, medium, large
            ],
            'sections' => [
                'show_shipping' => true,
                'show_payment_instructions' => true,
                'show_signatures' => true,
                'show_additional_notes' => false,
                'show_terms_conditions' => true,
            ],
            'layout' => [
                'header_style' => 'professional', // professional, minimal, modern
                'color_scheme' => 'blue', // blue, green, gray, custom
                'font_size' => 'normal', // small, normal, large
                'margins' => 'standard', // tight, standard, wide
            ],
            'appearance' => [
                'background_color' => '#ffffff',
                'border_color' => '#e5e7eb',
                'heading_color' => '#111827',
                'subheading_color' => '#1f2937',
                'text_color' => '#111827',
                'muted_text_color' => '#6b7280',
                'accent_color' => '#1d4ed8',
                'accent_text_color' => '#ffffff',
                'table_header_background' => '#1d4ed8',
                'table_header_text' => '#ffffff',
                'table_row_even' => '#f8fafc',
            ],
            'defaults' => [
                'payment_terms' => 30,
                'late_fee_percentage' => 1.5,
                'currency' => 'RM',
                'tax_percentage' => 6.0,
                'show_discount_column' => true,
                'show_tax_column' => true,
            ],
            'content' => [
                'default_terms' => 'Payment is due within the specified payment terms. Late payments may incur additional charges.',
                'default_notes' => 'Thank you for your business!',
                'payment_instructions' => [
                    'bank_name' => '',
                    'account_number' => '',
                    'account_holder' => '',
                    'swift_code' => '',
                    'additional_info' => 'Please include invoice number in payment reference.',
                ],
                'signature_blocks' => [
                    'show_company_signature' => true,
                    'show_client_signature' => true,
                    'company_signature_title' => 'Authorized Representative',
                    'client_signature_title' => 'Customer Acceptance',
                ],
            ],
            'columns' => [
                [
                    'key' => 'sl',
                    'label' => 'Sl.',
                    'visible' => true,
                    'order' => 1,
                ],
                [
                    'key' => 'description',
                    'label' => 'Description',
                    'visible' => true,
                    'order' => 2,
                ],
                [
                    'key' => 'quantity',
                    'label' => 'Qty',
                    'visible' => true,
                    'order' => 3,
                ],
                [
                    'key' => 'rate',
                    'label' => 'Rate',
                    'visible' => true,
                    'order' => 4,
                ],
                [
                    'key' => 'amount',
                    'label' => 'Amount',
                    'visible' => true,
                    'order' => 5,
                ],
            ],
        ];
    }

    /**
     * Get default settings for product invoice type
     */
    public function getDefaultProductInvoiceSettings(): array
    {
        $defaults = $this->getDefaultSettings();
        $defaults['template_name'] = 'Product Invoice Default';
        return $defaults;
    }

    /**
     * Get invoice settings for a company
     */
    public function getSettings(?int $companyId = null): array
    {
        $companyId = $companyId ?: (auth()->check() ? auth()->user()->company_id : null);

        if (!$companyId) {
            return $this->getDefaultSettings();
        }

        $cacheKey = "invoice_settings_company_{$companyId}";

        return Cache::remember($cacheKey, 3600, function () use ($companyId) {
            $company = Company::find($companyId);

            if (!$company || !$company->invoice_settings) {
                return $this->getDefaultSettings();
            }

            // Merge company settings with defaults to ensure all keys exist
            return array_merge_recursive($this->getDefaultSettings(), $company->invoice_settings);
        });
    }

    /**
     * Update invoice settings for a company
     */
    public function updateSettings(array $settings, ?int $companyId = null): bool
    {
        $companyId = $companyId ?: (auth()->check() ? auth()->user()->company_id : null);

        if (!$companyId) {
            return false;
        }

        $company = Company::find($companyId);
        if (!$company) {
            return false;
        }

        // Validate and merge with existing settings
        $currentSettings = $this->getSettings($companyId);
        $updatedSettings = array_merge_recursive($currentSettings, $settings);

        // Update company settings
        $company->update([
            'invoice_settings' => $updatedSettings
        ]);

        // Clear cache
        $cacheKey = "invoice_settings_company_{$companyId}";
        Cache::forget($cacheKey);

        return true;
    }

    /**
     * Get specific setting value
     */
    public function getSetting(string $key, $default = null, ?int $companyId = null)
    {
        $settings = $this->getSettings($companyId);

        return data_get($settings, $key, $default);
    }

    /**
     * Update specific setting value
     */
    public function setSetting(string $key, $value, ?int $companyId = null): bool
    {
        $currentSettings = $this->getSettings($companyId);

        // Use dot notation to set nested values
        data_set($currentSettings, $key, $value);

        return $this->updateSettings($currentSettings, $companyId);
    }

    /**
     * Reset settings to defaults
     */
    public function resetToDefaults(?int $companyId = null): bool
    {
        return $this->updateSettings($this->getDefaultSettings(), $companyId);
    }

    /**
     * Get invoice optional sections configuration
     */
    public function getOptionalSections(?int $companyId = null): array
    {
        return $this->getSetting('sections', [], $companyId);
    }

    /**
     * Update optional sections configuration
     */
    public function updateOptionalSections(array $sections, ?int $companyId = null): bool
    {
        return $this->setSetting('sections', $sections, $companyId);
    }

    /**
     * Get default payment instructions
     */
    public function getPaymentInstructions(?int $companyId = null): array
    {
        return $this->getSetting('content.payment_instructions', [], $companyId);
    }

    /**
     * Update payment instructions
     */
    public function updatePaymentInstructions(array $instructions, ?int $companyId = null): bool
    {
        return $this->setSetting('content.payment_instructions', $instructions, $companyId);
    }

    /**
     * Get logo settings
     */
    public function getLogoSettings(?int $companyId = null): array
    {
        return $this->getSetting('logo', [], $companyId);
    }

    /**
     * Check if a section should be shown by default
     */
    public function shouldShowSection(string $section, ?int $companyId = null): bool
    {
        return $this->getSetting("sections.show_{$section}", false, $companyId);
    }

    /**
     * Get invoice settings for API response
     */
    public function getSettingsForAPI(?int $companyId = null): array
    {
        $settings = $this->getSettings($companyId);

        return [
            'optional_sections' => $settings['sections'],
            'logo_settings' => $settings['logo'],
            'layout' => $settings['layout'],
            'appearance' => $settings['appearance'],
            'defaults' => $settings['defaults'],
            'payment_instructions' => $settings['content']['payment_instructions'],
            'signature_blocks' => $settings['content']['signature_blocks'],
            'default_terms' => $settings['content']['default_terms'],
            'default_notes' => $settings['content']['default_notes'],
        ];
    }

    /**
     * Apply invoice settings to an invoice instance
     */
    public function applySettingsToInvoice($invoice, ?int $companyId = null): void
    {
        $settings = $this->getSettings($companyId);

        // Apply default optional sections if not set
        if (!$invoice->optional_sections) {
            $invoice->optional_sections = $settings['sections'];
        }

        // Apply default terms and notes if not set
        if (!$invoice->terms_conditions) {
            $invoice->terms_conditions = $settings['content']['default_terms'];
        }

        if (!$invoice->notes) {
            $invoice->notes = $settings['content']['default_notes'];
        }

        // Apply payment terms if not set
        if (!$invoice->payment_terms) {
            $invoice->payment_terms = $settings['defaults']['payment_terms'];
        }

        // Apply tax percentage if not set
        if (!$invoice->tax_percentage) {
            $invoice->tax_percentage = $settings['defaults']['tax_percentage'];
        }
    }

    /**
     * Validate settings structure
     */
    public function validateSettings(array $settings): array
    {
        $errors = [];

        // Validate logo settings
        if (isset($settings['logo']['logo_position']) && !in_array($settings['logo']['logo_position'], ['left', 'center', 'right'])) {
            $errors['logo.logo_position'] = 'Logo position must be left, center, or right.';
        }

        // Validate payment terms
        if (isset($settings['defaults']['payment_terms']) && (!is_numeric($settings['defaults']['payment_terms']) || $settings['defaults']['payment_terms'] < 0)) {
            $errors['defaults.payment_terms'] = 'Payment terms must be a positive number.';
        }

        // Validate tax percentage
        if (isset($settings['defaults']['tax_percentage']) && (!is_numeric($settings['defaults']['tax_percentage']) || $settings['defaults']['tax_percentage'] < 0 || $settings['defaults']['tax_percentage'] > 100)) {
            $errors['defaults.tax_percentage'] = 'Tax percentage must be between 0 and 100.';
        }

        // Validate appearance colors
        if (isset($settings['appearance'])) {
            $colorFields = [
                'background_color', 'border_color', 'heading_color', 'subheading_color',
                'text_color', 'muted_text_color', 'accent_color', 'accent_text_color',
                'table_header_background', 'table_header_text', 'table_row_even'
            ];

            foreach ($colorFields as $field) {
                if (isset($settings['appearance'][$field])) {
                    $color = $settings['appearance'][$field];
                    // Validate hex color format (#RGB or #RRGGBB)
                    if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
                        $errors["appearance.{$field}"] = 'Color must be a valid hex format (#RGB or #RRGGBB).';
                    }
                }
            }
        }

        // Validate columns structure
        if (isset($settings['columns']) && is_array($settings['columns'])) {
            foreach ($settings['columns'] as $index => $column) {
                if (!isset($column['key']) || !isset($column['label'])) {
                    $errors["columns.{$index}"] = 'Each column must have a key and label.';
                }
                if (isset($column['label'])) {
                    if (!is_string($column['label'])) {
                        $errors["columns.{$index}.label"] = 'Column label must be a string.';
                    } elseif (strlen($column['label']) > 50) {
                        $errors["columns.{$index}.label"] = 'Column label must be 50 characters or less.';
                    }
                }
            }
        }

        // Validate content max lengths
        if (isset($settings['content']['default_terms'])) {
            if (is_array($settings['content']['default_terms'])) {
                $errors['content.default_terms'] = 'Default terms must be a string.';
            } elseif (strlen($settings['content']['default_terms']) > 2000) {
                $errors['content.default_terms'] = 'Default terms must be 2000 characters or less.';
            }
        }
        if (isset($settings['content']['default_notes'])) {
            if (is_array($settings['content']['default_notes'])) {
                $errors['content.default_notes'] = 'Default notes must be a string.';
            } elseif (strlen($settings['content']['default_notes']) > 1000) {
                $errors['content.default_notes'] = 'Default notes must be 1000 characters or less.';
            }
        }

        return $errors;
    }

    /**
     * Get appearance/palette settings with fallback
     */
    public function getAppearance(?int $companyId = null): array
    {
        return $this->getSetting('appearance', $this->getDefaultSettings()['appearance'], $companyId);
    }

    /**
     * Get columns configuration with fallback
     */
    public function getColumns(?int $companyId = null): array
    {
        return $this->getSetting('columns', $this->getDefaultSettings()['columns'], $companyId);
    }

    /**
     * Update columns configuration
     */
    public function updateColumns(array $columns, ?int $companyId = null): bool
    {
        // Validate and sort by order
        usort($columns, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));
        return $this->setSetting('columns', $columns, $companyId);
    }

    /**
     * Get default content (terms, notes, etc.)
     */
    public function getDefaultContent(?int $companyId = null): array
    {
        return $this->getSetting('content', $this->getDefaultSettings()['content'], $companyId);
    }

    /**
     * Get merged settings for PDF rendering with proper precedence:
     * 1. Document-specific values (from $invoice)
     * 2. Company document_settings.types.product_invoice
     * 3. Company invoice_settings (legacy)
     * 4. Hard-coded defaults
     */
    public function getMergedSettingsForPDF($invoice, ?int $companyId = null): array
    {
        $companyId = $companyId ?: $invoice->company_id;
        $company = \App\Models\Company::find($companyId);

        // Start with hard-coded defaults
        $merged = $this->getDefaultSettings();

        // Layer 3: Legacy company.invoice_settings (if exists)
        if ($company && !empty($company->invoice_settings)) {
            $merged = array_replace_recursive($merged, $company->invoice_settings);
        }

        // Layer 2: New company.document_settings.types.product_invoice (future enhancement)
        // if ($company && !empty($company->document_settings['types']['product_invoice'])) {
        //     $merged = array_replace_recursive($merged, $company->document_settings['types']['product_invoice']);
        // }

        // Layer 1: Invoice-specific overrides
        if (!empty($invoice->optional_sections)) {
            $invoiceSections = is_string($invoice->optional_sections)
                ? json_decode($invoice->optional_sections, true)
                : $invoice->optional_sections;
            if (is_array($invoiceSections)) {
                $merged['sections'] = array_replace($merged['sections'], $invoiceSections);
            }
        }

        return $merged;
    }
}