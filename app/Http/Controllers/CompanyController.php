<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{

    /**
     * Show the company settings page.
     */
    public function show(): View
    {
        $company = auth()->user()->company;
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        return view('company.show', compact('company'));
    }

    /**
     * Show the form for editing the company.
     */
    public function edit(): View
    {
        $company = auth()->user()->company;
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        $timezones = [
            'Asia/Kuala_Lumpur' => 'Malaysia (UTC+8)',
            'Asia/Singapore' => 'Singapore (UTC+8)',
            'Asia/Jakarta' => 'Indonesia (UTC+7)',
            'Asia/Bangkok' => 'Thailand (UTC+7)',
            'UTC' => 'UTC (GMT+0)',
        ];

        $currencies = [
            'MYR' => 'Malaysian Ringgit (RM)',
            'USD' => 'US Dollar ($)',
            'SGD' => 'Singapore Dollar (S$)',
            'IDR' => 'Indonesian Rupiah (Rp)',
            'THB' => 'Thai Baht (฿)',
        ];

        $dateFormats = [
            'Y-m-d' => 'YYYY-MM-DD (2025-01-15)',
            'd/m/Y' => 'DD/MM/YYYY (15/01/2025)',
            'm/d/Y' => 'MM/DD/YYYY (01/15/2025)',
            'd-m-Y' => 'DD-MM-YYYY (15-01-2025)',
            'M j, Y' => 'Month DD, YYYY (Jan 15, 2025)',
        ];

        return view('company.edit', compact('company', 'timezones', 'currencies', 'dateFormats'));
    }

    /**
     * Update the company settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $company = auth()->user()->company;
        
        if (!$company) {
            abort(404, 'Company not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'registration_number' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'timezone' => 'required|string|max:50',
            'currency' => 'required|string|max:3',
            'date_format' => 'required|string|max:20',
            'logo' => 'nullable|file|max:2048', // Simplified validation to avoid fileinfo dependency
            'remove_logo' => 'nullable|boolean',
            
            // Additional settings
            'additional_addresses' => 'nullable|array',
            'additional_addresses.*.label' => 'required|string|max:100',
            'additional_addresses.*.address' => 'required|string|max:500',
            'additional_addresses.*.city' => 'nullable|string|max:100',
            'additional_addresses.*.state' => 'nullable|string|max:100',
            'additional_addresses.*.postal_code' => 'nullable|string|max:20',
            'additional_addresses.*.country' => 'nullable|string|max:100',
            
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.title' => 'nullable|string|max:255',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
            'contacts.*.department' => 'nullable|string|max:100',
            
            'social_media' => 'nullable|array',
            'social_media.facebook' => 'nullable|url|max:255',
            'social_media.twitter' => 'nullable|url|max:255',
            'social_media.linkedin' => 'nullable|url|max:255',
            'social_media.instagram' => 'nullable|url|max:255',
            'social_media.youtube' => 'nullable|url|max:255',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Manual extension check since fileinfo may not be available
            $file = $request->file('logo');
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];

            if (!in_array($extension, $allowedExtensions)) {
                return redirect()->back()
                    ->withErrors(['logo' => 'Logo must be an image file (jpg, jpeg, png, gif, svg)'])
                    ->withInput();
            }

            // Delete old logo if exists
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }

            // Store new logo
            $logoPath = $request->file('logo')->store('company-logos', 'public');
            $validated['logo_path'] = $logoPath;
        } elseif ($request->boolean('remove_logo')) {
            // Remove existing logo
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $validated['logo_path'] = null;
        }

        // Handle additional settings
        $additionalSettings = [
            'additional_addresses' => $validated['additional_addresses'] ?? [],
            'contacts' => $validated['contacts'] ?? [],
            'social_media' => $validated['social_media'] ?? [],
        ];
        
        // Remove additional settings from main validated data
        unset($validated['additional_addresses'], $validated['contacts'], $validated['social_media']);
        
        // Remove upload fields from validated data
        unset($validated['logo'], $validated['remove_logo']);

        // Update company settings
        $currentSettings = $company->settings ?? [];
        $updatedSettings = array_merge($currentSettings, $additionalSettings);
        $validated['settings'] = $updatedSettings;

        $company->update($validated);

        return redirect()->route('company.show')
            ->with('success', 'Company settings updated successfully.');
    }

    /**
     * Get company logo URL
     */
    public function logoUrl(): string
    {
        $company = auth()->user()->company;
        
        if ($company && $company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
            return Storage::disk('public')->url($company->logo_path);
        }

        // Return default logo or placeholder
        return asset('images/default-company-logo.png');
    }
}
