<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Cache;

class InvoiceSettingsService
{
    /**
     * Default invoice settings structure - SIMPLIFIED FOR PDF GENERATION
     */
    public function getDefaultSettings(): array
    {
        return [
            // Color palette for PDF
            'appearance' => [
                'accent_color' => '#0b57d0',
                'accent_text_color' => '#ffffff',
                'text_color' => '#000000',
                'muted_text_color' => '#4b5563',
                'heading_color' => '#000000',
                'border_color' => '#d0d5dd',
            ],

            // Table columns configuration
            'columns' => [
                ['key' => 'sl', 'label' => 'Sl.', 'visible' => true, 'order' => 1],
                ['key' => 'description', 'label' => 'Description', 'visible' => true, 'order' => 2],
                ['key' => 'quantity', 'label' => 'Qty', 'visible' => true, 'order' => 3],
                ['key' => 'rate', 'label' => 'Rate', 'visible' => true, 'order' => 4],
                ['key' => 'amount', 'label' => 'Amount', 'visible' => true, 'order' => 5],
            ],

            // Section visibility toggles
            'sections' => [
                'show_company_logo' => true,
                'show_payment_instructions' => true,
                'show_signatures' => true,
            ],

            // Payment instructions for PDF
            'payment_instructions' => [
                'bank_name' => '',
                'account_number' => '',
                'account_holder' => '',
                'additional_info' => 'Please include invoice number in payment reference.',
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
        // Use array_replace_recursive instead of array_merge_recursive
        // to properly replace values instead of merging arrays
        $currentSettings = $this->getSettings($companyId);
        $updatedSettings = array_replace_recursive($currentSettings, $settings);

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
     * Get invoice settings for API response (SIMPLIFIED)
     */
    public function getSettingsForAPI(?int $companyId = null): array
    {
        $settings = $this->getSettings($companyId);

        return [
            'appearance' => $settings['appearance'] ?? $this->getDefaultSettings()['appearance'],
            'columns' => $settings['columns'] ?? $this->getDefaultSettings()['columns'],
            'sections' => $settings['sections'] ?? $this->getDefaultSettings()['sections'],
            'payment_instructions' => $settings['payment_instructions'] ?? $this->getDefaultSettings()['payment_instructions'],
        ];
    }

    /**
     * Apply invoice settings to an invoice instance (SIMPLIFIED)
     */
    public function applySettingsToInvoice($invoice, ?int $companyId = null): void
    {
        $settings = $this->getSettings($companyId);

        // Apply default optional sections if not set
        if (!$invoice->optional_sections) {
            $invoice->optional_sections = $settings['sections'] ?? $this->getDefaultSettings()['sections'];
        }

        // Note: terms_conditions, notes, payment_terms, and tax_percentage are now
        // managed at the invoice level, not in settings, so we don't apply them here
    }

    /**
     * Validate settings structure (SIMPLIFIED)
     */
    public function validateSettings(array $settings): array
    {
        $errors = [];

        // Validate appearance colors (SIMPLIFIED - only 6 essential colors)
        if (isset($settings['appearance'])) {
            $colorFields = ['accent_color', 'accent_text_color', 'text_color', 'muted_text_color', 'heading_color', 'border_color'];

            foreach ($colorFields as $field) {
                if (isset($settings['appearance'][$field])) {
                    $color = $settings['appearance'][$field];

                    if (!is_string($color)) {
                        $errors["appearance.{$field}"] = 'Color must be a string.';
                        continue;
                    }

                    if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
                        $errors["appearance.{$field}"] = 'Color must be a valid hex format.';
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
                if (isset($column['label']) && !is_string($column['label'])) {
                    $errors["columns.{$index}.label"] = 'Column label must be a string.';
                }
            }
        }

        // Validate sections (simple booleans)
        if (isset($settings['sections'])) {
            foreach (['show_company_logo', 'show_payment_instructions', 'show_signatures'] as $field) {
                if (isset($settings['sections'][$field]) && !is_bool($settings['sections'][$field])) {
                    $errors["sections.{$field}"] = 'Must be true or false.';
                }
            }
        }

        // Validate payment instructions (optional strings)
        if (isset($settings['payment_instructions'])) {
            foreach (['bank_name', 'account_number', 'account_holder', 'additional_info'] as $field) {
                if (isset($settings['payment_instructions'][$field]) && !is_string($settings['payment_instructions'][$field])) {
                    $errors["payment_instructions.{$field}"] = 'Must be a string.';
                }
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