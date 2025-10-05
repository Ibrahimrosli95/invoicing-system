<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Quotation;
use Illuminate\Support\Facades\Cache;

class ProductQuotationSettingsService
{
    public function __construct(private InvoiceSettingsService $invoiceSettingsService)
    {
    }

    /**
     * Default settings for product quotation PDFs.
     */
    public function getDefaultSettings(): array
    {
        $defaults = $this->invoiceSettingsService->getDefaultSettings();

        // Ensure appearance includes the table header palette used by the PDF layout.
        $defaults['appearance']['table_header_background'] = $defaults['appearance']['accent_color'] ?? '#0b57d0';
        $defaults['appearance']['table_header_text'] = '#ffffff';

        // Provide a defaults bucket for frequently referenced values.
        $defaults['defaults'] = array_merge([
            'currency' => 'RM',
        ], $defaults['defaults'] ?? []);

        // Mirror invoice optional sections but disable customer/company signatures by default.
        $defaults['sections'] = array_merge([
            'show_company_logo' => true,
            'show_payment_instructions' => true,
            'show_signatures' => true,
            'show_company_signature' => false,
            'show_customer_signature' => false,
        ], $defaults['sections'] ?? []);

        return $defaults;
    }

    /**
     * Retrieve product quotation settings for a company with sensible fallbacks.
     */
    public function getSettings(?int $companyId = null): array
    {
        $companyId = $companyId ?: (auth()->check() ? auth()->user()->company_id : null);
        $defaults = $this->getDefaultSettings();

        if (!$companyId) {
            return $defaults;
        }

        $cacheKey = "product_quotation_settings_company_{$companyId}";

        return Cache::remember($cacheKey, 3600, function () use ($companyId, $defaults) {
            $company = Company::find($companyId);

            if (!$company) {
                return $defaults;
            }

            $invoiceSettings = $company->invoice_settings ?? [];
            $productSettings = data_get($company->settings, 'product_quotation_settings', []);

            $merged = $defaults;

            if (!empty($invoiceSettings)) {
                $merged = array_replace_recursive($merged, $invoiceSettings);
            }

            if (!empty($productSettings)) {
                $merged = array_replace_recursive($merged, $productSettings);
            }

            return $merged;
        });
    }

    /**
     * Merge company settings with quotation-level overrides.
     */
    public function getMergedSettingsForPDF(Quotation $quotation, ?int $companyId = null): array
    {
        $settings = $this->getSettings($companyId ?: $quotation->company_id);

        if (!empty($quotation->optional_sections)) {
            $quotationSections = is_string($quotation->optional_sections)
                ? json_decode($quotation->optional_sections, true)
                : $quotation->optional_sections;

            if (is_array($quotationSections)) {
                $settings['sections'] = array_replace(
                    $settings['sections'] ?? [],
                    $quotationSections
                );
            }
        }

        return $settings;
    }

    /**
     * Persist updated product quotation settings for a company.
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

        $settingsPayload = $company->settings ?? [];
        $existing = data_get($settingsPayload, 'product_quotation_settings', []);
        $updated = array_replace_recursive($existing, $settings);
        data_set($settingsPayload, 'product_quotation_settings', $updated);

        $company->update(['settings' => $settingsPayload]);

        Cache::forget("product_quotation_settings_company_{$companyId}");

        return true;
    }
}
