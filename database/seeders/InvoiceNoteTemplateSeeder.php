<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\InvoiceNoteTemplate;

class InvoiceNoteTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Ensures all companies have default invoice note templates.
     */
    public function run(): void
    {
        $this->command->info('Seeding default invoice note templates...');

        Company::chunk(100, function ($companies) {
            foreach ($companies as $company) {
                $this->createDefaultTemplatesForCompany($company);
            }
        });

        $this->command->info('Default invoice note templates seeded successfully!');
    }

    /**
     * Create default templates for a company if they don't exist.
     */
    private function createDefaultTemplatesForCompany(Company $company): void
    {
        $templatesCreated = 0;

        // Notes Template
        if (!$this->hasDefaultTemplate($company->id, 'notes')) {
            InvoiceNoteTemplate::create([
                'company_id' => $company->id,
                'name' => 'Default Invoice Notes',
                'type' => 'notes',
                'content' => 'Thank you for your business!',
                'is_default' => true,
                'is_active' => true,
            ]);
            $templatesCreated++;
        }

        // Terms Template
        if (!$this->hasDefaultTemplate($company->id, 'terms')) {
            InvoiceNoteTemplate::create([
                'company_id' => $company->id,
                'name' => 'Default Terms & Conditions',
                'type' => 'terms',
                'content' => 'Payment is due within 30 days. Late payments may incur additional charges.',
                'is_default' => true,
                'is_active' => true,
            ]);
            $templatesCreated++;
        }

        // Payment Instructions Template
        if (!$this->hasDefaultTemplate($company->id, 'payment_instructions')) {
            InvoiceNoteTemplate::create([
                'company_id' => $company->id,
                'name' => 'Default Payment Instructions',
                'type' => 'payment_instructions',
                'content' => "Please make payments to:\n\nCompany: {$company->name}\nBank: [Your Bank Name]\nAccount: [Your Account Number]\n\nPlease include invoice number in payment reference.",
                'is_default' => true,
                'is_active' => true,
            ]);
            $templatesCreated++;
        }

        if ($templatesCreated > 0) {
            $this->command->info("  Created {$templatesCreated} default templates for: {$company->name}");
        }
    }

    /**
     * Check if a company has a default template for a given type.
     */
    private function hasDefaultTemplate(int $companyId, string $type): bool
    {
        return InvoiceNoteTemplate::where('company_id', $companyId)
            ->where('type', $type)
            ->where('is_default', true)
            ->exists();
    }

    /**
     * Create some example templates for variety.
     */
    private function createExampleTemplates(): void
    {
        // Get first company for examples
        $company = Company::first();

        if (!$company) {
            return;
        }

        // Example: Friendly Notes
        InvoiceNoteTemplate::create([
            'company_id' => $company->id,
            'name' => 'Friendly Thank You',
            'type' => 'notes',
            'content' => "We truly appreciate your business! If you have any questions about this invoice, please don't hesitate to reach out.",
            'is_default' => false,
            'is_active' => true,
        ]);

        // Example: Formal Terms
        InvoiceNoteTemplate::create([
            'company_id' => $company->id,
            'name' => 'Strict Payment Terms',
            'type' => 'terms',
            'content' => "Payment is due within 14 days of invoice date. Late payments will incur a 5% monthly interest charge. All disputes must be raised within 7 days of invoice receipt.",
            'is_default' => false,
            'is_active' => true,
        ]);

        // Example: Detailed Payment Instructions
        InvoiceNoteTemplate::create([
            'company_id' => $company->id,
            'name' => 'Multiple Payment Methods',
            'type' => 'payment_instructions',
            'content' => "Payment Options:\n\n1. Bank Transfer:\n   Bank: Maybank\n   Account: 1234567890\n   Reference: Invoice Number\n\n2. Cheque:\n   Payable to: {$company->name}\n   Mail to: [Your Address]\n\n3. Online Payment:\n   Visit: [Payment Portal URL]",
            'is_default' => false,
            'is_active' => true,
        ]);

        $this->command->info('  Created example templates for demonstration');
    }
}
