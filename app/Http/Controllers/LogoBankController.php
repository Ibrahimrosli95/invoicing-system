<?php

namespace App\Http\Controllers;

use App\Models\CompanyLogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class LogoBankController extends Controller
{
    /**
     * Display all logos in the bank.
     */
    public function index()
    {
        $logos = auth()->user()->company->logos()->latest()->get();

        return view('logo-bank.index', compact('logos'));
    }

    /**
     * Store a new logo in the bank.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|file|max:2048',
            'notes' => 'nullable|string|max:500',
            'set_as_default' => 'nullable|boolean',
        ]);

        try {
            // Manual extension check (avoiding fileinfo dependency)
            $file = $request->file('logo');
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];

            if (!in_array($extension, $allowedExtensions)) {
                return redirect()->back()
                    ->withErrors(['logo' => 'Logo must be an image file (jpg, jpeg, png, gif, svg)'])
                    ->withInput();
            }

            // Store the logo file
            $logoPath = $file->store('company-logos', 'public');

            // Create logo record
            $logo = auth()->user()->company->logos()->create([
                'name' => $request->name,
                'file_path' => $logoPath,
                'notes' => $request->notes,
                'is_default' => false,
            ]);

            // Set as default if requested
            if ($request->boolean('set_as_default')) {
                $logo->setAsDefault();
            }

            return redirect()->back()->with('success', 'Logo added to bank successfully.');

        } catch (\Exception $e) {
            \Log::error('Logo upload failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Failed to upload logo: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Set a logo as the default.
     */
    public function setDefault(CompanyLogo $logo): RedirectResponse
    {
        // Ensure logo belongs to user's company
        if ($logo->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        $logo->setAsDefault();

        return redirect()->back()->with('success', 'Default logo updated successfully.');
    }

    /**
     * Delete a logo from the bank.
     */
    public function destroy(CompanyLogo $logo): RedirectResponse
    {
        // Ensure logo belongs to user's company
        if ($logo->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized');
        }

        // Prevent deleting the default logo if it's the only one
        if ($logo->is_default && auth()->user()->company->logos()->count() === 1) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete the only logo. Upload another logo first.']);
        }

        // If deleting default logo, set another as default
        if ($logo->is_default) {
            $nextLogo = auth()->user()->company->logos()->where('id', '!=', $logo->id)->first();
            if ($nextLogo) {
                $nextLogo->setAsDefault();
            }
        }

        $logo->delete();

        return redirect()->back()->with('success', 'Logo deleted successfully.');
    }

    /**
     * Serve a logo file.
     */
    public function serve(CompanyLogo $logo): Response
    {
        // Ensure logo belongs to user's company
        if ($logo->company_id !== auth()->user()->company_id) {
            \Log::warning('Logo access denied', [
                'logo_id' => $logo->id,
                'logo_company_id' => $logo->company_id,
                'user_company_id' => auth()->user()->company_id,
            ]);
            abort(403, 'Unauthorized');
        }

        $path = Storage::disk('public')->path($logo->file_path);

        \Log::info('Logo serve attempt', [
            'logo_id' => $logo->id,
            'file_path' => $logo->file_path,
            'full_path' => $path,
            'exists' => file_exists($path),
            'storage_path' => storage_path('app/public'),
        ]);

        if (!file_exists($path)) {
            \Log::error('Logo file not found', [
                'logo_id' => $logo->id,
                'expected_path' => $path,
                'file_path' => $logo->file_path,
            ]);
            abort(404, 'Logo file not found');
        }

        // Determine MIME type from extension (avoiding fileinfo dependency)
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
        ];
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        // Add Last-Modified header based on file modification time
        $lastModified = filemtime($path);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=3600',
            'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
        ]);
    }

    /**
     * Get logos for AJAX requests (used in invoice builder).
     */
    public function getLogos()
    {
        $logos = auth()->user()->company->logos()->get()->map(function ($logo) {
            $url = route('logo-bank.serve', $logo->id) . '?v=' . $logo->updated_at->timestamp;

            \Log::info('Logo URL generated', [
                'logo_id' => $logo->id,
                'url' => $url,
                'file_path' => $logo->file_path,
            ]);

            return [
                'id' => $logo->id,
                'name' => $logo->name,
                'url' => $url,
                'is_default' => $logo->is_default,
                'notes' => $logo->notes,
            ];
        });

        \Log::info('GetLogos response', [
            'count' => $logos->count(),
            'company_id' => auth()->user()->company_id,
        ]);

        return response()->json(['logos' => $logos]);
    }
}
