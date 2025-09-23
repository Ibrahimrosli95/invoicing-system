<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class DocumentSettingsController extends Controller
{

    /**
     * Display the document settings page.
     */
    public function index(): View
    {
        $company = auth()->user()->company;
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        // Get current document settings
        $documentSettings = $company->settings['document'] ?? $this->getDefaultDocumentSettings();

        return view('settings.documents.index', compact('company', 'documentSettings'));
    }

    /**
     * Update document settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $company = auth()->user()->company;
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        $validated = $request->validate([
            // Terms & Conditions
            'quotation_terms' => 'nullable|string|max:10000',
            'invoice_terms' => 'nullable|string|max:10000',
            'payment_terms' => 'nullable|string|max:10000',
            
            // Default Notes
            'quotation_notes' => 'nullable|string|max:5000',
            'invoice_notes' => 'nullable|string|max:5000',
            'payment_notes' => 'nullable|string|max:5000',
            
            // Payment Instructions
            'payment_instructions' => 'nullable|string|max:10000',
            'bank_details' => 'nullable|string|max:5000',
            
            // Signatures
            'signature_name' => 'nullable|string|max:255',
            'signature_title' => 'nullable|string|max:255',
            'signature_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'remove_signature' => 'nullable|boolean',
            
            // Document Settings
            'quotation_validity_days' => 'nullable|integer|min:1|max:365',
            'invoice_due_days' => 'nullable|integer|min:1|max:365',
            'late_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'late_fee_grace_days' => 'nullable|integer|min:0|max:90',
            
            // Footer Settings
            'footer_text' => 'nullable|string|max:1000',
            'show_company_registration' => 'boolean',
            'show_tax_number' => 'boolean',
            'show_website' => 'boolean',
        ]);

        // Handle signature upload
        $settings = $company->settings ?? [];
        $documentSettings = $settings['document'] ?? [];

        if ($request->hasFile('signature_image')) {
            // Delete old signature if exists
            if (isset($documentSettings['signature_path']) && Storage::disk('public')->exists($documentSettings['signature_path'])) {
                Storage::disk('public')->delete($documentSettings['signature_path']);
            }

            // Store new signature
            $signaturePath = $request->file('signature_image')->store('signatures', 'public');
            $validated['signature_path'] = $signaturePath;
        } elseif ($request->boolean('remove_signature')) {
            // Remove existing signature
            if (isset($documentSettings['signature_path']) && Storage::disk('public')->exists($documentSettings['signature_path'])) {
                Storage::disk('public')->delete($documentSettings['signature_path']);
            }
            $validated['signature_path'] = null;
        } else {
            // Keep existing signature
            $validated['signature_path'] = $documentSettings['signature_path'] ?? null;
        }

        // Remove upload fields from validated data
        unset($validated['signature_image'], $validated['remove_signature']);

        // Update document settings
        $settings['document'] = array_merge($documentSettings, $validated);
        $company->update(['settings' => $settings]);

        return redirect()->route('settings.documents.index')
            ->with('success', 'Document settings updated successfully.');
    }

    /**
     * Show bank accounts management.
     */
    public function bankAccounts(): View
    {
        $company = auth()->user()->company;
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        $bankAccounts = $company->settings['bank_accounts'] ?? [];

        return view('settings.documents.bank-accounts', compact('company', 'bankAccounts'));
    }

    /**
     * Update bank accounts.
     */
    public function updateBankAccounts(Request $request): RedirectResponse
    {
        $company = auth()->user()->company;
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        $validated = $request->validate([
            'bank_accounts' => 'nullable|array',
            'bank_accounts.*.bank_name' => 'required|string|max:255',
            'bank_accounts.*.account_name' => 'required|string|max:255',
            'bank_accounts.*.account_number' => 'required|string|max:50',
            'bank_accounts.*.branch' => 'nullable|string|max:255',
            'bank_accounts.*.swift_code' => 'nullable|string|max:20',
            'bank_accounts.*.is_primary' => 'boolean',
        ]);

        // Ensure only one primary account
        if (isset($validated['bank_accounts'])) {
            $primaryCount = 0;
            foreach ($validated['bank_accounts'] as &$account) {
                if (isset($account['is_primary']) && $account['is_primary']) {
                    $primaryCount++;
                    if ($primaryCount > 1) {
                        $account['is_primary'] = false;
                    }
                }
            }
        }

        $settings = $company->settings ?? [];
        $settings['bank_accounts'] = $validated['bank_accounts'] ?? [];
        $company->update(['settings' => $settings]);

        return redirect()->route('settings.documents.bank-accounts')
            ->with('success', 'Bank accounts updated successfully.');
    }

    /**
     * Show custom fields management.
     */
    public function customFields(): View
    {
        $company = auth()->user()->company;
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        $customFields = $company->settings['custom_fields'] ?? $this->getDefaultCustomFields();

        return view('settings.documents.custom-fields', compact('company', 'customFields'));
    }

    /**
     * Update custom fields.
     */
    public function updateCustomFields(Request $request): RedirectResponse
    {
        $company = auth()->user()->company;
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        $validated = $request->validate([
            'quotation_fields' => 'nullable|array',
            'quotation_fields.*.label' => 'required|string|max:255',
            'quotation_fields.*.type' => 'required|string|in:text,textarea,number,date,select',
            'quotation_fields.*.required' => 'boolean',
            'quotation_fields.*.options' => 'nullable|string|max:1000',
            
            'invoice_fields' => 'nullable|array',
            'invoice_fields.*.label' => 'required|string|max:255',
            'invoice_fields.*.type' => 'required|string|in:text,textarea,number,date,select',
            'invoice_fields.*.required' => 'boolean',
            'invoice_fields.*.options' => 'nullable|string|max:1000',
        ]);

        $settings = $company->settings ?? [];
        $settings['custom_fields'] = [
            'quotation_fields' => $validated['quotation_fields'] ?? [],
            'invoice_fields' => $validated['invoice_fields'] ?? [],
        ];
        $company->update(['settings' => $settings]);

        return redirect()->route('settings.documents.custom-fields')
            ->with('success', 'Custom fields updated successfully.');
    }

    /**
     * Export document settings as JSON.
     */
    public function export(): \Illuminate\Http\JsonResponse
    {
        $company = auth()->user()->company;
        $documentSettings = $company->settings['document'] ?? [];

        // Remove sensitive file paths from export
        $exportSettings = $documentSettings;
        unset($exportSettings['signature_path']);

        return response()->json([
            'company_name' => $company->name,
            'exported_at' => now()->toISOString(),
            'document_settings' => $exportSettings,
        ]);
    }

    /**
     * Import document settings from JSON.
     */
    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'import_file' => 'required|file|mimes:json|max:1024',
        ]);

        try {
            $content = file_get_contents($validated['import_file']->path());
            $data = json_decode($content, true);

            if (!$data || !isset($data['document_settings'])) {
                throw new \Exception('Invalid import file format');
            }

            $company = auth()->user()->company;
            $settings = $company->settings ?? [];
            
            // Merge imported settings (excluding file paths for security)
            $importedSettings = $data['document_settings'];
            unset($importedSettings['signature_path']); // Don't import file paths
            
            $settings['document'] = array_merge($settings['document'] ?? [], $importedSettings);
            $company->update(['settings' => $settings]);

            return redirect()->route('settings.documents.index')
                ->with('success', 'Document settings imported successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['import_file' => 'Failed to import: ' . $e->getMessage()]);
        }
    }

    /**
     * Get default document settings.
     */
    private function getDefaultDocumentSettings(): array
    {
        return [
            'quotation_terms' => "1. This quotation is valid for 30 days from the date of issue.\n2. Prices are inclusive of all applicable taxes unless otherwise stated.\n3. Payment terms: 50% advance, 50% on completion.\n4. Delivery time: As specified in the quotation.\n5. Any changes to the scope of work may result in additional charges.",
            'invoice_terms' => "1. Payment is due within 30 days of invoice date.\n2. Late payment may incur additional charges.\n3. All disputes must be raised within 7 days of invoice date.\n4. Payment should be made to the bank account details provided.\n5. Please quote the invoice number when making payment.",
            'payment_terms' => "NET 30 days from invoice date",
            'quotation_notes' => "Thank you for your interest in our services. We look forward to working with you.",
            'invoice_notes' => "Thank you for your business. Please retain this invoice for your records.",
            'payment_notes' => "Payment can be made via bank transfer or cheque.",
            'payment_instructions' => "Please make payment to the bank account details provided below. Include the invoice number as reference.",
            'bank_details' => "Please contact us for bank account details.",
            'quotation_validity_days' => 30,
            'invoice_due_days' => 30,
            'late_fee_percentage' => 2.0,
            'late_fee_grace_days' => 7,
            'footer_text' => "This document is computer generated and does not require a signature.",
            'show_company_registration' => true,
            'show_tax_number' => true,
            'show_website' => true,
        ];
    }

    /**
     * Get default custom fields.
     */
    private function getDefaultCustomFields(): array
    {
        return [
            'quotation_fields' => [
                [
                    'label' => 'Project Reference',
                    'type' => 'text',
                    'required' => false,
                    'options' => null,
                ],
                [
                    'label' => 'Expected Start Date',
                    'type' => 'date',
                    'required' => false,
                    'options' => null,
                ],
            ],
            'invoice_fields' => [
                [
                    'label' => 'Purchase Order Number',
                    'type' => 'text',
                    'required' => false,
                    'options' => null,
                ],
                [
                    'label' => 'Delivery Date',
                    'type' => 'date',
                    'required' => false,
                    'options' => null,
                ],
            ],
        ];
    }
}