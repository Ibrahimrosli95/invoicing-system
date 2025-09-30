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
}
