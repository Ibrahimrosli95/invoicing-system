<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Move signature configuration from company.settings['document']
     * to company.invoice_settings['signature']
     */
    public function up(): void
    {
        Company::chunk(100, function ($companies) {
            foreach ($companies as $company) {
                $this->migrateSignatureToInvoiceSettings($company);
            }
        });
    }

    /**
     * Migrate a single company's signature settings to invoice_settings.
     */
    private function migrateSignatureToInvoiceSettings(Company $company): void
    {
        $settings = $company->settings ?? [];
        $documentSettings = $settings['document'] ?? [];

        // Extract signature data from document settings
        $signatureName = $documentSettings['signature_name'] ?? '';
        $signatureTitle = $documentSettings['signature_title'] ?? '';
        $signaturePath = $documentSettings['signature_path'] ?? '';

        // Get current invoice_settings or create empty array
        $invoiceSettings = $company->invoice_settings ?? [];

        // Only migrate if there's actual signature data
        if ($signatureName || $signatureTitle || $signaturePath) {
            // Add signature to invoice_settings
            $invoiceSettings['signature'] = [
                'name' => $signatureName,
                'title' => $signatureTitle,
                'image_path' => $signaturePath,
            ];

            // Update company
            $company->update([
                'invoice_settings' => $invoiceSettings
            ]);

            \Log::info('Migrated signature to invoice_settings', [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'has_signature_image' => !empty($signaturePath),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * Move signature back from invoice_settings to document settings.
     * Note: This preserves data but doesn't delete from invoice_settings.
     */
    public function down(): void
    {
        Company::chunk(100, function ($companies) {
            foreach ($companies as $company) {
                $this->restoreSignatureToDocumentSettings($company);
            }
        });
    }

    /**
     * Restore signature back to document settings.
     */
    private function restoreSignatureToDocumentSettings(Company $company): void
    {
        $invoiceSettings = $company->invoice_settings ?? [];
        $signature = $invoiceSettings['signature'] ?? [];

        // Only restore if there's signature data
        if (!empty($signature)) {
            $settings = $company->settings ?? [];
            $documentSettings = $settings['document'] ?? [];

            // Restore to document settings
            $documentSettings['signature_name'] = $signature['name'] ?? '';
            $documentSettings['signature_title'] = $signature['title'] ?? '';
            $documentSettings['signature_path'] = $signature['image_path'] ?? '';

            $settings['document'] = $documentSettings;

            // Update company
            $company->update([
                'settings' => $settings
            ]);

            \Log::info('Restored signature to document settings', [
                'company_id' => $company->id,
                'company_name' => $company->name,
            ]);
        }
    }
};
