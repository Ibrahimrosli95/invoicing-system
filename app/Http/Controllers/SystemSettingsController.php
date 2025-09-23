<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{

    /**
     * Display the system settings page.
     */
    public function index(): View
    {
        $company = auth()->user()->company;
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        // Get current system settings
        $systemSettings = $company->settings['system'] ?? $this->getDefaultSystemSettings();

        return view('settings.system.index', compact('company', 'systemSettings'));
    }

    /**
     * Update system settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $company = auth()->user()->company;
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        $validated = $request->validate([
            // Email Configuration
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|in:tls,ssl,none',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            
            // Notification Settings
            'email_notifications_enabled' => 'boolean',
            'sms_notifications_enabled' => 'boolean',
            'push_notifications_enabled' => 'boolean',
            'notification_frequency' => 'nullable|string|in:instant,hourly,daily,weekly',
            
            // Security Settings
            'session_timeout_minutes' => 'nullable|integer|min:5|max:1440',
            'password_expiry_days' => 'nullable|integer|min:0|max:365',
            'max_login_attempts' => 'nullable|integer|min:1|max:20',
            'require_2fa' => 'boolean',
            'allowed_ip_addresses' => 'nullable|string|max:1000',
            
            // Backup Settings
            'auto_backup_enabled' => 'boolean',
            'backup_frequency' => 'nullable|string|in:daily,weekly,monthly',
            'backup_retention_days' => 'nullable|integer|min:1|max:365',
            'backup_storage' => 'nullable|string|in:local,s3,google,azure',
            
            // Integration Settings
            'api_rate_limit' => 'nullable|integer|min:1|max:10000',
            'webhook_timeout' => 'nullable|integer|min:5|max:300',
            'webhook_retry_attempts' => 'nullable|integer|min:0|max:10',
            
            // Performance Settings
            'cache_enabled' => 'boolean',
            'cache_ttl_minutes' => 'nullable|integer|min:1|max:1440',
            'query_logging_enabled' => 'boolean',
            'debug_mode_enabled' => 'boolean',
            
            // Business Settings
            'default_quotation_validity_days' => 'nullable|integer|min:1|max:365',
            'default_invoice_due_days' => 'nullable|integer|min:1|max:365',
            'automatic_invoice_generation' => 'boolean',
            'automatic_payment_reminders' => 'boolean',
            'late_fee_calculation' => 'boolean',
            
            // Customization
            'custom_css' => 'nullable|string|max:10000',
            'custom_javascript' => 'nullable|string|max:10000',
            'maintenance_mode' => 'boolean',
            'maintenance_message' => 'nullable|string|max:1000',
        ]);

        // Update system settings
        $settings = $company->settings ?? [];
        $settings['system'] = array_merge($settings['system'] ?? [], $validated);
        $company->update(['settings' => $settings]);

        return redirect()->route('settings.system.index')
            ->with('success', 'System settings updated successfully.');
    }

    /**
     * Test email configuration.
     */
    public function testEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'test_email' => 'required|email|max:255',
        ]);

        try {
            // Use current email settings to send test email
            $company = auth()->user()->company;
            $systemSettings = $company->settings['system'] ?? [];

            // Configure mail settings temporarily for testing
            if (!empty($systemSettings['smtp_host'])) {
                config([
                    'mail.mailers.smtp.host' => $systemSettings['smtp_host'],
                    'mail.mailers.smtp.port' => $systemSettings['smtp_port'] ?? 587,
                    'mail.mailers.smtp.username' => $systemSettings['smtp_username'],
                    'mail.mailers.smtp.password' => $systemSettings['smtp_password'],
                    'mail.mailers.smtp.encryption' => $systemSettings['smtp_encryption'] ?? 'tls',
                    'mail.from.address' => $systemSettings['mail_from_address'] ?? config('mail.from.address'),
                    'mail.from.name' => $systemSettings['mail_from_name'] ?? config('mail.from.name'),
                ]);
            }

            // Send test email
            \Mail::raw('This is a test email from your Sales System. If you received this, your email configuration is working correctly.', function ($message) use ($validated, $company) {
                $message->to($validated['test_email'])
                       ->subject('Test Email from ' . $company->name);
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to ' . $validated['test_email'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Clear application cache.
     */
    public function clearCache(): RedirectResponse
    {
        try {
            // Clear various caches
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');

            return redirect()->route('settings.system.index')
                ->with('success', 'Application cache cleared successfully.');

        } catch (\Exception $e) {
            return redirect()->route('settings.system.index')
                ->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Export system settings as JSON.
     */
    public function export(): \Illuminate\Http\JsonResponse
    {
        $company = auth()->user()->company;
        $systemSettings = $company->settings['system'] ?? [];

        // Remove sensitive information from export
        $exportSettings = $systemSettings;
        unset($exportSettings['smtp_password'], $exportSettings['custom_javascript']);

        return response()->json([
            'company_name' => $company->name,
            'exported_at' => now()->toISOString(),
            'system_settings' => $exportSettings,
        ]);
    }

    /**
     * Import system settings from JSON.
     */
    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'import_file' => 'required|file|mimes:json|max:1024',
        ]);

        try {
            $content = file_get_contents($validated['import_file']->path());
            $data = json_decode($content, true);

            if (!$data || !isset($data['system_settings'])) {
                throw new \Exception('Invalid import file format');
            }

            $company = auth()->user()->company;
            $settings = $company->settings ?? [];
            
            // Merge imported settings (excluding sensitive data)
            $importedSettings = $data['system_settings'];
            unset($importedSettings['smtp_password'], $importedSettings['custom_javascript']);
            
            $settings['system'] = array_merge($settings['system'] ?? [], $importedSettings);
            $company->update(['settings' => $settings]);

            return redirect()->route('settings.system.index')
                ->with('success', 'System settings imported successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['import_file' => 'Failed to import: ' . $e->getMessage()]);
        }
    }

    /**
     * Reset system settings to defaults.
     */
    public function resetToDefaults(): RedirectResponse
    {
        $company = auth()->user()->company;
        $settings = $company->settings ?? [];
        $settings['system'] = $this->getDefaultSystemSettings();
        $company->update(['settings' => $settings]);

        return redirect()->route('settings.system.index')
            ->with('success', 'System settings reset to defaults successfully.');
    }

    /**
     * Get default system settings.
     */
    private function getDefaultSystemSettings(): array
    {
        return [
            // Email Configuration
            'smtp_host' => null,
            'smtp_port' => 587,
            'smtp_username' => null,
            'smtp_password' => null,
            'smtp_encryption' => 'tls',
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            
            // Notification Settings
            'email_notifications_enabled' => true,
            'sms_notifications_enabled' => false,
            'push_notifications_enabled' => true,
            'notification_frequency' => 'instant',
            
            // Security Settings
            'session_timeout_minutes' => 120,
            'password_expiry_days' => 0,
            'max_login_attempts' => 5,
            'require_2fa' => false,
            'allowed_ip_addresses' => null,
            
            // Backup Settings
            'auto_backup_enabled' => false,
            'backup_frequency' => 'weekly',
            'backup_retention_days' => 30,
            'backup_storage' => 'local',
            
            // Integration Settings
            'api_rate_limit' => 1000,
            'webhook_timeout' => 30,
            'webhook_retry_attempts' => 3,
            
            // Performance Settings
            'cache_enabled' => true,
            'cache_ttl_minutes' => 60,
            'query_logging_enabled' => false,
            'debug_mode_enabled' => false,
            
            // Business Settings
            'default_quotation_validity_days' => 30,
            'default_invoice_due_days' => 30,
            'automatic_invoice_generation' => false,
            'automatic_payment_reminders' => true,
            'late_fee_calculation' => true,
            
            // Customization
            'custom_css' => null,
            'custom_javascript' => null,
            'maintenance_mode' => false,
            'maintenance_message' => 'System is under maintenance. Please try again later.',
        ];
    }
}