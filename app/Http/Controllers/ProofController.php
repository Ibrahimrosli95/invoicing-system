<?php

namespace App\Http\Controllers;

use App\Models\Proof;
use App\Models\ProofAsset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProofController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of proofs with filtering and search
     */
    public function index(Request $request): View
    {
        $query = Proof::forCompany()
                     ->with(['assets', 'creator', 'views'])
                     ->withCount('assets');

        // Apply filters
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('visibility')) {
            $query->byVisibility($request->visibility);
        }

        if ($request->filled('featured')) {
            $query->featured();
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        
        if (in_array($sortBy, ['created_at', 'title', 'view_count', 'conversion_impact', 'sort_order'])) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $proofs = $query->paginate(20);

        // Get statistics for dashboard
        $stats = [
            'total' => Proof::forCompany()->count(),
            'active' => Proof::forCompany()->active()->count(),
            'draft' => Proof::forCompany()->where('status', 'draft')->count(),
            'featured' => Proof::forCompany()->featured()->count(),
            'total_views' => Proof::forCompany()->sum('view_count'),
            'total_clicks' => Proof::forCompany()->sum('click_count'),
        ];

        return view('proofs.index', compact('proofs', 'stats'));
    }

    /**
     * Show the form for creating a new proof
     */
    public function create(Request $request): View
    {
        // Get scope context if provided
        $scopeType = $request->get('scope_type');
        $scopeId = $request->get('scope_id');
        $scopeModel = null;

        if ($scopeType && $scopeId) {
            $modelClass = 'App\\Models\\' . ucfirst($scopeType);
            if (class_exists($modelClass)) {
                $scopeModel = $modelClass::find($scopeId);
            }
        }

        return view('proofs.create', compact('scopeModel', 'scopeType', 'scopeId'));
    }

    /**
     * Store a newly created proof
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(Proof::TYPES))],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scope_type' => 'nullable|string',
            'scope_id' => 'nullable|integer',
            'visibility' => ['required', Rule::in(array_keys(Proof::VISIBILITY_LEVELS))],
            'show_in_pdf' => 'boolean',
            'show_in_quotation' => 'boolean',
            'show_in_invoice' => 'boolean',
            'is_featured' => 'boolean',
            'expires_at' => 'nullable|date|after:today',
            'metadata' => 'nullable|array',
            'assets' => 'nullable|array',
            'assets.*' => 'file|max:10240', // 10MB max per file
        ]);

        DB::transaction(function () use ($validated, $request, &$proof) {
            $proof = Proof::create($validated);

            // Handle file uploads
            if ($request->hasFile('assets')) {
                foreach ($request->file('assets') as $index => $file) {
                    $asset = ProofAsset::createFromUpload($file, $proof, [
                        'sort_order' => $index,
                        'is_primary' => $index === 0, // First asset is primary
                        'title' => $request->input("asset_titles.{$index}"),
                        'description' => $request->input("asset_descriptions.{$index}"),
                        'alt_text' => $request->input("asset_alt_texts.{$index}"),
                    ]);

                    // Process image dimensions if it's an image
                    if ($asset->isImage()) {
                        $asset->extractImageDimensions();
                        $asset->generateThumbnail();
                    }
                }
            }

            // Auto-publish if requested
            if ($request->boolean('publish_now')) {
                $proof->publish();
            }
        });

        return redirect()
            ->route('proofs.show', $proof->uuid)
            ->with('success', 'Proof created successfully.');
    }

    /**
     * Display the specified proof
     */
    public function show(string $uuid): View
    {
        $proof = Proof::where('uuid', $uuid)
                     ->forCompany()
                     ->with(['assets', 'creator', 'scope', 'views' => function ($query) {
                         $query->latest()->limit(10);
                     }])
                     ->firstOrFail();

        // Record view if proof is viewable
        if ($proof->canBeViewed()) {
            $proof->recordView([
                'source' => 'web_interface',
            ]);
        }

        // Get engagement statistics
        $engagementStats = [
            'views_today' => $proof->getViewsInPeriod(1),
            'views_week' => $proof->getViewsInPeriod(7),
            'views_month' => $proof->getViewsInPeriod(30),
            'unique_views_month' => $proof->getUniqueViewsInPeriod(30),
            'engagement_rate' => $proof->getEngagementRate(),
        ];

        return view('proofs.show', compact('proof', 'engagementStats'));
    }

    /**
     * Show the form for editing the specified proof
     */
    public function edit(string $uuid): View
    {
        $proof = Proof::where('uuid', $uuid)
                     ->forCompany()
                     ->with('assets')
                     ->firstOrFail();

        return view('proofs.edit', compact('proof'));
    }

    /**
     * Update the specified proof
     */
    public function update(Request $request, string $uuid): RedirectResponse
    {
        $proof = Proof::where('uuid', $uuid)
                     ->forCompany()
                     ->firstOrFail();

        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(Proof::TYPES))],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'visibility' => ['required', Rule::in(array_keys(Proof::VISIBILITY_LEVELS))],
            'show_in_pdf' => 'boolean',
            'show_in_quotation' => 'boolean',
            'show_in_invoice' => 'boolean',
            'is_featured' => 'boolean',
            'expires_at' => 'nullable|date|after:today',
            'metadata' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
            'conversion_impact' => 'nullable|numeric|between:0,100',
        ]);

        $proof->update($validated);

        return redirect()
            ->route('proofs.show', $proof->uuid)
            ->with('success', 'Proof updated successfully.');
    }

    /**
     * Remove the specified proof
     */
    public function destroy(string $uuid): RedirectResponse
    {
        $proof = Proof::where('uuid', $uuid)
                     ->forCompany()
                     ->firstOrFail();

        DB::transaction(function () use ($proof) {
            // Delete all assets and their files
            foreach ($proof->assets as $asset) {
                $asset->delete(); // This will trigger the file deletion
            }
            
            $proof->delete();
        });

        return redirect()
            ->route('proofs.index')
            ->with('success', 'Proof deleted successfully.');
    }

    /**
     * Duplicate a proof
     */
    public function duplicate(string $uuid): RedirectResponse
    {
        $proof = Proof::where('uuid', $uuid)
                     ->forCompany()
                     ->firstOrFail();

        $duplicate = $proof->duplicate();

        return redirect()
            ->route('proofs.edit', $duplicate->uuid)
            ->with('success', 'Proof duplicated successfully.');
    }

    /**
     * Publish/unpublish a proof
     */
    public function toggleStatus(Request $request, string $uuid): JsonResponse
    {
        $proof = Proof::where('uuid', $uuid)
                     ->forCompany()
                     ->firstOrFail();

        $newStatus = $request->input('status');

        if ($newStatus === 'active' && $proof->status !== 'active') {
            $proof->publish();
        } else {
            $proof->update(['status' => $newStatus]);
        }

        return response()->json([
            'success' => true,
            'status' => $proof->status,
            'message' => 'Proof status updated successfully.',
        ]);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(string $uuid): JsonResponse
    {
        $proof = Proof::where('uuid', $uuid)
                     ->forCompany()
                     ->firstOrFail();

        if ($proof->is_featured) {
            $proof->unmarkAsFeatured();
        } else {
            $proof->markAsFeatured();
        }

        return response()->json([
            'success' => true,
            'is_featured' => $proof->is_featured,
            'message' => 'Featured status updated successfully.',
        ]);
    }

    /**
     * Upload additional assets to existing proof
     */
    public function uploadAssets(Request $request, string $uuid): JsonResponse
    {
        $proof = Proof::where('uuid', $uuid)
                     ->forCompany()
                     ->firstOrFail();

        $request->validate([
            'assets' => 'required|array',
            'assets.*' => 'file|max:10240', // 10MB max per file
        ]);

        $uploadedAssets = [];

        DB::transaction(function () use ($request, $proof, &$uploadedAssets) {
            $maxSortOrder = $proof->assets()->max('sort_order') ?? 0;

            foreach ($request->file('assets') as $index => $file) {
                $asset = ProofAsset::createFromUpload($file, $proof, [
                    'sort_order' => $maxSortOrder + $index + 1,
                    'title' => $request->input("asset_titles.{$index}"),
                    'description' => $request->input("asset_descriptions.{$index}"),
                    'alt_text' => $request->input("asset_alt_texts.{$index}"),
                ]);

                // Process image dimensions if it's an image
                if ($asset->isImage()) {
                    $asset->extractImageDimensions();
                    $asset->generateThumbnail();
                }

                $uploadedAssets[] = $asset->toApiArray();
            }
        });

        return response()->json([
            'success' => true,
            'assets' => $uploadedAssets,
            'message' => count($uploadedAssets) . ' assets uploaded successfully.',
        ]);
    }

    /**
     * Get proofs for a specific scope (AJAX)
     */
    public function getForScope(Request $request): JsonResponse
    {
        $request->validate([
            'scope_type' => 'required|string',
            'scope_id' => 'required|integer',
            'type' => 'nullable|string',
        ]);

        $query = Proof::forCompany()
                     ->where('scope_type', $request->scope_type)
                     ->where('scope_id', $request->scope_id)
                     ->active()
                     ->published()
                     ->notExpired();

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        $proofs = $query->with('assets')->get();

        return response()->json([
            'success' => true,
            'proofs' => $proofs->map->toApiArray(),
        ]);
    }

    /**
     * Get analytics data for proof management
     */
    public function analytics(): JsonResponse
    {
        $stats = [
            'overview' => [
                'total_proofs' => Proof::forCompany()->count(),
                'active_proofs' => Proof::forCompany()->active()->count(),
                'total_views' => Proof::forCompany()->sum('view_count'),
                'total_clicks' => Proof::forCompany()->sum('click_count'),
                'avg_engagement_rate' => Proof::forCompany()->avg('conversion_impact'),
            ],
            'by_type' => Proof::forCompany()
                             ->selectRaw('type, COUNT(*) as count, SUM(view_count) as total_views')
                             ->groupBy('type')
                             ->get()
                             ->mapWithKeys(function ($item) {
                                 return [Proof::TYPES[$item->type] => [
                                     'count' => $item->count,
                                     'views' => $item->total_views,
                                 ]];
                             }),
            'top_performing' => Proof::forCompany()
                                    ->active()
                                    ->orderBy('view_count', 'desc')
                                    ->limit(5)
                                    ->get(['uuid', 'title', 'view_count', 'click_count'])
                                    ->map->toApiArray(),
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }

    /**
     * Show proof pack generator form
     */
    public function proofPackForm(Request $request): View
    {
        $proofs = Proof::forCompany()
                      ->with(['assets'])
                      ->active()
                      ->get()
                      ->groupBy('type');

        $analytics = app(\App\Services\PDFService::class)->getProofAnalytics(
            Proof::forCompany()->active()->get()
        );

        return view('proofs.proof-pack', compact('proofs', 'analytics'));
    }

    /**
     * Generate and download proof pack PDF
     */
    public function generateProofPack(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'proof_ids' => 'required|array|min:1',
            'proof_ids.*' => 'exists:proofs,uuid',
            'orientation' => 'in:portrait,landscape',
            'show_analytics' => 'boolean',
            'watermark' => 'nullable|string|max:50',
        ]);

        // Get selected proofs
        $proofs = Proof::forCompany()
                      ->with(['assets' => function ($query) {
                          $query->where('processing_status', 'completed')
                                ->where('show_in_gallery', true)
                                ->orderBy('is_primary', 'desc')
                                ->orderBy('sort_order');
                      }, 'company'])
                      ->whereIn('uuid', $request->proof_ids)
                      ->active()
                      ->orderBy('is_featured', 'desc')
                      ->orderBy('sort_order')
                      ->get();

        if ($proofs->isEmpty()) {
            return back()->with('error', 'No valid proofs selected for PDF generation.');
        }

        // Generate proof pack PDF
        $pdfService = app(\App\Services\PDFService::class);
        
        $options = [
            'title' => $request->title,
            'company_id' => auth()->user()->company_id,
            'orientation' => $request->orientation ?? 'portrait',
            'show_analytics' => $request->boolean('show_analytics'),
            'watermark' => $request->watermark,
        ];

        try {
            $pdfPath = $pdfService->generateProofPackPDF($proofs, $options);
            
            // Generate download filename
            $filename = \Illuminate\Support\Str::slug($request->title) . '_proof_pack.pdf';
            
            return response()->download(Storage::path($pdfPath), $filename, [
                'Content-Type' => 'application/pdf'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Proof pack PDF generation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate proof pack PDF. Please try again.');
        }
    }

    /**
     * Preview proof pack PDF in browser
     */
    public function previewProofPack(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'proof_ids' => 'required|array|min:1',
            'proof_ids.*' => 'exists:proofs,uuid',
            'orientation' => 'in:portrait,landscape',
            'show_analytics' => 'boolean',
            'watermark' => 'nullable|string|max:50',
        ]);

        // Get selected proofs
        $proofs = Proof::forCompany()
                      ->with(['assets' => function ($query) {
                          $query->where('processing_status', 'completed')
                                ->where('show_in_gallery', true)
                                ->orderBy('is_primary', 'desc')
                                ->orderBy('sort_order');
                      }, 'company'])
                      ->whereIn('uuid', $request->proof_ids)
                      ->active()
                      ->orderBy('is_featured', 'desc')
                      ->orderBy('sort_order')
                      ->get();

        if ($proofs->isEmpty()) {
            return back()->with('error', 'No valid proofs selected for PDF generation.');
        }

        // Generate proof pack PDF
        $pdfService = app(\App\Services\PDFService::class);
        
        $options = [
            'title' => $request->title,
            'company_id' => auth()->user()->company_id,
            'orientation' => $request->orientation ?? 'portrait',
            'show_analytics' => $request->boolean('show_analytics'),
            'watermark' => $request->watermark,
        ];

        try {
            $pdfPath = $pdfService->generateProofPackPDF($proofs, $options);
            
            return response()->file(Storage::path($pdfPath), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . \Illuminate\Support\Str::slug($request->title) . '_proof_pack.pdf"'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Proof pack PDF preview failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate proof pack PDF preview. Please try again.');
        }
    }

    /**
     * Generate a secure signed URL for proof pack sharing
     */
    public function generateSecureShareUrl(Request $request): JsonResponse
    {
        $request->validate([
            'proof_ids' => 'required|array|min:1',
            'proof_ids.*' => 'exists:proofs,uuid',
            'expires_in' => 'integer|min:1|max:168', // Max 7 days
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'recipient_email' => 'nullable|email',
            'recipient_name' => 'nullable|string|max:255',
        ]);

        try {
            // Verify user can access all requested proofs
            $proofs = Proof::forCompany()
                ->whereIn('uuid', $request->proof_ids)
                ->where('status', 'active')
                ->get();

            if ($proofs->count() !== count($request->proof_ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more proofs not found or not accessible.'
                ], 403);
            }

            // Generate secure sharing token
            $sharingData = [
                'proof_ids' => $request->proof_ids,
                'title' => $request->title,
                'description' => $request->description,
                'company_id' => auth()->user()->company_id,
                'created_by' => auth()->id(),
                'recipient_email' => $request->recipient_email,
                'recipient_name' => $request->recipient_name,
                'created_at' => now()->toIso8601String(),
                'expires_at' => now()->addHours($request->expires_in ?? 24)->toIso8601String(),
            ];

            $token = encrypt($sharingData);
            $shareUrl = route('proofs.shared-pack', ['token' => $token]);

            // Log the sharing activity
            \Log::info('Proof pack sharing URL generated', [
                'company_id' => auth()->user()->company_id,
                'user_id' => auth()->id(),
                'proof_count' => $proofs->count(),
                'expires_in_hours' => $request->expires_in ?? 24,
                'recipient_email' => $request->recipient_email,
            ]);

            return response()->json([
                'success' => true,
                'share_url' => $shareUrl,
                'expires_at' => $sharingData['expires_at'],
                'proof_count' => $proofs->count(),
                'title' => $request->title,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to generate secure share URL: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate sharing URL. Please try again.'
            ], 500);
        }
    }

    /**
     * Display shared proof pack without authentication
     */
    public function sharedProofPack(string $token): View|RedirectResponse
    {
        try {
            $sharingData = decrypt($token);
            
            // Check if sharing token has expired
            $expiresAt = \Carbon\Carbon::parse($sharingData['expires_at']);
            if ($expiresAt->isPast()) {
                return view('proofs.shared.expired');
            }

            // Fetch the proofs
            $proofs = Proof::where('company_id', $sharingData['company_id'])
                ->whereIn('uuid', $sharingData['proof_ids'])
                ->where('status', 'active')
                ->with(['assets' => function ($query) {
                    $query->where('status', 'processed')
                        ->orderBy('display_order');
                }])
                ->get();

            if ($proofs->isEmpty()) {
                return view('proofs.shared.not-found');
            }

            // Track the view
            $this->trackSharedView($sharingData, request()->ip(), request()->userAgent());

            return view('proofs.shared.pack', [
                'proofs' => $proofs,
                'title' => $sharingData['title'],
                'description' => $sharingData['description'] ?? null,
                'recipient_name' => $sharingData['recipient_name'] ?? null,
                'company_id' => $sharingData['company_id'],
                'expires_at' => $expiresAt,
                'created_at' => \Carbon\Carbon::parse($sharingData['created_at']),
            ]);

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return view('proofs.shared.invalid');
        } catch (\Exception $e) {
            \Log::error('Failed to display shared proof pack: ' . $e->getMessage());
            return view('proofs.shared.error');
        }
    }

    /**
     * Download shared proof pack PDF without authentication
     */
    public function downloadSharedProofPack(string $token): \Symfony\Component\HttpFoundation\BinaryFileResponse|RedirectResponse
    {
        try {
            $sharingData = decrypt($token);
            
            // Check if sharing token has expired
            $expiresAt = \Carbon\Carbon::parse($sharingData['expires_at']);
            if ($expiresAt->isPast()) {
                abort(410, 'Shared link has expired');
            }

            // Fetch the proofs
            $proofs = Proof::where('company_id', $sharingData['company_id'])
                ->whereIn('uuid', $sharingData['proof_ids'])
                ->where('status', 'active')
                ->with(['assets' => function ($query) {
                    $query->where('status', 'processed')
                        ->orderBy('display_order');
                }])
                ->get();

            if ($proofs->isEmpty()) {
                abort(404, 'Proof pack not found');
            }

            // Generate PDF
            $pdfService = app(\App\Services\PDFService::class);
            $options = [
                'title' => $sharingData['title'],
                'description' => $sharingData['description'],
                'show_company_info' => true,
                'include_analytics' => false, // No analytics for external sharing
                'watermark' => 'SHARED COPY',
            ];

            $pdfPath = $pdfService->generateProofPackPDF($proofs, $options);
            
            // Track the download
            $this->trackSharedDownload($sharingData, request()->ip(), request()->userAgent());
            
            return response()->file(Storage::path($pdfPath), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . \Illuminate\Support\Str::slug($sharingData['title']) . '_proof_pack.pdf"'
            ]);

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(403, 'Invalid sharing token');
        } catch (\Exception $e) {
            \Log::error('Failed to download shared proof pack: ' . $e->getMessage());
            abort(500, 'Failed to generate proof pack PDF');
        }
    }

    /**
     * Track shared proof pack view
     */
    private function trackSharedView(array $sharingData, string $ip, string $userAgent): void
    {
        try {
            \Log::info('Shared proof pack viewed', [
                'company_id' => $sharingData['company_id'],
                'proof_count' => count($sharingData['proof_ids']),
                'recipient_email' => $sharingData['recipient_email'] ?? null,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'title' => $sharingData['title'],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to track shared view: ' . $e->getMessage());
        }
    }

    /**
     * Track shared proof pack download
     */
    private function trackSharedDownload(array $sharingData, string $ip, string $userAgent): void
    {
        try {
            \Log::info('Shared proof pack downloaded', [
                'company_id' => $sharingData['company_id'],
                'proof_count' => count($sharingData['proof_ids']),
                'recipient_email' => $sharingData['recipient_email'] ?? null,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'title' => $sharingData['title'],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to track shared download: ' . $e->getMessage());
        }
    }

    /**
     * Send proof pack via email with optional PDF attachment
     */
    public function emailProofPack(Request $request): JsonResponse
    {
        $request->validate([
            'proof_ids' => 'required|array|min:1',
            'proof_ids.*' => 'exists:proofs,uuid',
            'recipient_email' => 'required|email',
            'recipient_name' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'message' => 'nullable|string|max:2000',
            'email_subject' => 'nullable|string|max:255',
            'expires_in' => 'integer|min:1|max:168', // Max 7 days
            'attach_pdf' => 'boolean',
            'create_share_link' => 'boolean',
        ]);

        try {
            // Verify user can access all requested proofs
            $proofs = Proof::forCompany()
                ->whereIn('uuid', $request->proof_ids)
                ->where('status', 'active')
                ->with(['assets' => function ($query) {
                    $query->where('status', 'processed')
                        ->orderBy('display_order');
                }])
                ->get();

            if ($proofs->count() !== count($request->proof_ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more proofs not found or not accessible.'
                ], 403);
            }

            $shareData = [
                'title' => $request->title,
                'description' => $request->description,
                'message' => $request->message,
                'email_subject' => $request->email_subject,
                'recipient_email' => $request->recipient_email,
                'recipient_name' => $request->recipient_name,
                'expires_at' => now()->addHours($request->expires_in ?? 24)->toIso8601String(),
                'attach_pdf' => $request->attach_pdf ?? false,
            ];

            // Generate secure share URL if requested
            if ($request->create_share_link ?? true) {
                $sharingData = [
                    'proof_ids' => $request->proof_ids,
                    'title' => $request->title,
                    'description' => $request->description,
                    'company_id' => auth()->user()->company_id,
                    'created_by' => auth()->id(),
                    'recipient_email' => $request->recipient_email,
                    'recipient_name' => $request->recipient_name,
                    'created_at' => now()->toIso8601String(),
                    'expires_at' => $shareData['expires_at'],
                ];

                $token = encrypt($sharingData);
                $shareData['share_url'] = route('proofs.shared-pack', ['token' => $token]);
            }

            // Create a notification recipient (we'll use a generic User-like object)
            $recipient = new class($request->recipient_email, $request->recipient_name) {
                public string $email;
                public ?string $name;
                
                public function __construct(string $email, ?string $name = null) {
                    $this->email = $email;
                    $this->name = $name;
                }
                
                public function routeNotificationForMail() {
                    return $this->email;
                }
            };

            // Send the notification
            $notification = new \App\Notifications\ProofPackSharedNotification($proofs, $shareData, auth()->user());
            \Notification::send($recipient, $notification);

            // Log the email sending activity
            \Log::info('Proof pack email sent', [
                'company_id' => auth()->user()->company_id,
                'user_id' => auth()->id(),
                'proof_count' => $proofs->count(),
                'recipient_email' => $request->recipient_email,
                'attach_pdf' => $request->attach_pdf ?? false,
                'expires_in_hours' => $request->expires_in ?? 24,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proof pack email sent successfully to ' . $request->recipient_email,
                'recipient_email' => $request->recipient_email,
                'proof_count' => $proofs->count(),
                'share_url' => $shareData['share_url'] ?? null,
                'expires_at' => $shareData['expires_at'],
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send proof pack email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send proof pack email. Please try again.'
            ], 500);
        }
    }

    /**
     * Bulk email proof pack to multiple recipients
     */
    public function bulkEmailProofPack(Request $request): JsonResponse
    {
        $request->validate([
            'proof_ids' => 'required|array|min:1',
            'proof_ids.*' => 'exists:proofs,uuid',
            'recipients' => 'required|array|min:1|max:50', // Max 50 recipients
            'recipients.*.email' => 'required|email',
            'recipients.*.name' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'message' => 'nullable|string|max:2000',
            'email_subject' => 'nullable|string|max:255',
            'expires_in' => 'integer|min:1|max:168', // Max 7 days
            'attach_pdf' => 'boolean',
            'create_share_links' => 'boolean',
        ]);

        try {
            // Verify user can access all requested proofs
            $proofs = Proof::forCompany()
                ->whereIn('uuid', $request->proof_ids)
                ->where('status', 'active')
                ->with(['assets' => function ($query) {
                    $query->where('status', 'processed')
                        ->orderBy('display_order');
                }])
                ->get();

            if ($proofs->count() !== count($request->proof_ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more proofs not found or not accessible.'
                ], 403);
            }

            $results = [
                'success' => 0,
                'failed' => 0,
                'recipients' => []
            ];

            foreach ($request->recipients as $recipientData) {
                try {
                    $shareData = [
                        'title' => $request->title,
                        'description' => $request->description,
                        'message' => $request->message,
                        'email_subject' => $request->email_subject,
                        'recipient_email' => $recipientData['email'],
                        'recipient_name' => $recipientData['name'] ?? null,
                        'expires_at' => now()->addHours($request->expires_in ?? 24)->toIso8601String(),
                        'attach_pdf' => $request->attach_pdf ?? false,
                    ];

                    // Generate individual share URL if requested
                    if ($request->create_share_links ?? true) {
                        $sharingData = [
                            'proof_ids' => $request->proof_ids,
                            'title' => $request->title,
                            'description' => $request->description,
                            'company_id' => auth()->user()->company_id,
                            'created_by' => auth()->id(),
                            'recipient_email' => $recipientData['email'],
                            'recipient_name' => $recipientData['name'] ?? null,
                            'created_at' => now()->toIso8601String(),
                            'expires_at' => $shareData['expires_at'],
                        ];

                        $token = encrypt($sharingData);
                        $shareData['share_url'] = route('proofs.shared-pack', ['token' => $token]);
                    }

                    // Create recipient object
                    $recipient = new class($recipientData['email'], $recipientData['name'] ?? null) {
                        public string $email;
                        public ?string $name;
                        
                        public function __construct(string $email, ?string $name = null) {
                            $this->email = $email;
                            $this->name = $name;
                        }
                        
                        public function routeNotificationForMail() {
                            return $this->email;
                        }
                    };

                    // Send the notification
                    $notification = new \App\Notifications\ProofPackSharedNotification($proofs, $shareData, auth()->user());
                    \Notification::send($recipient, $notification);

                    $results['success']++;
                    $results['recipients'][] = [
                        'email' => $recipientData['email'],
                        'name' => $recipientData['name'] ?? null,
                        'status' => 'sent',
                        'share_url' => $shareData['share_url'] ?? null,
                    ];

                } catch (\Exception $e) {
                    \Log::error('Failed to send proof pack email to ' . $recipientData['email'] . ': ' . $e->getMessage());
                    
                    $results['failed']++;
                    $results['recipients'][] = [
                        'email' => $recipientData['email'],
                        'name' => $recipientData['name'] ?? null,
                        'status' => 'failed',
                        'error' => 'Email delivery failed',
                    ];
                }
            }

            // Log bulk email activity
            \Log::info('Bulk proof pack emails processed', [
                'company_id' => auth()->user()->company_id,
                'user_id' => auth()->id(),
                'proof_count' => $proofs->count(),
                'total_recipients' => count($request->recipients),
                'successful' => $results['success'],
                'failed' => $results['failed'],
            ]);

            return response()->json([
                'success' => true,
                'message' => "Sent {$results['success']} emails successfully" . 
                           ($results['failed'] > 0 ? ", {$results['failed']} failed" : ''),
                'results' => $results,
                'proof_count' => $proofs->count(),
                'expires_at' => now()->addHours($request->expires_in ?? 24)->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to process bulk proof pack emails: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send proof pack emails. Please try again.'
            ], 500);
        }
    }

    /**
     * Create a new version of an existing proof pack
     */
    public function createProofPackVersion(Request $request): JsonResponse
    {
        $request->validate([
            'proof_ids' => 'required|array|min:1',
            'proof_ids.*' => 'exists:proofs,uuid',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'version_notes' => 'nullable|string|max:2000',
            'base_version' => 'nullable|string|max:50',
        ]);

        try {
            // Verify user can access all requested proofs
            $proofs = Proof::forCompany()
                ->whereIn('uuid', $request->proof_ids)
                ->where('status', 'active')
                ->get();

            if ($proofs->count() !== count($request->proof_ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more proofs not found or not accessible.'
                ], 403);
            }

            // Generate version information
            $baseVersion = $request->base_version ?? '1.0';
            $newVersion = $this->incrementVersion($baseVersion);
            $versionId = \Illuminate\Support\Str::uuid();

            $versionData = [
                'version_id' => $versionId,
                'version' => $newVersion,
                'base_version' => $baseVersion,
                'title' => $request->title,
                'description' => $request->description,
                'version_notes' => $request->version_notes,
                'proof_ids' => $request->proof_ids,
                'proof_count' => $proofs->count(),
                'company_id' => auth()->user()->company_id,
                'created_by' => auth()->id(),
                'created_by_name' => auth()->user()->name,
                'created_at' => now()->toIso8601String(),
                'last_modified' => now()->toIso8601String(),
                'status' => 'active',
            ];

            // Store version data in cache/session for now (could be moved to database table later)
            $versionKey = "proof_pack_version_{$versionId}";
            cache()->put($versionKey, $versionData, now()->addDays(30)); // 30 days expiry

            // Log version creation
            \Log::info('Proof pack version created', [
                'company_id' => auth()->user()->company_id,
                'user_id' => auth()->id(),
                'version_id' => $versionId,
                'version' => $newVersion,
                'proof_count' => $proofs->count(),
                'title' => $request->title,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proof pack version created successfully',
                'version_data' => $versionData,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to create proof pack version: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create proof pack version. Please try again.'
            ], 500);
        }
    }

    /**
     * Update an existing proof pack version
     */
    public function updateProofPackVersion(Request $request, string $versionId): JsonResponse
    {
        $request->validate([
            'proof_ids' => 'sometimes|array|min:1',
            'proof_ids.*' => 'exists:proofs,uuid',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'version_notes' => 'nullable|string|max:2000',
            'update_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $versionKey = "proof_pack_version_{$versionId}";
            $versionData = cache()->get($versionKey);

            if (!$versionData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proof pack version not found or has expired.'
                ], 404);
            }

            // Verify user can access this version
            if ($versionData['company_id'] !== auth()->user()->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to this proof pack version.'
                ], 403);
            }

            // Update proof IDs if provided
            if ($request->has('proof_ids')) {
                $proofs = Proof::forCompany()
                    ->whereIn('uuid', $request->proof_ids)
                    ->where('status', 'active')
                    ->get();

                if ($proofs->count() !== count($request->proof_ids)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'One or more proofs not found or not accessible.'
                    ], 403);
                }

                $versionData['proof_ids'] = $request->proof_ids;
                $versionData['proof_count'] = $proofs->count();
            }

            // Update other fields
            if ($request->has('title')) {
                $versionData['title'] = $request->title;
            }
            if ($request->has('description')) {
                $versionData['description'] = $request->description;
            }
            if ($request->has('version_notes')) {
                $versionData['version_notes'] = $request->version_notes;
            }

            // Add update tracking
            $versionData['last_modified'] = now()->toIso8601String();
            $versionData['last_modified_by'] = auth()->id();
            $versionData['last_modified_by_name'] = auth()->user()->name;
            
            if ($request->update_notes) {
                if (!isset($versionData['update_history'])) {
                    $versionData['update_history'] = [];
                }
                $versionData['update_history'][] = [
                    'updated_at' => now()->toIso8601String(),
                    'updated_by' => auth()->user()->name,
                    'notes' => $request->update_notes,
                ];
            }

            // Save updated version data
            cache()->put($versionKey, $versionData, now()->addDays(30));

            // Log version update
            \Log::info('Proof pack version updated', [
                'company_id' => auth()->user()->company_id,
                'user_id' => auth()->id(),
                'version_id' => $versionId,
                'version' => $versionData['version'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proof pack version updated successfully',
                'version_data' => $versionData,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to update proof pack version: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update proof pack version. Please try again.'
            ], 500);
        }
    }

    /**
     * Get proof pack version details
     */
    public function getProofPackVersion(string $versionId): JsonResponse
    {
        try {
            $versionKey = "proof_pack_version_{$versionId}";
            $versionData = cache()->get($versionKey);

            if (!$versionData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proof pack version not found or has expired.'
                ], 404);
            }

            // Verify user can access this version
            if ($versionData['company_id'] !== auth()->user()->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to this proof pack version.'
                ], 403);
            }

            // Get current proofs data
            $proofs = Proof::forCompany()
                ->whereIn('uuid', $versionData['proof_ids'])
                ->where('status', 'active')
                ->with(['assets' => function ($query) {
                    $query->where('status', 'processed')
                        ->orderBy('display_order');
                }])
                ->get();

            return response()->json([
                'success' => true,
                'version_data' => $versionData,
                'proofs' => $proofs,
                'current_proof_count' => $proofs->count(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to get proof pack version: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve proof pack version. Please try again.'
            ], 500);
        }
    }

    /**
     * List all versions of proof packs for the company
     */
    public function listProofPackVersions(Request $request): JsonResponse
    {
        try {
            // This is a simplified version - in production, you'd want to store versions in database
            // For now, we'll return cached versions (this would be limited)
            $companyId = auth()->user()->company_id;
            
            // Get version keys from cache (this is a basic implementation)
            // In a real implementation, you'd query a database table
            $versions = collect();
            
            // For demonstration, we'll show how this would work
            return response()->json([
                'success' => true,
                'message' => 'Version control system is ready. Versions will appear here once created.',
                'versions' => $versions,
                'total' => 0,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to list proof pack versions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve proof pack versions. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete a proof pack version
     */
    public function deleteProofPackVersion(string $versionId): JsonResponse
    {
        try {
            $versionKey = "proof_pack_version_{$versionId}";
            $versionData = cache()->get($versionKey);

            if (!$versionData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proof pack version not found or has expired.'
                ], 404);
            }

            // Verify user can access this version
            if ($versionData['company_id'] !== auth()->user()->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to this proof pack version.'
                ], 403);
            }

            // Only allow deletion by the creator or company managers
            $user = auth()->user();
            $canDelete = $versionData['created_by'] === $user->id || 
                        $user->hasRole(['company_manager', 'sales_manager']);

            if (!$canDelete) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this proof pack version.'
                ], 403);
            }

            // Mark as deleted instead of completely removing
            $versionData['status'] = 'deleted';
            $versionData['deleted_at'] = now()->toIso8601String();
            $versionData['deleted_by'] = $user->id;
            $versionData['deleted_by_name'] = $user->name;

            cache()->put($versionKey, $versionData, now()->addDays(30));

            // Log version deletion
            \Log::info('Proof pack version deleted', [
                'company_id' => auth()->user()->company_id,
                'user_id' => auth()->id(),
                'version_id' => $versionId,
                'version' => $versionData['version'],
                'title' => $versionData['title'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proof pack version deleted successfully',
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to delete proof pack version: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete proof pack version. Please try again.'
            ], 500);
        }
    }

    /**
     * Compare two proof pack versions
     */
    public function compareProofPackVersions(Request $request): JsonResponse
    {
        $request->validate([
            'version_id_1' => 'required|string',
            'version_id_2' => 'required|string',
        ]);

        try {
            $version1Key = "proof_pack_version_{$request->version_id_1}";
            $version2Key = "proof_pack_version_{$request->version_id_2}";
            
            $version1 = cache()->get($version1Key);
            $version2 = cache()->get($version2Key);

            if (!$version1 || !$version2) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or both versions not found or have expired.'
                ], 404);
            }

            // Verify user can access both versions
            $companyId = auth()->user()->company_id;
            if ($version1['company_id'] !== $companyId || $version2['company_id'] !== $companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to one or both proof pack versions.'
                ], 403);
            }

            // Compare versions
            $comparison = [
                'version_1' => [
                    'id' => $request->version_id_1,
                    'version' => $version1['version'],
                    'title' => $version1['title'],
                    'description' => $version1['description'] ?? '',
                    'proof_count' => $version1['proof_count'],
                    'proof_ids' => $version1['proof_ids'],
                    'created_at' => $version1['created_at'],
                    'last_modified' => $version1['last_modified'] ?? $version1['created_at'],
                ],
                'version_2' => [
                    'id' => $request->version_id_2,
                    'version' => $version2['version'],
                    'title' => $version2['title'],
                    'description' => $version2['description'] ?? '',
                    'proof_count' => $version2['proof_count'],
                    'proof_ids' => $version2['proof_ids'],
                    'created_at' => $version2['created_at'],
                    'last_modified' => $version2['last_modified'] ?? $version2['created_at'],
                ],
                'differences' => [
                    'title_changed' => $version1['title'] !== $version2['title'],
                    'description_changed' => ($version1['description'] ?? '') !== ($version2['description'] ?? ''),
                    'proof_count_changed' => $version1['proof_count'] !== $version2['proof_count'],
                    'proofs_changed' => array_diff($version1['proof_ids'], $version2['proof_ids']) !== [] || 
                                      array_diff($version2['proof_ids'], $version1['proof_ids']) !== [],
                    'added_proofs' => array_diff($version2['proof_ids'], $version1['proof_ids']),
                    'removed_proofs' => array_diff($version1['proof_ids'], $version2['proof_ids']),
                ]
            ];

            return response()->json([
                'success' => true,
                'comparison' => $comparison,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to compare proof pack versions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to compare proof pack versions. Please try again.'
            ], 500);
        }
    }

    /**
     * Generate proof pack PDF from a specific version
     */
    public function generateVersionedProofPackPDF(Request $request, string $versionId): \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
    {
        try {
            $versionKey = "proof_pack_version_{$versionId}";
            $versionData = cache()->get($versionKey);

            if (!$versionData || $versionData['status'] === 'deleted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Proof pack version not found or has been deleted.'
                ], 404);
            }

            // Verify user can access this version
            if ($versionData['company_id'] !== auth()->user()->company_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied to this proof pack version.'
                ], 403);
            }

            // Get proofs for this version
            $proofs = Proof::forCompany()
                ->whereIn('uuid', $versionData['proof_ids'])
                ->where('status', 'active')
                ->with(['assets' => function ($query) {
                    $query->where('status', 'processed')
                        ->orderBy('display_order');
                }])
                ->get();

            if ($proofs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid proofs found for this version.'
                ], 404);
            }

            // Generate PDF with version information
            $pdfService = app(\App\Services\PDFService::class);
            $options = [
                'title' => $versionData['title'],
                'description' => $versionData['description'],
                'show_company_info' => true,
                'include_analytics' => true,
                'version' => $versionData['version'],
                'version_notes' => $versionData['version_notes'] ?? null,
                'watermark' => 'VERSION ' . $versionData['version'],
            ];

            $pdfPath = $pdfService->generateProofPackPDF($proofs, $options);
            
            $filename = \Illuminate\Support\Str::slug($versionData['title']) . '_v' . $versionData['version'] . '_proof_pack.pdf';
            
            return response()->file(Storage::path($pdfPath), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to generate versioned proof pack PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate versioned proof pack PDF. Please try again.'
            ], 500);
        }
    }

    /**
     * Helper method to increment version numbers
     */
    private function incrementVersion(string $baseVersion): string
    {
        // Simple semantic versioning (major.minor.patch)
        $parts = explode('.', $baseVersion);
        
        if (count($parts) >= 2) {
            // Increment minor version
            $parts[1] = (int)$parts[1] + 1;
            if (count($parts) >= 3) {
                $parts[2] = 0; // Reset patch version
            }
        } else {
            // If not semantic versioning, just append .1
            return $baseVersion . '.1';
        }
        
        return implode('.', $parts);
    }
}
