<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentSection;
use App\Models\AssessmentItem;
use App\Models\AssessmentPhoto;
use App\Models\ServiceAssessmentTemplate;
use App\Models\Lead;
use App\Models\Team;
use App\Models\User;
use App\Models\Quotation;
use App\Models\NumberSequence;
use App\Services\PDFService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AssessmentController extends Controller
{
    protected PDFService $pdfService;

    /**
     * Create the controller instance.
     */
    public function __construct(PDFService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Display a listing of assessments.
     */
    public function index(Request $request): View
    {
        $query = Assessment::query()
            ->forCompany()
            ->forUserTeams()
            ->with(['lead', 'team', 'assignedUser', 'serviceTemplate']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('assessment_code', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('contact_phone', 'like', "%{$search}%")
                  ->orWhere('property_address', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $assessments = $query->paginate(20)->appends($request->query());

        // Get filter options
        $teams = Team::forCompany()->get();
        $users = User::whereHas('teams', function ($q) {
            $q->forCompany();
        })->get();

        // Dashboard statistics
        $stats = [
            'total' => Assessment::forCompany()->forUserTeams()->count(),
            'draft' => Assessment::forCompany()->forUserTeams()->where('status', Assessment::STATUS_DRAFT)->count(),
            'scheduled' => Assessment::forCompany()->forUserTeams()->where('status', Assessment::STATUS_SCHEDULED)->count(),
            'in_progress' => Assessment::forCompany()->forUserTeams()->where('status', Assessment::STATUS_IN_PROGRESS)->count(),
            'completed' => Assessment::forCompany()->forUserTeams()->where('status', Assessment::STATUS_COMPLETED)->count(),
            'overdue' => Assessment::forCompany()->forUserTeams()->overdue()->count(),
        ];

        return view('assessments.index', compact('assessments', 'teams', 'users', 'stats'));
    }

    /**
     * Show the form for creating a new assessment.
     */
    public function create(Request $request): View
    {
        $lead = null;
        if ($request->filled('lead_id')) {
            $lead = Lead::forCompany()->forUserTeams()->findOrFail($request->lead_id);
        }

        $teams = Team::forCompany()->get();
        $users = User::whereHas('teams', function ($q) {
            $q->forCompany();
        })->get();

        $serviceTemplates = ServiceAssessmentTemplate::forCompany()
            ->active()
            ->approved()
            ->get()
            ->groupBy('service_type');

        return view('assessments.create', compact('lead', 'teams', 'users', 'serviceTemplates'));
    }

    /**
     * Store a newly created assessment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'team_id' => 'required|exists:teams,id',
            'assigned_to' => 'required|exists:users,id',
            'service_type' => 'required|in:waterproofing,painting,sports_court,industrial',
            'assessment_type' => 'required|in:initial,detailed,maintenance,warranty,compliance',
            'service_template_id' => 'nullable|exists:service_assessment_templates,id',
            'client_name' => 'required|string|max:100',
            'company' => 'nullable|string|max:100',
            'contact_email' => 'nullable|email|max:100',
            'contact_phone' => 'required|string|max:20',
            'property_address' => 'required|string|max:255',
            'property_type' => 'nullable|string|max:50',
            'property_size' => 'nullable|string|max:50',
            'property_age' => 'nullable|integer|min:0|max:200',
            'priority' => 'required|in:low,medium,high,urgent',
            'requested_date' => 'nullable|date|after_or_equal:today',
            'scheduled_date' => 'nullable|date|after_or_equal:today',
            'estimated_cost' => 'nullable|numeric|min:0',
            'budget_range' => 'nullable|string|max:50',
            'timeline_urgency' => 'nullable|string|max:100',
            'summary' => 'nullable|string|max:1000',
            'internal_notes' => 'nullable|string|max:2000',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Generate assessment code
            $assessmentCode = $this->generateAssessmentCode();

            // Create assessment
            $assessment = Assessment::create([
                'uuid' => Str::uuid(),
                'assessment_code' => $assessmentCode,
                'company_id' => auth()->user()->company_id,
                'created_by' => auth()->id(),
                'status' => Assessment::STATUS_DRAFT,
                ...$validated
            ]);

            // If service template is selected, generate sections and items
            if ($validated['service_template_id']) {
                $template = ServiceAssessmentTemplate::find($validated['service_template_id']);
                if ($template) {
                    $template->generateAssessmentSections($assessment);
                    $template->incrementUsage();
                }
            }

            // Update lead status if assessment is created from lead
            if ($assessment->lead_id) {
                $lead = $assessment->lead;
                if ($lead && $lead->status === 'NEW') {
                    $lead->update(['status' => 'ASSESSMENT_SCHEDULED']);
                }
            }

            return redirect()
                ->route('assessments.show', $assessment)
                ->with('success', 'Assessment created successfully.');
        });
    }

    /**
     * Display the specified assessment.
     */
    public function show(Assessment $assessment): View
    {
        $this->authorize('view', $assessment);

        $assessment->load([
            'lead',
            'team',
            'assignedUser',
            'createdBy',
            'serviceTemplate',
            'sections.items',
            'photos' => function ($query) {
                $query->orderBy('display_order');
            }
        ]);

        // Calculate progress statistics
        $totalSections = $assessment->sections->count();
        $completedSections = $assessment->sections->where('status', 'completed')->count();
        $progressPercentage = $totalSections > 0 ? round(($completedSections / $totalSections) * 100) : 0;

        // Get recent photos
        $recentPhotos = $assessment->photos()
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return view('assessments.show', compact('assessment', 'progressPercentage', 'recentPhotos'));
    }

    /**
     * Show the form for editing the assessment.
     */
    public function edit(Assessment $assessment): View
    {
        $this->authorize('update', $assessment);

        $assessment->load(['sections.items', 'photos']);

        $teams = Team::forCompany()->get();
        $users = User::whereHas('teams', function ($q) {
            $q->forCompany();
        })->get();

        return view('assessments.edit', compact('assessment', 'teams', 'users'));
    }

    /**
     * Update the specified assessment.
     */
    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('update', $assessment);

        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
            'assigned_to' => 'required|exists:users,id',
            'client_name' => 'required|string|max:100',
            'company' => 'nullable|string|max:100',
            'contact_email' => 'nullable|email|max:100',
            'contact_phone' => 'required|string|max:20',
            'property_address' => 'required|string|max:255',
            'property_type' => 'nullable|string|max:50',
            'property_size' => 'nullable|string|max:50',
            'property_age' => 'nullable|integer|min:0|max:200',
            'priority' => 'required|in:low,medium,high,urgent',
            'requested_date' => 'nullable|date',
            'scheduled_date' => 'nullable|date',
            'estimated_cost' => 'nullable|numeric|min:0',
            'budget_range' => 'nullable|string|max:50',
            'timeline_urgency' => 'nullable|string|max:100',
            'summary' => 'nullable|string|max:1000',
            'internal_notes' => 'nullable|string|max:2000',
            'weather_conditions' => 'nullable|string|max:100',
            'temperature' => 'nullable|numeric|min:-50|max:60',
            'humidity_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        $assessment->update($validated);

        return redirect()
            ->route('assessments.show', $assessment)
            ->with('success', 'Assessment updated successfully.');
    }

    /**
     * Remove the specified assessment.
     */
    public function destroy(Assessment $assessment): RedirectResponse
    {
        $this->authorize('delete', $assessment);

        return DB::transaction(function () use ($assessment) {
            // Delete associated files
            $assessment->photos->each(function ($photo) {
                $photo->deleteFiles();
            });

            // Delete the assessment
            $assessment->delete();

            return redirect()
                ->route('assessments.index')
                ->with('success', 'Assessment deleted successfully.');
        });
    }

    /**
     * Schedule an assessment.
     */
    public function schedule(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('update', $assessment);

        $validated = $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
            'assigned_to' => 'required|exists:users,id',
        ]);

        if ($assessment->schedule(Carbon::parse($validated['scheduled_date']), $validated['assigned_to'])) {
            return redirect()
                ->route('assessments.show', $assessment)
                ->with('success', 'Assessment scheduled successfully.');
        }

        return redirect()
            ->route('assessments.show', $assessment)
            ->with('error', 'Unable to schedule assessment. Please check the current status.');
    }

    /**
     * Start an assessment.
     */
    public function start(Assessment $assessment): RedirectResponse
    {
        $this->authorize('update', $assessment);

        if ($assessment->start()) {
            return redirect()
                ->route('assessments.show', $assessment)
                ->with('success', 'Assessment started successfully.');
        }

        return redirect()
            ->route('assessments.show', $assessment)
            ->with('error', 'Unable to start assessment. Please check the current status.');
    }

    /**
     * Mark assessment as completed.
     */
    public function complete(Assessment $assessment): RedirectResponse
    {
        $this->authorize('update', $assessment);

        if ($assessment->markAsCompleted()) {
            return redirect()
                ->route('assessments.show', $assessment)
                ->with('success', 'Assessment marked as completed successfully.');
        }

        return redirect()
            ->route('assessments.show', $assessment)
            ->with('error', 'Unable to complete assessment. Please ensure all sections are completed.');
    }

    /**
     * Generate quotation from assessment.
     */
    public function generateQuotation(Assessment $assessment): RedirectResponse
    {
        $this->authorize('update', $assessment);

        $quotation = $assessment->generateQuotation();

        if ($quotation) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('success', 'Quotation generated successfully from assessment.');
        }

        return redirect()
            ->route('assessments.show', $assessment)
            ->with('error', 'Unable to generate quotation. Assessment must be completed first.');
    }

    /**
     * Upload photos for assessment.
     */
    public function uploadPhotos(Request $request, Assessment $assessment): JsonResponse
    {
        $this->authorize('update', $assessment);

        $request->validate([
            'photos' => 'required|array|max:10',
            'photos.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'section_id' => 'nullable|exists:assessment_sections,id',
            'item_id' => 'nullable|exists:assessment_items,id',
            'photo_type' => 'required|in:before,during,after,issue,general,reference',
        ]);

        $uploadedPhotos = [];

        foreach ($request->file('photos') as $file) {
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs("assessments/{$assessment->company_id}/{$assessment->uuid}/photos", $filename, 'public');

            $photo = AssessmentPhoto::create([
                'assessment_id' => $assessment->id,
                'section_id' => $request->section_id,
                'item_id' => $request->item_id,
                'file_path' => $path,
                'file_name' => $filename,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'photo_type' => $request->photo_type,
                'display_order' => AssessmentPhoto::where('assessment_id', $assessment->id)->count() + 1,
            ]);

            // Process photo in background
            $photo->processPhoto();

            $uploadedPhotos[] = [
                'id' => $photo->id,
                'file_name' => $photo->file_name,
                'file_url' => $photo->getFileUrl(),
                'thumbnail_url' => $photo->getThumbnailUrl(),
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Photos uploaded successfully.',
            'photos' => $uploadedPhotos,
        ]);
    }

    /**
     * Download assessment PDF report.
     */
    public function downloadPDF(Assessment $assessment): Response
    {
        $this->authorize('view', $assessment);

        try {
            $pdf = $this->pdfService->generateAssessmentPDF($assessment);
            $filename = "assessment-{$assessment->assessment_code}.pdf";

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        } catch (\Exception $e) {
            return redirect()
                ->route('assessments.show', $assessment)
                ->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    /**
     * Preview assessment PDF report.
     */
    public function previewPDF(Assessment $assessment): Response
    {
        $this->authorize('view', $assessment);

        try {
            $pdf = $this->pdfService->generateAssessmentPDF($assessment);

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline');
        } catch (\Exception $e) {
            return redirect()
                ->route('assessments.show', $assessment)
                ->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generate assessment code.
     */
    protected function generateAssessmentCode(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $companyId = auth()->user()->company_id;

            // Get or create number sequence for assessments
            $sequence = NumberSequence::firstOrCreate([
                'company_id' => $companyId,
                'type' => NumberSequence::TYPE_ASSESSMENT,
                'year' => $year,
            ], [
                'prefix' => 'ASS',
                'current_number' => 0,
                'padding' => 6,
                'format' => '{prefix}-{year}-{number}',
                'yearly_reset' => true,
            ]);

            // Check if year has changed and reset if needed
            if ($sequence->yearly_reset && $sequence->year !== $year) {
                $sequence->update([
                    'year' => $year,
                    'current_number' => 0,
                ]);
            }

            // Increment and get next number
            $sequence->increment('current_number');
            $nextNumber = str_pad($sequence->current_number, $sequence->padding, '0', STR_PAD_LEFT);

            // Update last generated info
            $sequence->update([
                'last_generated_at' => now(),
                'last_generated_number' => $nextNumber,
            ]);

            return str_replace(
                ['{prefix}', '{year}', '{number}'],
                [$sequence->prefix, $year, $nextNumber],
                $sequence->format
            );
        });
    }

    /**
     * API endpoint to get assessment templates by service type.
     */
    public function getTemplatesByServiceType(Request $request): JsonResponse
    {
        $request->validate([
            'service_type' => 'required|in:waterproofing,painting,sports_court,industrial',
        ]);

        $templates = ServiceAssessmentTemplate::forCompany()
            ->active()
            ->approved()
            ->forServiceType($request->service_type)
            ->get(['id', 'template_name', 'template_description', 'scoring_method', 'passing_score']);

        return response()->json($templates);
    }
}