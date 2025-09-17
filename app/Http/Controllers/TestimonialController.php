<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TestimonialController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Testimonial::class, 'testimonial');
    }

    /**
     * Display a listing of testimonials with filtering and search
     */
    public function index(Request $request)
    {
        $query = Testimonial::query()
            ->forCompany()
            ->with(['createdBy', 'approvedBy', 'relatedQuotation', 'relatedInvoice', 'relatedLead']);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_company', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Apply status filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        // Apply rating filter
        if ($request->filled('min_rating')) {
            $query->byRating($request->min_rating);
        }

        // Apply project type filter
        if ($request->filled('project_type')) {
            $query->forProjectType($request->project_type);
        }

        // Apply featured filter
        if ($request->boolean('featured')) {
            $query->featured();
        }

        // Apply consent filter
        if ($request->boolean('with_consent')) {
            $query->withConsent();
        }

        // Apply sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['created_at', 'title', 'customer_name', 'rating', 'project_value', 'approved_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $testimonials = $query->paginate(20)->withQueryString();

        // Get filter statistics
        $stats = $this->getTestimonialStats();

        return view('testimonials.index', compact('testimonials', 'stats'));
    }

    /**
     * Show the form for creating a new testimonial
     */
    public function create(Request $request)
    {
        $testimonial = new Testimonial();
        
        // Pre-populate from related entity if provided
        if ($request->filled('quotation_id')) {
            $quotation = Quotation::forCompany()->findOrFail($request->quotation_id);
            $testimonial->fill([
                'customer_name' => $quotation->customer_name,
                'customer_email' => $quotation->customer_email,
                'customer_company' => $quotation->customer_company,
                'customer_phone' => $quotation->customer_phone,
                'project_value' => $quotation->total,
                'related_quotation_id' => $quotation->id,
            ]);
        }

        if ($request->filled('invoice_id')) {
            $invoice = Invoice::forCompany()->findOrFail($request->invoice_id);
            $testimonial->fill([
                'customer_name' => $invoice->customer_name,
                'customer_email' => $invoice->customer_email,
                'customer_company' => $invoice->customer_company,
                'customer_phone' => $invoice->customer_phone,
                'project_value' => $invoice->total,
                'related_invoice_id' => $invoice->id,
            ]);
        }

        if ($request->filled('lead_id')) {
            $lead = Lead::forCompany()->findOrFail($request->lead_id);
            $testimonial->fill([
                'customer_name' => $lead->contact_name,
                'customer_email' => $lead->email,
                'customer_company' => $lead->company_name,
                'customer_phone' => $lead->phone,
                'related_lead_id' => $lead->id,
            ]);
        }

        $projectTypes = $this->getProjectTypes();
        $collectionMethods = $this->getCollectionMethods();

        return view('testimonials.create', compact('testimonial', 'projectTypes', 'collectionMethods'));
    }

    /**
     * Store a newly created testimonial
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_company' => 'nullable|string|max:255',
            'customer_position' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'content' => 'required|string',
            'summary' => 'nullable|string|max:500',
            'rating' => 'nullable|integer|min:1|max:5',
            'project_type' => 'nullable|string|max:255',
            'project_value' => 'nullable|numeric|min:0',
            'project_completion_date' => 'nullable|date',
            'customer_photo' => 'nullable|image|max:2048',
            'customer_signature' => 'nullable|image|max:2048',
            'project_images.*' => 'nullable|image|max:5120',
            'allow_public_display' => 'boolean',
            'show_customer_name' => 'boolean',
            'show_customer_company' => 'boolean',
            'is_featured' => 'boolean',
            'consent_given' => 'boolean',
            'consent_method' => 'nullable|string|max:50',
            'marketing_consent' => 'boolean',
            'collection_method' => 'required|string|in:' . implode(',', array_keys($this->getCollectionMethods())),
            'source_url' => 'nullable|url',
            'related_quotation_id' => 'nullable|exists:quotations,id',
            'related_invoice_id' => 'nullable|exists:invoices,id',
            'related_lead_id' => 'nullable|exists:leads,id',
        ]);

        $testimonial = new Testimonial($validated);

        // Handle file uploads
        if ($request->hasFile('customer_photo')) {
            $testimonial->customer_photo = $this->uploadFile($request->file('customer_photo'), 'customer-photos');
        }

        if ($request->hasFile('customer_signature')) {
            $testimonial->customer_signature = $this->uploadFile($request->file('customer_signature'), 'customer-signatures');
        }

        if ($request->hasFile('project_images')) {
            $projectImages = [];
            foreach ($request->file('project_images') as $image) {
                $projectImages[] = $this->uploadFile($image, 'project-images');
            }
            $testimonial->project_images = $projectImages;
        }

        // Set consent date if consent given
        if ($testimonial->consent_given && !$testimonial->consent_date) {
            $testimonial->consent_date = now();
        }

        $testimonial->save();

        return redirect()->route('testimonials.show', $testimonial->uuid)
                        ->with('success', 'Testimonial created successfully.');
    }

    /**
     * Display the specified testimonial
     */
    public function show(Testimonial $testimonial)
    {
        $testimonial->load(['createdBy', 'updatedBy', 'approvedBy', 'relatedQuotation', 'relatedInvoice', 'relatedLead']);
        
        // Increment view count
        $testimonial->incrementViews();

        return view('testimonials.show', compact('testimonial'));
    }

    /**
     * Show the form for editing the specified testimonial
     */
    public function edit(Testimonial $testimonial)
    {
        $projectTypes = $this->getProjectTypes();
        $collectionMethods = $this->getCollectionMethods();

        return view('testimonials.edit', compact('testimonial', 'projectTypes', 'collectionMethods'));
    }

    /**
     * Update the specified testimonial
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_company' => 'nullable|string|max:255',
            'customer_position' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'content' => 'required|string',
            'summary' => 'nullable|string|max:500',
            'rating' => 'nullable|integer|min:1|max:5',
            'project_type' => 'nullable|string|max:255',
            'project_value' => 'nullable|numeric|min:0',
            'project_completion_date' => 'nullable|date',
            'customer_photo' => 'nullable|image|max:2048',
            'customer_signature' => 'nullable|image|max:2048',
            'project_images.*' => 'nullable|image|max:5120',
            'allow_public_display' => 'boolean',
            'show_customer_name' => 'boolean',
            'show_customer_company' => 'boolean',
            'is_featured' => 'boolean',
            'consent_given' => 'boolean',
            'consent_method' => 'nullable|string|max:50',
            'marketing_consent' => 'boolean',
            'source_url' => 'nullable|url',
        ]);

        // Handle file uploads
        if ($request->hasFile('customer_photo')) {
            // Delete old photo
            if ($testimonial->customer_photo) {
                Storage::delete($testimonial->customer_photo);
            }
            $validated['customer_photo'] = $this->uploadFile($request->file('customer_photo'), 'customer-photos');
        }

        if ($request->hasFile('customer_signature')) {
            // Delete old signature
            if ($testimonial->customer_signature) {
                Storage::delete($testimonial->customer_signature);
            }
            $validated['customer_signature'] = $this->uploadFile($request->file('customer_signature'), 'customer-signatures');
        }

        if ($request->hasFile('project_images')) {
            // Delete old images
            if ($testimonial->project_images) {
                foreach ($testimonial->project_images as $oldImage) {
                    Storage::delete($oldImage);
                }
            }
            
            $projectImages = [];
            foreach ($request->file('project_images') as $image) {
                $projectImages[] = $this->uploadFile($image, 'project-images');
            }
            $validated['project_images'] = $projectImages;
        }

        // Set consent date if consent given and not already set
        if ($validated['consent_given'] && !$testimonial->consent_date) {
            $validated['consent_date'] = now();
        }

        $testimonial->update($validated);

        return redirect()->route('testimonials.show', $testimonial->uuid)
                        ->with('success', 'Testimonial updated successfully.');
    }

    /**
     * Remove the specified testimonial
     */
    public function destroy(Testimonial $testimonial)
    {
        // Delete associated files
        if ($testimonial->customer_photo) {
            Storage::delete($testimonial->customer_photo);
        }

        if ($testimonial->customer_signature) {
            Storage::delete($testimonial->customer_signature);
        }

        if ($testimonial->project_images) {
            foreach ($testimonial->project_images as $image) {
                Storage::delete($image);
            }
        }

        $testimonial->delete();

        return redirect()->route('testimonials.index')
                        ->with('success', 'Testimonial deleted successfully.');
    }

    /**
     * Approve a testimonial
     */
    public function approve(Testimonial $testimonial)
    {
        $this->authorize('approve', $testimonial);

        $testimonial->approve();

        return back()->with('success', 'Testimonial approved successfully.');
    }

    /**
     * Reject a testimonial
     */
    public function reject(Request $request, Testimonial $testimonial)
    {
        $this->authorize('approve', $testimonial);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $testimonial->reject($validated['rejection_reason']);

        return back()->with('success', 'Testimonial rejected.');
    }

    /**
     * Publish a testimonial
     */
    public function publish(Testimonial $testimonial)
    {
        $this->authorize('publish', $testimonial);

        if ($testimonial->publish()) {
            return back()->with('success', 'Testimonial published successfully.');
        }

        return back()->with('error', 'Cannot publish testimonial. Check approval status and consent.');
    }

    /**
     * Archive a testimonial
     */
    public function archive(Testimonial $testimonial)
    {
        $this->authorize('update', $testimonial);

        $testimonial->archive();

        return back()->with('success', 'Testimonial archived successfully.');
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Testimonial $testimonial)
    {
        $this->authorize('update', $testimonial);

        $testimonial->update(['is_featured' => !$testimonial->is_featured]);

        $status = $testimonial->is_featured ? 'featured' : 'unfeatured';
        return back()->with('success', "Testimonial {$status} successfully.");
    }

    /**
     * Upload file and return path
     */
    private function uploadFile($file, $directory)
    {
        $companyId = auth()->user()->company_id;
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "testimonials/{$companyId}/{$directory}/{$filename}";
        
        Storage::putFileAs(dirname($path), $file, basename($path));
        
        return $path;
    }

    /**
     * Get testimonial statistics
     */
    private function getTestimonialStats()
    {
        $baseQuery = Testimonial::forCompany();

        return [
            'total' => $baseQuery->count(),
            'pending_approval' => $baseQuery->where('approval_status', Testimonial::APPROVAL_STATUS_PENDING)->count(),
            'approved' => $baseQuery->approved()->count(),
            'published' => $baseQuery->published()->count(),
            'featured' => $baseQuery->featured()->count(),
            'with_consent' => $baseQuery->withConsent()->count(),
            'high_rated' => $baseQuery->byRating(4)->count(),
        ];
    }

    /**
     * Get available project types
     */
    private function getProjectTypes()
    {
        return [
            'Construction' => 'Construction',
            'Renovation' => 'Renovation',
            'Installation' => 'Installation',
            'Maintenance' => 'Maintenance',
            'Repair' => 'Repair',
            'Consultation' => 'Consultation',
            'Design' => 'Design',
            'Other' => 'Other',
        ];
    }

    /**
     * Get collection methods
     */
    private function getCollectionMethods()
    {
        return [
            Testimonial::COLLECTION_METHOD_MANUAL => 'Manual Entry',
            Testimonial::COLLECTION_METHOD_EMAIL_REQUEST => 'Email Request',
            Testimonial::COLLECTION_METHOD_FORM_SUBMISSION => 'Form Submission',
            Testimonial::COLLECTION_METHOD_IMPORTED => 'Imported',
        ];
    }
}
