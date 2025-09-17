<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CertificationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Certification::class, 'certification');
    }

    /**
     * Display a listing of certifications with filtering and search
     */
    public function index(Request $request)
    {
        $query = Certification::query()
            ->forCompany()
            ->with(['createdBy', 'updatedBy', 'verifiedBy']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('certification_body', 'like', "%{$search}%")
                  ->orWhere('certificate_number', 'like', "%{$search}%")
                  ->orWhere('certification_type', 'like', "%{$search}%");
            });
        }

        // Apply status filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        // Apply certification type filter
        if ($request->filled('certification_type')) {
            $query->byType($request->certification_type);
        }

        // Apply expiry filters
        if ($request->filled('expiry_status')) {
            switch ($request->expiry_status) {
                case 'active':
                    $query->active();
                    break;
                case 'expired':
                    $query->expired();
                    break;
                case 'expiring_soon':
                    $query->expiringWithin(90);
                    break;
                case 'expiring_30':
                    $query->expiringWithin(30);
                    break;
            }
        }

        // Apply featured filter
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Apply verified filter
        if ($request->boolean('verified')) {
            $query->verified();
        }

        // Apply sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['created_at', 'title', 'certification_type', 'issued_date', 'expiry_date', 'status'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $certifications = $query->paginate(20)->withQueryString();

        // Get filter statistics
        $stats = $this->getCertificationStats();

        // Get certification types for filter dropdown
        $certificationTypes = $this->getCertificationTypes();

        return view('certifications.index', compact('certifications', 'stats', 'certificationTypes'));
    }

    /**
     * Show the form for creating a new certification
     */
    public function create()
    {
        $certification = new Certification();
        $certificationTypes = $this->getCertificationTypes();
        $certificationBodies = $this->getCertificationBodies();

        return view('certifications.create', compact('certification', 'certificationTypes', 'certificationBodies'));
    }

    /**
     * Store a newly created certification
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'certification_body' => 'required|string|max:255',
            'certificate_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'certification_type' => 'required|string|max:255',
            'scope' => 'nullable|string',
            'issued_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issued_date',
            'does_expire' => 'boolean',
            'validity_years' => 'nullable|integer|min:1|max:50',
            'auto_renewal' => 'boolean',
            'issuing_authority' => 'nullable|string|max:255',
            'assessor_name' => 'nullable|string|max:255',
            'assessor_number' => 'nullable|string|max:255',
            'next_assessment_date' => 'nullable|date',
            'next_surveillance_date' => 'nullable|date',
            'renewal_reminder_days' => 'nullable|integer|min:1|max:365',
            'certification_cost' => 'nullable|numeric|min:0',
            'business_benefits' => 'nullable|string',
            'show_on_documents' => 'boolean',
            'show_on_website' => 'boolean',
            'show_expiry_date' => 'boolean',
            'is_featured' => 'boolean',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'accreditation_logo' => 'nullable|image|max:2048',
            'supporting_documents.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'applicable_services' => 'nullable|array',
            'applicable_projects' => 'nullable|array',
            'compliance_requirements' => 'nullable|array',
        ]);

        $certification = new Certification($validated);

        // Handle file uploads
        if ($request->hasFile('certificate_file')) {
            $certification->certificate_file = $this->uploadFile($request->file('certificate_file'), 'certificates');
        }

        if ($request->hasFile('accreditation_logo')) {
            $certification->accreditation_logo = $this->uploadFile($request->file('accreditation_logo'), 'logos');
        }

        if ($request->hasFile('supporting_documents')) {
            $supportingDocs = [];
            foreach ($request->file('supporting_documents') as $doc) {
                $supportingDocs[] = $this->uploadFile($doc, 'supporting-docs');
            }
            $certification->supporting_documents = $supportingDocs;
        }

        // Calculate expiry date if validity years provided
        if ($certification->validity_years && $certification->issued_date) {
            $certification->expiry_date = Carbon::parse($certification->issued_date)->addYears($certification->validity_years);
        }

        // Set default renewal reminder days
        if (!$certification->renewal_reminder_days) {
            $certification->renewal_reminder_days = 90;
        }

        $certification->save();

        return redirect()->route('certifications.show', $certification->uuid)
                        ->with('success', 'Certification created successfully.');
    }

    /**
     * Display the specified certification
     */
    public function show(Certification $certification)
    {
        $certification->load(['createdBy', 'updatedBy', 'verifiedBy']);
        
        // Increment view count
        $certification->incrementViews();

        return view('certifications.show', compact('certification'));
    }

    /**
     * Show the form for editing the specified certification
     */
    public function edit(Certification $certification)
    {
        $certificationTypes = $this->getCertificationTypes();
        $certificationBodies = $this->getCertificationBodies();

        return view('certifications.edit', compact('certification', 'certificationTypes', 'certificationBodies'));
    }

    /**
     * Update the specified certification
     */
    public function update(Request $request, Certification $certification)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'certification_body' => 'required|string|max:255',
            'certificate_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'certification_type' => 'required|string|max:255',
            'scope' => 'nullable|string',
            'issued_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issued_date',
            'does_expire' => 'boolean',
            'validity_years' => 'nullable|integer|min:1|max:50',
            'auto_renewal' => 'boolean',
            'issuing_authority' => 'nullable|string|max:255',
            'assessor_name' => 'nullable|string|max:255',
            'assessor_number' => 'nullable|string|max:255',
            'next_assessment_date' => 'nullable|date',
            'next_surveillance_date' => 'nullable|date',
            'renewal_reminder_days' => 'nullable|integer|min:1|max:365',
            'certification_cost' => 'nullable|numeric|min:0',
            'business_benefits' => 'nullable|string',
            'show_on_documents' => 'boolean',
            'show_on_website' => 'boolean',
            'show_expiry_date' => 'boolean',
            'is_featured' => 'boolean',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'accreditation_logo' => 'nullable|image|max:2048',
            'supporting_documents.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'applicable_services' => 'nullable|array',
            'applicable_projects' => 'nullable|array',
            'compliance_requirements' => 'nullable|array',
        ]);

        // Handle file uploads
        if ($request->hasFile('certificate_file')) {
            // Delete old file
            if ($certification->certificate_file) {
                Storage::delete($certification->certificate_file);
            }
            $validated['certificate_file'] = $this->uploadFile($request->file('certificate_file'), 'certificates');
        }

        if ($request->hasFile('accreditation_logo')) {
            // Delete old logo
            if ($certification->accreditation_logo) {
                Storage::delete($certification->accreditation_logo);
            }
            $validated['accreditation_logo'] = $this->uploadFile($request->file('accreditation_logo'), 'logos');
        }

        if ($request->hasFile('supporting_documents')) {
            // Delete old supporting documents
            if ($certification->supporting_documents) {
                foreach ($certification->supporting_documents as $oldDoc) {
                    Storage::delete($oldDoc);
                }
            }
            
            $supportingDocs = [];
            foreach ($request->file('supporting_documents') as $doc) {
                $supportingDocs[] = $this->uploadFile($doc, 'supporting-docs');
            }
            $validated['supporting_documents'] = $supportingDocs;
        }

        // Calculate expiry date if validity years provided
        if (isset($validated['validity_years']) && $validated['validity_years'] && $validated['issued_date']) {
            $validated['expiry_date'] = Carbon::parse($validated['issued_date'])->addYears($validated['validity_years']);
        }

        $certification->update($validated);

        return redirect()->route('certifications.show', $certification->uuid)
                        ->with('success', 'Certification updated successfully.');
    }

    /**
     * Remove the specified certification
     */
    public function destroy(Certification $certification)
    {
        // Delete associated files
        if ($certification->certificate_file) {
            Storage::delete($certification->certificate_file);
        }

        if ($certification->accreditation_logo) {
            Storage::delete($certification->accreditation_logo);
        }

        if ($certification->supporting_documents) {
            foreach ($certification->supporting_documents as $doc) {
                Storage::delete($doc);
            }
        }

        $certification->delete();

        return redirect()->route('certifications.index')
                        ->with('success', 'Certification deleted successfully.');
    }

    /**
     * Verify a certification
     */
    public function verify(Request $request, Certification $certification)
    {
        $this->authorize('verify', $certification);

        $validated = $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        $certification->verify($validated['verification_notes']);

        return back()->with('success', 'Certification verified successfully.');
    }

    /**
     * Revoke a certification
     */
    public function revoke(Request $request, Certification $certification)
    {
        $this->authorize('manage', $certification);

        $validated = $request->validate([
            'revocation_reason' => 'required|string|max:1000',
        ]);

        $certification->revoke($validated['revocation_reason']);

        return back()->with('success', 'Certification revoked.');
    }

    /**
     * Suspend a certification
     */
    public function suspend(Request $request, Certification $certification)
    {
        $this->authorize('manage', $certification);

        $validated = $request->validate([
            'suspension_reason' => 'required|string|max:1000',
        ]);

        $certification->suspend($validated['suspension_reason']);

        return back()->with('success', 'Certification suspended.');
    }

    /**
     * Reactivate a certification
     */
    public function reactivate(Certification $certification)
    {
        $this->authorize('manage', $certification);

        if ($certification->reactivate()) {
            return back()->with('success', 'Certification reactivated successfully.');
        }

        return back()->with('error', 'Cannot reactivate expired certification.');
    }

    /**
     * Renew a certification
     */
    public function renew(Request $request, Certification $certification)
    {
        $this->authorize('manage', $certification);

        $validated = $request->validate([
            'new_expiry_date' => 'required|date|after:today',
        ]);

        $newExpiryDate = Carbon::parse($validated['new_expiry_date']);
        
        if ($certification->renew($newExpiryDate)) {
            return back()->with('success', 'Certification renewed successfully.');
        }

        return back()->with('error', 'Failed to renew certification.');
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Certification $certification)
    {
        $this->authorize('update', $certification);

        $certification->update(['is_featured' => !$certification->is_featured]);

        $status = $certification->is_featured ? 'featured' : 'unfeatured';
        return back()->with('success', "Certification {$status} successfully.");
    }

    /**
     * Send renewal reminder
     */
    public function sendRenewalReminder(Certification $certification)
    {
        $this->authorize('manage', $certification);

        if ($certification->sendRenewalReminder()) {
            return back()->with('success', 'Renewal reminder sent successfully.');
        }

        return back()->with('info', 'Reminder already sent for this certification.');
    }

    /**
     * Upload file and return path
     */
    private function uploadFile($file, $directory)
    {
        $companyId = auth()->user()->company_id;
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "certifications/{$companyId}/{$directory}/{$filename}";
        
        Storage::putFileAs(dirname($path), $file, basename($path));
        
        return $path;
    }

    /**
     * Get certification statistics
     */
    private function getCertificationStats()
    {
        $baseQuery = Certification::forCompany();

        return [
            'total' => $baseQuery->count(),
            'active' => $baseQuery->active()->count(),
            'expired' => $baseQuery->expired()->count(),
            'expiring_soon' => $baseQuery->expiringWithin(90)->count(),
            'expiring_30' => $baseQuery->expiringWithin(30)->count(),
            'verified' => $baseQuery->verified()->count(),
            'featured' => $baseQuery->featured()->count(),
            'for_documents' => $baseQuery->forDocuments()->count(),
        ];
    }

    /**
     * Get available certification types
     */
    private function getCertificationTypes()
    {
        return [
            'ISO 9001' => 'ISO 9001 - Quality Management',
            'ISO 14001' => 'ISO 14001 - Environmental Management',
            'ISO 45001' => 'ISO 45001 - Occupational Health & Safety',
            'ISO 27001' => 'ISO 27001 - Information Security',
            'OHSAS 18001' => 'OHSAS 18001 - Occupational Health & Safety',
            'HACCP' => 'HACCP - Food Safety',
            'CE Marking' => 'CE Marking - European Conformity',
            'Green Building' => 'Green Building Certification',
            'Energy Star' => 'Energy Star Certification',
            'LEED' => 'LEED - Leadership in Energy and Environmental Design',
            'Professional License' => 'Professional License',
            'Trade Certification' => 'Trade Certification',
            'Industry Specific' => 'Industry Specific Certification',
            'Other' => 'Other Certification',
        ];
    }

    /**
     * Get certification bodies
     */
    private function getCertificationBodies()
    {
        return [
            'ISO' => 'International Organization for Standardization',
            'SIRIM' => 'SIRIM Berhad',
            'BSI' => 'British Standards Institution',
            'SGS' => 'SGS Malaysia',
            'TUV' => 'TUV SUD Malaysia',
            'Bureau Veritas' => 'Bureau Veritas Malaysia',
            'UKAS' => 'United Kingdom Accreditation Service',
            'IEC' => 'International Electrotechnical Commission',
            'Government Authority' => 'Government Authority',
            'Professional Body' => 'Professional Body',
            'Industry Association' => 'Industry Association',
            'Other' => 'Other Certification Body',
        ];
    }

    /**
     * Download certification file
     */
    public function download(Certification $certification)
    {
        $this->authorize('view', $certification);

        if (!$certification->certificate_file_path) {
            abort(404, 'Certificate file not found.');
        }

        if (!Storage::exists($certification->certificate_file_path)) {
            abort(404, 'Certificate file no longer exists.');
        }

        $fileName = $certification->name . '_Certificate.' . pathinfo($certification->certificate_file_path, PATHINFO_EXTENSION);
        
        return Storage::download($certification->certificate_file_path, $fileName);
    }
}
