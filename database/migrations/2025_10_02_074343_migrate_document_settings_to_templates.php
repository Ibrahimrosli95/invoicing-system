<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
use App\Models\InvoiceNoteTemplate;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Migrate existing document settings from company.settings JSON
     * to proper invoice_note_templates table entries.
     */
    public function up(): void
    {
        // Process each company
        Company::chunk(100, function ($companies) {
            foreach ($companies as $company) {
                $this->migrateCompanySettings($company);
            }
        });
    }

    /**
     * Migrate a single company's document settings to templates.
     */
    private function migrateCompanySettings(Company $company): void
    {
        $settings = $company->settings ?? [];
        $documentSettings = $settings['document'] ?? [];

        // Define default content if not present
        $defaults = [
            'notes' => 'Thank you for your business!',
            'terms' => 'Payment is due within 30 days. Late payments may incur additional charges.',
            'payment_instructions' => "Please make payments to:\n\nCompany: {$company->name}\nBank: [Your Bank Name]\nAccount: [Your Account Number]\n\nPlease include invoice number in payment reference.",
        ];

        // Migrate Invoice Notes
        $this->createOrUpdateTemplate(
            $company->id,
            'notes',
            'Default Invoice Notes',
            $documentSettings['invoice_notes'] ?? $defaults['notes']
        );

        // Migrate Invoice Terms
        $this->createOrUpdateTemplate(
            $company->id,
            'terms',
            'Default Terms & Conditions',
            $documentSettings['invoice_terms'] ?? $defaults['terms']
        );

        // Migrate Payment Instructions
        $paymentInstructions = $documentSettings['payment_instructions'] ?? null;
        if (!$paymentInstructions && isset($documentSettings['bank_details'])) {
            $paymentInstructions = $documentSettings['bank_details'];
        }
        if (!$paymentInstructions) {
            $paymentInstructions = $defaults['payment_instructions'];
        }

        $this->createOrUpdateTemplate(
            $company->id,
            'payment_instructions',
            'Default Payment Instructions',
            $paymentInstructions
        );

        \Log::info('Migrated document settings to templates', [
            'company_id' => $company->id,
            'company_name' => $company->name,
        ]);
    }

    /**
     * Create or update a template, ensuring only one default per type.
     */
    private function createOrUpdateTemplate(int $companyId, string $type, string $name, string $content): void
    {
        // Check if a default template already exists for this type
        $existingDefault = InvoiceNoteTemplate::where('company_id', $companyId)
            ->where('type', $type)
            ->where('is_default', true)
            ->first();

        if ($existingDefault) {
            // Update existing default template if content is different
            if ($existingDefault->content !== $content) {
                $existingDefault->update(['content' => $content]);
                \Log::info('Updated existing default template', [
                    'template_id' => $existingDefault->id,
                    'type' => $type,
                ]);
            }
        } else {
            // Create new default template
            InvoiceNoteTemplate::create([
                'company_id' => $companyId,
                'name' => $name,
                'type' => $type,
                'content' => $content,
                'is_default' => true,
                'is_active' => true,
            ]);

            \Log::info('Created new default template', [
                'company_id' => $companyId,
                'type' => $type,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * Note: This doesn't restore the old settings as they may have been modified.
     * Templates created by this migration will remain for data preservation.
     */
    public function down(): void
    {
        // We don't delete templates as they may have been modified by users
        // This is a one-way migration for data integrity
        \Log::warning('Rollback of template migration does not restore old document settings');
    }
};
