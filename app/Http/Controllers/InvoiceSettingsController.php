<?php

namespace App\Http\Controllers;

use App\Services\InvoiceSettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class InvoiceSettingsController extends Controller
{
    protected InvoiceSettingsService $settingsService;

    public function __construct(InvoiceSettingsService $settingsService)
    {
        $this->middleware('auth');
        $this->settingsService = $settingsService;
    }

    /**
     * Display invoice settings management page
     */
    public function index(): View
    {
        $this->authorize('manage settings');

        $settings = $this->settingsService->getSettings();

        return view('settings.invoice.index', compact('settings'));
    }

    /**
     * Get current invoice settings (API endpoint)
     */
    public function getSettings(Request $request): JsonResponse
    {
        $settings = $this->settingsService->getSettingsForAPI();

        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }

    /**
     * Update invoice settings
     */
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('manage settings');

        // Validate the settings
        $errors = $this->settingsService->validateSettings($request->all());

        if (!empty($errors)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $errors
                ], 422);
            }

            return back()->withErrors($errors);
        }

        // Update settings
        $success = $this->settingsService->updateSettings($request->all());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Settings updated successfully.' : 'Failed to update settings.',
                'settings' => $success ? $this->settingsService->getSettingsForAPI() : null
            ]);
        }

        if ($success) {
            return back()->with('success', 'Invoice settings updated successfully.');
        }

        return back()->with('error', 'Failed to update invoice settings.');
    }

    /**
     * Update optional sections configuration
     */
    public function updateOptionalSections(Request $request): JsonResponse
    {
        $request->validate([
            'sections' => 'required|array',
            'sections.show_shipping' => 'boolean',
            'sections.show_payment_instructions' => 'boolean',
            'sections.show_signatures' => 'boolean',
            'sections.show_additional_notes' => 'boolean',
            'sections.show_terms_conditions' => 'boolean',
        ]);

        $success = $this->settingsService->updateOptionalSections($request->sections);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Optional sections updated successfully.' : 'Failed to update optional sections.',
            'sections' => $success ? $this->settingsService->getOptionalSections() : null
        ]);
    }

    /**
     * Update logo settings
     */
    public function updateLogoSettings(Request $request): JsonResponse
    {
        $request->validate([
            'logo.show_company_logo' => 'boolean',
            'logo.logo_position' => 'in:left,center,right',
            'logo.logo_size' => 'in:small,medium,large',
        ]);

        $success = $this->settingsService->setSetting('logo', $request->logo);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Logo settings updated successfully.' : 'Failed to update logo settings.',
            'logo_settings' => $success ? $this->settingsService->getLogoSettings() : null
        ]);
    }

    /**
     * Update payment instructions
     */
    public function updatePaymentInstructions(Request $request): JsonResponse
    {
        $request->validate([
            'payment_instructions.bank_name' => 'nullable|string|max:100',
            'payment_instructions.account_number' => 'nullable|string|max:50',
            'payment_instructions.account_holder' => 'nullable|string|max:100',
            'payment_instructions.swift_code' => 'nullable|string|max:20',
            'payment_instructions.additional_info' => 'nullable|string|max:500',
        ]);

        $success = $this->settingsService->updatePaymentInstructions($request->payment_instructions);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Payment instructions updated successfully.' : 'Failed to update payment instructions.',
            'payment_instructions' => $success ? $this->settingsService->getPaymentInstructions() : null
        ]);
    }

    /**
     * Update default settings
     */
    public function updateDefaults(Request $request): JsonResponse
    {
        $request->validate([
            'defaults.payment_terms' => 'required|integer|min:1|max:365',
            'defaults.late_fee_percentage' => 'required|numeric|min:0|max:100',
            'defaults.currency' => 'required|string|max:5',
            'defaults.tax_percentage' => 'required|numeric|min:0|max:100',
            'defaults.show_discount_column' => 'boolean',
            'defaults.show_tax_column' => 'boolean',
        ]);

        $success = $this->settingsService->setSetting('defaults', $request->defaults);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Default settings updated successfully.' : 'Failed to update default settings.',
            'defaults' => $success ? $this->settingsService->getSetting('defaults') : null
        ]);
    }

    /**
     * Update content settings (terms, notes, signatures)
     */
    public function updateContent(Request $request): JsonResponse
    {
        $request->validate([
            'content.default_terms' => 'nullable|string|max:2000',
            'content.default_notes' => 'nullable|string|max:1000',
            'content.signature_blocks.show_company_signature' => 'boolean',
            'content.signature_blocks.show_client_signature' => 'boolean',
            'content.signature_blocks.company_signature_title' => 'nullable|string|max:100',
            'content.signature_blocks.client_signature_title' => 'nullable|string|max:100',
        ]);

        $success = $this->settingsService->setSetting('content', $request->content);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Content settings updated successfully.' : 'Failed to update content settings.',
            'content' => $success ? $this->settingsService->getSetting('content') : null
        ]);
    }

    /**
     * Reset settings to defaults
     */
    public function resetToDefaults(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('manage settings');

        $success = $this->settingsService->resetToDefaults();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Settings reset to defaults successfully.' : 'Failed to reset settings.',
                'settings' => $success ? $this->settingsService->getSettingsForAPI() : null
            ]);
        }

        if ($success) {
            return back()->with('success', 'Invoice settings reset to defaults successfully.');
        }

        return back()->with('error', 'Failed to reset invoice settings.');
    }

    /**
     * Get settings for invoice builder
     */
    public function getForBuilder(Request $request): JsonResponse
    {
        $settings = $this->settingsService->getSettingsForAPI();

        return response()->json([
            'success' => true,
            'settings' => $settings,
            'optional_sections' => $settings['optional_sections'],
            'defaults' => $settings['defaults']
        ]);
    }

    /**
     * Preview settings (for testing changes before saving)
     */
    public function preview(Request $request): JsonResponse
    {
        // Validate the preview settings
        $errors = $this->settingsService->validateSettings($request->all());

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'errors' => $errors
            ], 422);
        }

        // Merge preview settings with current settings
        $currentSettings = $this->settingsService->getSettings();
        $previewSettings = array_merge_recursive($currentSettings, $request->all());

        return response()->json([
            'success' => true,
            'preview_settings' => $previewSettings,
            'message' => 'Settings preview generated successfully.'
        ]);
    }

    /**
     * Generate preview PDF with current/test settings
     */
    public function previewPDF(Request $request)
    {
        $this->authorize('manage settings');

        // Get settings from request - may be JSON string or array
        $settings = $request->input('settings', []);
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }

        // Create mock invoice data for preview
        $mockInvoice = $this->createMockInvoice($settings);

        // Use PDF renderer with preview settings
        $pdfRenderer = app(\App\Services\InvoicePdfRenderer::class);

        return $pdfRenderer->inlineResponse($mockInvoice);
    }

    /**
     * Get columns configuration
     */
    public function getColumns(Request $request): JsonResponse
    {
        $columns = $this->settingsService->getColumns();

        return response()->json([
            'success' => true,
            'columns' => $columns
        ]);
    }

    /**
     * Update columns configuration
     */
    public function updateColumns(Request $request): JsonResponse
    {
        $request->validate([
            'columns' => 'required|array',
            'columns.*.key' => 'required|string',
            'columns.*.label' => 'required|string|max:50',
            'columns.*.visible' => 'required|boolean',
            'columns.*.order' => 'required|integer|min:1',
        ]);

        $success = $this->settingsService->updateColumns($request->columns);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Columns updated successfully.' : 'Failed to update columns.',
            'columns' => $success ? $this->settingsService->getColumns() : null
        ]);
    }

    /**
     * Update appearance/palette settings
     */
    public function updateAppearance(Request $request): JsonResponse
    {
        // Validate that appearance is an array
        $validated = $request->validate([
            'appearance' => 'required|array',
        ]);

        // Manual validation for color fields to avoid regex delimiter issues
        $colorFields = [
            'background_color', 'border_color', 'heading_color', 'text_color',
            'muted_text_color', 'accent_color', 'accent_text_color',
            'table_header_background', 'table_header_text', 'table_row_even'
        ];

        $appearance = $request->appearance;
        $errors = [];

        foreach ($colorFields as $field) {
            if (isset($appearance[$field])) {
                $color = $appearance[$field];

                // Check if it's a string and matches hex format
                if (!is_string($color)) {
                    $errors["appearance.{$field}"] = "The appearance.{$field} must be a string.";
                } elseif (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
                    $errors["appearance.{$field}"] = "The appearance.{$field} must be a valid hex color.";
                }
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $errors
            ], 422);
        }

        $success = $this->settingsService->setSetting('appearance', $appearance);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Appearance updated successfully.' : 'Failed to update appearance.',
            'appearance' => $success ? $this->settingsService->getAppearance() : null
        ]);
    }

    /**
     * Create mock invoice for PDF preview
     */
    private function createMockInvoice(array $settingsOverride = []): object
    {
        $company = auth()->user()->company;

        // Create a mock invoice object with necessary properties
        $mockInvoice = new \stdClass();
        $mockInvoice->id = 0;
        $mockInvoice->number = 'PREVIEW-001';
        $mockInvoice->company_id = $company->id;
        $mockInvoice->company = $company;
        $mockInvoice->issued_date = now();
        $mockInvoice->due_date = now()->addDays(30);
        $mockInvoice->payment_terms = 30;
        $mockInvoice->customer_name = 'Sample Customer';
        $mockInvoice->customer_company = 'Sample Company Ltd.';
        $mockInvoice->customer_email = 'customer@example.com';
        $mockInvoice->customer_phone = '+60123456789';
        $mockInvoice->customer_address = '123 Sample Street';
        $mockInvoice->customer_city = 'Kuala Lumpur';
        $mockInvoice->customer_state = 'Wilayah Persekutuan';
        $mockInvoice->customer_postal_code = '50000';
        $mockInvoice->payment_instructions = '';
        $mockInvoice->optional_sections = $settingsOverride['sections'] ?? null;
        $mockInvoice->subtotal = 1000.00;
        $mockInvoice->discount_amount = 50.00;
        $mockInvoice->tax_amount = 57.00;
        $mockInvoice->total = 1007.00;
        $mockInvoice->amount_paid = 0.00;

        // Mock items
        $item1 = new \stdClass();
        $item1->description = 'Sample Product 1';
        $item1->quantity = 2;
        $item1->unit_price = 250.00;
        $item1->total_price = 500.00;

        $item2 = new \stdClass();
        $item2->description = 'Sample Product 2';
        $item2->quantity = 5;
        $item2->unit_price = 100.00;
        $item2->total_price = 500.00;

        $mockInvoice->items = collect([$item1, $item2]);
        $mockInvoice->paymentRecords = collect([]);
        $mockInvoice->createdBy = auth()->user();

        return $mockInvoice;
    }
}
