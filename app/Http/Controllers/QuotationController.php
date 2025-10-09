<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationSection;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Team;
use App\Models\User;
use App\Models\CustomerSegment;
use App\Services\PDFService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class QuotationController extends Controller
{

    /**
     * Display a listing of quotations.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quotation::class);

        $query = Quotation::forCompany()
            ->forUserTeams()
            ->with(['lead', 'team', 'assignedTo', 'createdBy', 'customerSegment']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('customer_segment_id')) {
            $query->where('customer_segment_id', $request->customer_segment_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $quotations = $query->paginate(20)->withQueryString();

        // Get filter options
        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator', 'sales_manager']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $customerSegments = CustomerSegment::forCompany()
            ->active()
            ->select('id', 'name', 'color')
            ->orderBy('name')
            ->get();

        $filters = [
            'statuses' => Quotation::getStatuses(),
            'types' => Quotation::getTypes(),
            'teams' => $teams,
            'assignees' => $assignees,
            'customer_segments' => $customerSegments,
        ];

        return view('quotations.index', compact('quotations', 'filters'));
    }

    /**
     * Display product quotations only.
     */
    public function productIndex(Request $request): View
    {
        $this->authorize('viewAny', Quotation::class);

        $query = Quotation::forCompany()
            ->forUserTeams()
            ->where('type', 'product')
            ->with(['lead', 'team', 'assignedTo', 'createdBy', 'customerSegment']);

        // Apply filters (same as index)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('customer_segment_id')) {
            $query->where('customer_segment_id', $request->customer_segment_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $quotations = $query->paginate(20)->withQueryString();

        // Get filter options
        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator', 'sales_manager']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $customerSegments = CustomerSegment::forCompany()
            ->active()
            ->select('id', 'name', 'color')
            ->orderBy('name')
            ->get();

        $filters = [
            'statuses' => Quotation::getStatuses(),
            'teams' => $teams,
            'assignees' => $assignees,
            'customer_segments' => $customerSegments,
        ];

        return view('quotations.product-index', compact('quotations', 'filters'));
    }

    /**
     * Display service quotations only.
     */
    public function serviceIndex(Request $request): View
    {
        $this->authorize('viewAny', Quotation::class);

        $query = Quotation::forCompany()
            ->forUserTeams()
            ->where('type', 'service')
            ->with(['lead', 'team', 'assignedTo', 'createdBy', 'customerSegment']);

        // Apply filters (same as index)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('team_id')) {
            $query->where('team_id', $request->team_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('customer_segment_id')) {
            $query->where('customer_segment_id', $request->customer_segment_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $quotations = $query->paginate(20)->withQueryString();

        // Get filter options
        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator', 'sales_manager']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $customerSegments = CustomerSegment::forCompany()
            ->active()
            ->select('id', 'name', 'color')
            ->orderBy('name')
            ->get();

        $filters = [
            'statuses' => Quotation::getStatuses(),
            'teams' => $teams,
            'assignees' => $assignees,
            'customer_segments' => $customerSegments,
        ];

        return view('quotations.service-index', compact('quotations', 'filters'));
    }

    /**
     * Show the form for creating a new quotation.
     *
     * Redirects to the appropriate builder based on feature flag.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Quotation::class);

        // Check if enhanced builder is enabled
        if (config('features.quotation_builder_v2', false)) {
            // Default to product builder
            return redirect()->route('quotations.create.products', $request->only(['lead_id']));
        }

        // Fallback to existing create form if feature flag is disabled
        $lead = null;
        if ($request->filled('lead_id')) {
            $lead = Lead::forCompany()->findOrFail($request->lead_id);
            $this->authorize('view', $lead);
        }

        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $customerSegments = CustomerSegment::forCompany()
            ->active()
            ->select('id', 'name', 'color', 'default_discount_percentage')
            ->orderBy('name')
            ->get();

        $companyBrands = \App\Models\CompanyBrand::forCompany()
            ->active()
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('quotations.create', compact('lead', 'teams', 'assignees', 'customerSegments', 'companyBrands'));
    }

    /**
     * Store a newly created quotation in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Quotation::class);

        $validated = $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(Quotation::getTypes())),
            'lead_id' => 'nullable|exists:leads,id',
            'customer_segment_id' => 'nullable|exists:customer_segments,id',
            'team_id' => 'nullable|exists:teams,id',
            'assigned_to' => 'nullable|exists:users,id',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'customer_address' => 'nullable|string',
            'customer_city' => 'nullable|string|max:100',
            'customer_state' => 'nullable|string|max:100',
            'customer_postal_code' => 'nullable|string|max:20',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'valid_until' => 'nullable|date|after:today',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.unit' => 'required|string|max:20',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.item_code' => 'nullable|string|max:50',
            'items.*.specifications' => 'nullable|string',
            'items.*.notes' => 'nullable|string',
            'sections' => 'nullable|array',
            'sections.*.name' => 'required_with:sections|string|max:200',
            'sections.*.description' => 'nullable|string',
        ]);

        // Add company_id and other defaults
        $validated['company_id'] = auth()->user()->company_id;
        $validated['created_by'] = auth()->id();

        $quotation = Quotation::create($validated);

        // Create sections if provided (for service quotations)
        if (!empty($validated['sections'])) {
            foreach ($validated['sections'] as $index => $sectionData) {
                $section = QuotationSection::create([
                    'quotation_id' => $quotation->id,
                    'name' => $sectionData['name'],
                    'description' => $sectionData['description'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        }

        // Create items
        foreach ($validated['items'] as $index => $itemData) {
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'description' => $itemData['description'],
                'unit' => $itemData['unit'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'item_code' => $itemData['item_code'] ?? null,
                'specifications' => $itemData['specifications'] ?? null,
                'notes' => $itemData['notes'] ?? null,
                'sort_order' => $index,
            ]);
        }

        // Calculate totals
        $quotation->fresh()->calculateTotals();
        $quotation->save();

        // Update lead status if quotation was created from a lead
        if ($quotation->lead_id) {
            $lead = Lead::find($quotation->lead_id);
            if ($lead) {
                $lead->markAsQuoted();

                // Create lead activity
                LeadActivity::create([
                    'company_id' => $lead->company_id,
                    'lead_id' => $lead->id,
                    'user_id' => auth()->id(),
                    'type' => 'quotation_created',
                    'title' => 'Quotation Created',
                    'description' => "Quotation #{$quotation->number} created from this lead",
                    'metadata' => [
                        'quotation_id' => $quotation->id,
                        'quotation_number' => $quotation->number,
                        'quotation_total' => $quotation->total,
                    ],
                ]);

                // Track quote amount for price war detection
                if (config('lead_tracking.enabled') && config('lead_tracking.contact_tracking.track_quote_amounts')) {
                    $lead->recordContact(auth()->user(), $quotation->total);
                }
            }
        }

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation created successfully.');
    }

    /**
     * Store a new quotation via API (for quotation builder)
     */
    public function storeApi(Request $request): JsonResponse
    {
        $this->authorize('create', Quotation::class);

        try {
            $type = $request->input('type', Quotation::TYPE_PRODUCT);

            $validated = $request->validate([
                'type' => ['nullable', Rule::in(array_keys(Quotation::getTypes()))],
                'lead_id' => 'nullable|exists:leads,id',
                'team_id' => 'nullable|exists:teams,id',
                'assigned_to' => 'nullable|exists:users,id',
                'customer_segment_id' => 'nullable|exists:customer_segments,id',
                'company_logo_id' => 'nullable|integer',
                'customer_name' => 'required|string|max:100',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => 'nullable|email|max:100',
                'customer_address' => 'nullable|string',
                'customer_city' => 'nullable|string|max:100',
                'customer_state' => 'nullable|string|max:100',
                'customer_postal_code' => 'nullable|string|max:20',
                'customer_company' => 'nullable|string|max:150',
                'title' => 'nullable|string|max:150',
                'description' => 'nullable|string',
                'terms_conditions' => 'nullable|string',
                'notes' => 'nullable|string',
                'payment_instructions' => 'nullable|string',
                'optional_sections' => 'nullable|array',
                'shipping_info' => 'nullable|array',
                'quotation_date' => 'required|date',
                'valid_until' => 'nullable|date|after_or_equal:quotation_date',
                'validity_period' => 'nullable|integer|min:1|max:365',
                'reference_number' => 'nullable|string|max:100',
                'tax_percentage' => 'nullable|numeric|min:0|max:100',
                'discount_percentage' => 'nullable|numeric|min:0|max:100',
                'status' => 'nullable|in:DRAFT,SENT',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string|max:500',
                'items.*.unit' => 'nullable|string|max:20',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.item_code' => 'nullable|string|max:100',
                'items.*.specifications' => 'nullable|string',
                'items.*.notes' => 'nullable|string',
                'items.*.source_type' => 'nullable|string|in:pricing_item,service_template_item,manual',
                'items.*.source_id' => 'nullable|integer',
                // Service quotation specific fields
                'sections' => 'nullable|array',
                'sections.*.name' => 'required_with:sections|string|max:200',
                'sections.*.description' => 'nullable|string',
                'sections.*.sort_order' => 'nullable|integer',
                'sections.*.items' => 'nullable|array',
                'sections.*.items.*.description' => 'required|string|max:500',
                'sections.*.items.*.unit' => 'nullable|string|max:20',
                'sections.*.items.*.quantity' => 'required|numeric|min:0.01',
                'sections.*.items.*.unit_price' => 'required|numeric|min:0',
                'sections.*.items.*.item_code' => 'nullable|string|max:100',
                'sections.*.items.*.specifications' => 'nullable|string',
                'sections.*.items.*.notes' => 'nullable|string',
            ]);

            $validated['type'] = $type;

            // Calculate validity period or valid_until if not provided
            if (!isset($validated['valid_until']) && isset($validated['validity_period'])) {
                $quotationDate = new \DateTime($validated['quotation_date']);
                $quotationDate->modify("+{$validated['validity_period']} days");
                $validated['valid_until'] = $quotationDate->format('Y-m-d');
            }

            $validated['company_id'] = auth()->user()->company_id;
            $validated['created_by'] = auth()->id();
            $validated['status'] = $validated['status'] ?? Quotation::STATUS_DRAFT;

            // Automatic lead creation/matching
            if (!isset($validated['lead_id']) || empty($validated['lead_id'])) {
                // Find or create lead from customer data to ensure CRM tracking
                $lead = Lead::findOrCreateFromCustomerData($validated, Lead::SOURCE_QUOTATION_BUILDER);
                $validated['lead_id'] = $lead->id;

                // Inherit team and assignment from lead if not specified
                $validated['team_id'] = $validated['team_id'] ?? $lead->team_id;
                $validated['assigned_to'] = $validated['assigned_to'] ?? $lead->assigned_to;
            }

            $items = $validated['items'] ?? [];
            $sections = $validated['sections'] ?? [];
            unset($validated['items'], $validated['sections'], $validated['validity_period']);

            $quotation = Quotation::create($validated);

            // Handle service quotations with sections
            if ($type === Quotation::TYPE_SERVICE && !empty($sections)) {
                foreach ($sections as $sectionIndex => $sectionData) {
                    $section = QuotationSection::create([
                        'quotation_id' => $quotation->id,
                        'name' => $sectionData['name'],
                        'description' => $sectionData['description'] ?? null,
                        'sort_order' => $sectionData['sort_order'] ?? $sectionIndex,
                    ]);

                    // Create items for this section
                    if (!empty($sectionData['items'])) {
                        foreach ($sectionData['items'] as $itemIndex => $item) {
                            QuotationItem::create([
                                'quotation_id' => $quotation->id,
                                'quotation_section_id' => $section->id,
                                'description' => $item['description'],
                                'unit' => $item['unit'] ?? 'pcs',
                                'quantity' => $item['quantity'],
                                'unit_price' => $item['unit_price'],
                                'item_code' => $item['item_code'] ?? null,
                                'specifications' => $item['specifications'] ?? null,
                                'notes' => $item['notes'] ?? null,
                                'sort_order' => $itemIndex,
                            ]);
                        }
                    }
                }
            }
            // Handle product quotations with simple items
            else {
                foreach ($items as $index => $item) {
                    QuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'description' => $item['description'],
                        'unit' => $item['unit'] ?? 'pcs',
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'item_code' => $item['item_code'] ?? null,
                        'specifications' => $item['specifications'] ?? null,
                        'notes' => $item['notes'] ?? null,
                        'source_type' => $item['source_type'] ?? null,
                        'source_id' => $item['source_id'] ?? null,
                        'sort_order' => $index,
                    ]);
                }
            }

            $quotation->fresh()->calculateTotals();
            $quotation->save();

            // Update lead status if quotation was created from a lead
            if ($quotation->lead_id) {
                $lead = Lead::find($quotation->lead_id);
                if ($lead) {
                    $lead->markAsQuoted();

                    // Create lead activity
                    LeadActivity::create([
                        'company_id' => $lead->company_id,
                        'lead_id' => $lead->id,
                        'user_id' => auth()->id(),
                        'type' => 'quotation_created',
                        'title' => 'Quotation Created',
                        'description' => "Quotation #{$quotation->number} created from this lead",
                        'metadata' => [
                            'quotation_id' => $quotation->id,
                            'quotation_number' => $quotation->number,
                            'quotation_total' => $quotation->total,
                        ],
                    ]);

                    // Track quote amount for price war detection
                    if (config('lead_tracking.enabled') && config('lead_tracking.contact_tracking.track_quote_amounts')) {
                        $lead->recordContact(auth()->user(), $quotation->total);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Quotation created successfully.',
                'quotation' => [
                    'id' => $quotation->id,
                    'number' => $quotation->number,
                    'status' => $quotation->status,
                    'total' => $quotation->total,
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Quotation creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create quotation. Please try again.'
            ], 500);
        }
    }

    /**
     * Update quotation via API (for builder/edit interface).
     */
    public function updateApi(Request $request, Quotation $quotation): JsonResponse
    {
        $this->authorize('update', $quotation);

        // Check if quotation can be edited
        if (!$quotation->canBeEdited()) {
            return response()->json([
                'success' => false,
                'message' => 'This quotation cannot be edited in its current status.'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'customer_segment_id' => 'nullable|exists:customer_segments,id',
                'customer_name' => 'required|string|max:100',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => 'nullable|email|max:100',
                'customer_address' => 'nullable|string',
                'customer_city' => 'nullable|string|max:100',
                'customer_state' => 'nullable|string|max:100',
                'customer_postal_code' => 'nullable|string|max:20',
                'customer_company' => 'nullable|string|max:150',
                'title' => 'nullable|string|max:150',
                'description' => 'nullable|string',
                'terms_conditions' => 'nullable|string',
                'notes' => 'nullable|string',
                'quotation_date' => 'required|date',
                'valid_until' => 'nullable|date|after_or_equal:quotation_date',
                'validity_period' => 'nullable|integer|min:1|max:365',
                'reference_number' => 'nullable|string|max:100',
                'tax_percentage' => 'nullable|numeric|min:0|max:100',
                'discount_percentage' => 'nullable|numeric|min:0|max:100',
                'discount_amount' => 'nullable|numeric|min:0',
                'items' => 'required|array|min:1',
                'items.*.description' => 'required|string|max:500',
                'items.*.unit' => 'nullable|string|max:20',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.item_code' => 'nullable|string|max:100',
                'items.*.specifications' => 'nullable|string',
                'items.*.notes' => 'nullable|string',
                'items.*.source_type' => 'nullable|string|in:pricing_item,service_template_item,manual',
                'items.*.source_id' => 'nullable|integer',
                // Service quotation specific fields
                'sections' => 'nullable|array',
                'sections.*.name' => 'required_with:sections|string|max:200',
                'sections.*.description' => 'nullable|string',
                'sections.*.sort_order' => 'nullable|integer',
                'sections.*.items' => 'nullable|array',
                'sections.*.items.*.description' => 'required|string|max:500',
                'sections.*.items.*.unit' => 'nullable|string|max:20',
                'sections.*.items.*.quantity' => 'required|numeric|min:0.01',
                'sections.*.items.*.unit_price' => 'required|numeric|min:0',
                'sections.*.items.*.item_code' => 'nullable|string|max:100',
                'sections.*.items.*.specifications' => 'nullable|string',
                'sections.*.items.*.notes' => 'nullable|string',
            ]);

            // Calculate validity period or valid_until if not provided
            if (!isset($validated['valid_until']) && isset($validated['validity_period'])) {
                $quotationDate = new \DateTime($validated['quotation_date']);
                $quotationDate->modify("+{$validated['validity_period']} days");
                $validated['valid_until'] = $quotationDate->format('Y-m-d');
            }

            // Update quotation data
            $items = $validated['items'] ?? [];
            $sections = $validated['sections'] ?? [];
            unset($validated['items'], $validated['sections'], $validated['validity_period']);

            // Handle lead relationship updates when customer info changes
            if ($quotation->lead_id) {
                $lead = $quotation->lead;

                // If phone number changed, find or create different lead
                if ($validated['customer_phone'] !== $lead->phone) {
                    $newLead = Lead::findOrCreateFromCustomerData($validated, Lead::SOURCE_QUOTATION_BUILDER);
                    $validated['lead_id'] = $newLead->id;
                } else {
                    // Update existing lead with latest information
                    if (in_array($lead->status, [Lead::STATUS_NEW, Lead::STATUS_CONTACTED, Lead::STATUS_QUOTED])) {
                        $lead->update([
                            'name' => $validated['customer_name'],
                            'email' => $validated['customer_email'] ?? $lead->email,
                            'address' => $validated['customer_address'] ?? $lead->address,
                            'city' => $validated['customer_city'] ?? $lead->city,
                            'state' => $validated['customer_state'] ?? $lead->state,
                            'postal_code' => $validated['customer_postal_code'] ?? $lead->postal_code,
                            'last_contacted_at' => now(),
                        ]);
                    }
                }
            } else {
                // If quotation doesn't have a lead yet, create one
                $lead = Lead::findOrCreateFromCustomerData($validated, Lead::SOURCE_QUOTATION_BUILDER);
                $validated['lead_id'] = $lead->id;
            }

            $quotation->update($validated);

            // Delete existing sections and items
            $quotation->sections()->delete();
            $quotation->items()->delete();

            // Handle service quotations with sections
            if ($quotation->type === Quotation::TYPE_SERVICE && !empty($sections)) {
                foreach ($sections as $sectionIndex => $sectionData) {
                    $section = QuotationSection::create([
                        'quotation_id' => $quotation->id,
                        'name' => $sectionData['name'],
                        'description' => $sectionData['description'] ?? null,
                        'sort_order' => $sectionData['sort_order'] ?? $sectionIndex,
                    ]);

                    // Create items for this section
                    if (!empty($sectionData['items'])) {
                        foreach ($sectionData['items'] as $itemIndex => $item) {
                            QuotationItem::create([
                                'quotation_id' => $quotation->id,
                                'quotation_section_id' => $section->id,
                                'description' => $item['description'],
                                'unit' => $item['unit'] ?? 'pcs',
                                'quantity' => $item['quantity'],
                                'unit_price' => $item['unit_price'],
                                'item_code' => $item['item_code'] ?? null,
                                'specifications' => $item['specifications'] ?? null,
                                'notes' => $item['notes'] ?? null,
                                'sort_order' => $itemIndex,
                            ]);
                        }
                    }
                }
            }
            // Handle product quotations with simple items
            else {
                foreach ($items as $index => $item) {
                    QuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'description' => $item['description'],
                        'unit' => $item['unit'] ?? 'pcs',
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'item_code' => $item['item_code'] ?? null,
                        'specifications' => $item['specifications'] ?? null,
                        'notes' => $item['notes'] ?? null,
                        'source_type' => $item['source_type'] ?? null,
                        'source_id' => $item['source_id'] ?? null,
                        'sort_order' => $index,
                    ]);
                }
            }

            // Recalculate totals
            $quotation->fresh()->calculateTotals();
            $quotation->save();

            return response()->json([
                'success' => true,
                'message' => 'Quotation updated successfully.',
                'quotation' => [
                    'id' => $quotation->id,
                    'number' => $quotation->number,
                    'status' => $quotation->status,
                    'total' => $quotation->total,
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Quotation update failed', [
                'error' => $e->getMessage(),
                'quotation_id' => $quotation->id,
                'user_id' => auth()->id(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update quotation. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified quotation.
     */
    public function show(Quotation $quotation): View
    {
        $this->authorize('view', $quotation);

        $quotation->load([
            'lead',
            'team',
            'assignedTo',
            'createdBy',
            'customerSegment',
            'items',
            'sections.items'
        ]);

        return view('quotations.show', compact('quotation'));
    }

    /**
     * Show the product quotation builder interface.
     */
    public function productBuilder(Request $request): View
    {
        $this->authorize('create', Quotation::class);

        // Load default templates (quotations may use similar templates to invoices)
        $defaultTemplates = [
            'notes' => \App\Models\InvoiceNoteTemplate::getDefaultForType('notes'),
            'terms' => \App\Models\InvoiceNoteTemplate::getDefaultForType('terms'),
            'payment_instructions' => \App\Models\InvoiceNoteTemplate::getDefaultForType('payment_instructions'),
        ];

        // Get customer segments for pricing
        $customerSegments = CustomerSegment::forCompany()
            ->active()
            ->orderBy('name')
            ->get();

        return view('quotations.product-builder', compact('defaultTemplates', 'customerSegments'));
    }

    /**
     * Show the service quotation builder interface.
     */
    public function serviceBuilder(Request $request): View
    {
        $this->authorize('create', Quotation::class);

        // Load default templates (quotations may use similar templates to invoices)
        $defaultTemplates = [
            'notes' => \App\Models\InvoiceNoteTemplate::getDefaultForType('notes'),
            'terms' => \App\Models\InvoiceNoteTemplate::getDefaultForType('terms'),
            'payment_instructions' => \App\Models\InvoiceNoteTemplate::getDefaultForType('payment_instructions'),
        ];

        // Get customer segments for pricing
        $customerSegments = CustomerSegment::forCompany()
            ->active()
            ->orderBy('name')
            ->get();

        // Get company brands for letterhead selection
        $companyBrands = \App\Models\CompanyBrand::forCompany()
            ->active()
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('quotations.service-builder', compact('defaultTemplates', 'customerSegments', 'companyBrands'));
    }

    /**
     * Show the form for editing the specified quotation.
     */
    public function edit(Quotation $quotation): View
    {
        $this->authorize('update', $quotation);

        if (!$quotation->canBeEdited()) {
            return redirect()->route('quotations.show', $quotation)
                ->with('error', 'This quotation cannot be edited in its current status.');
        }

        $quotation->load(['items', 'sections.items']);

        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $customerSegments = CustomerSegment::forCompany()
            ->active()
            ->select('id', 'name', 'color', 'default_discount_percentage')
            ->orderBy('name')
            ->get();

        $companyBrands = \App\Models\CompanyBrand::forCompany()
            ->active()
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return view('quotations.edit', compact('quotation', 'teams', 'assignees', 'customerSegments', 'companyBrands'));
    }

    /**
     * Update the specified quotation in storage.
     */
    public function update(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('update', $quotation);

        if (!$quotation->canBeEdited()) {
            return redirect()->route('quotations.show', $quotation)
                ->with('error', 'This quotation cannot be edited in its current status.');
        }

        $validated = $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys(Quotation::getTypes())),
            'customer_segment_id' => 'nullable|exists:customer_segments,id',
            'team_id' => 'nullable|exists:teams,id',
            'assigned_to' => 'nullable|exists:users,id',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'customer_address' => 'nullable|string',
            'customer_city' => 'nullable|string|max:100',
            'customer_state' => 'nullable|string|max:100',
            'customer_postal_code' => 'nullable|string|max:20',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'valid_until' => 'nullable|date|after:today',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'internal_notes' => 'nullable|string',
        ]);

        $quotation->update($validated);

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation updated successfully.');
    }

    /**
     * Remove the specified quotation from storage.
     */
    public function destroy(Quotation $quotation): RedirectResponse
    {
        $this->authorize('delete', $quotation);

        if (!$quotation->canBeEdited()) {
            return redirect()->route('quotations.show', $quotation)
                ->with('error', 'This quotation cannot be deleted in its current status.');
        }

        $quotation->delete();

        return redirect()->route('quotations.index')
            ->with('success', 'Quotation deleted successfully.');
    }

    /**
     * Mark quotation as sent.
     */
    public function markAsSent(Quotation $quotation): RedirectResponse
    {
        $this->authorize('update', $quotation);

        if (!$quotation->canBeSent()) {
            return redirect()->route('quotations.show', $quotation)
                ->with('error', 'This quotation cannot be sent in its current status.');
        }

        $quotation->markAsSent();

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation marked as sent.');
    }

    /**
     * Mark quotation as accepted.
     */
    public function markAsAccepted(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('update', $quotation);

        if (!$quotation->canBeAccepted()) {
            return redirect()->route('quotations.show', $quotation)
                ->with('error', 'This quotation cannot be accepted in its current status.');
        }

        $quotation->markAsAccepted();

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation marked as accepted.');
    }

    /**
     * Mark quotation as rejected.
     */
    public function markAsRejected(Request $request, Quotation $quotation): RedirectResponse
    {
        $this->authorize('update', $quotation);

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:255',
        ]);

        $quotation->markAsRejected($validated['rejection_reason'] ?? null);

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation marked as rejected.');
    }

    /**
     * Create quotation from lead.
     */
    public function createFromLead(Lead $lead): RedirectResponse
    {
        $this->authorize('view', $lead);
        $this->authorize('create', Quotation::class);

        return redirect()->route('quotations.create', ['lead_id' => $lead->id]);
    }

    /**
     * Download PDF for quotation.
     */
    public function downloadPDF(Quotation $quotation, PDFService $pdfService)
    {
        $this->authorize('view', $quotation);

        try {
            return $pdfService->downloadPDF($quotation);
        } catch (\Exception $e) {
            return redirect()->route('quotations.show', $quotation)
                ->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Preview PDF for quotation.
     */
    public function previewPDF(Quotation $quotation, PDFService $pdfService)
    {
        $this->authorize('view', $quotation);

        try {
            return $pdfService->streamPDF($quotation);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Quotation PDF Preview Failed', [
                'quotation_id' => $quotation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return error page directly instead of redirecting
            return response()->view('errors.pdf-generation', [
                'error' => $e->getMessage(),
                'quotation' => $quotation
            ], 500);
        }
    }

    /**
     * Get pricing items for AJAX integration in quotation forms
     */
    public function getPricingItems(Request $request)
    {
        $term = $request->get('q', '');
        
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $items = \App\Models\PricingItem::search($term)
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'category_name' => $item->category->name ?? '',
                    'category_path' => $item->getCategoryPath(),
                    'image_url' => $item->getImageUrl(),
                    'specifications' => $item->specifications,
                    'quotation_data' => $item->toQuotationItemData(),
                ];
            });

        return response()->json($items);
    }

    /**
     * Get segment pricing for an item with specific quantity (AJAX endpoint)
     */
    public function getSegmentPricing(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|exists:pricing_items,id',
            'segment_id' => 'required|exists:customer_segments,id',
            'quantity' => 'required|numeric|min:1',
        ]);

        $item = \App\Models\PricingItem::findOrFail($request->item_id);
        $segment = CustomerSegment::findOrFail($request->segment_id);
        $quantity = (int) $request->quantity;

        $pricing = $item->getPriceForSegment($request->segment_id, $quantity);
        
        return response()->json([
            'success' => true,
            'pricing' => $pricing,
            'segment' => [
                'id' => $segment->id,
                'name' => $segment->name,
                'color' => $segment->color,
                'default_discount_percentage' => $segment->default_discount_percentage,
            ]
        ]);
    }

    /**
     * Show the product quotation creation form.
     */
    public function createProduct(Request $request): View
    {
        $this->authorize('create', Quotation::class);

        $lead = null;
        if ($request->filled('lead_id')) {
            $lead = Lead::forCompany()->findOrFail($request->lead_id);
            $this->authorize('view', $lead);
        }

        $formData = $this->buildQuotationFormPayload($lead);

        return view('quotations.create-product', array_merge($formData, ['lead' => $lead]));
    }

    /**
     * Show the service quotation creation form.
     */
    public function createService(Request $request): View
    {
        $this->authorize('create', Quotation::class);

        $lead = null;
        if ($request->filled('lead_id')) {
            $lead = Lead::forCompany()->findOrFail($request->lead_id);
            $this->authorize('view', $lead);
        }

        $formData = $this->buildQuotationFormPayload($lead);

        return view('quotations.create-service', array_merge($formData, ['lead' => $lead]));
    }

    /**
     * Build common form payload for quotation creation.
     */
    private function buildQuotationFormPayload(?Lead $lead = null): array
    {
        // Get teams for assignment
        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get assignees (sales staff)
        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get customer segments for pricing
        $customerSegments = CustomerSegment::forCompany()
            ->active()
            ->select('id', 'name', 'color', 'default_discount_percentage')
            ->orderBy('name')
            ->get();

        // Get next quotation number preview
        $nextNumber = Quotation::generateNumber();

        // Get document defaults from company settings
        $company = auth()->user()->company;
        $documentDefaults = $company->settings['document'] ?? [];

        // Get recent leads for client shortlist (if not converting from lead)
        $recentClients = collect();
        if (!$lead) {
            $recentLeads = Lead::forCompany()
                ->select('customer_name', 'customer_email', 'customer_phone')
                ->whereNotNull('customer_name')
                ->orderBy('created_at', 'desc')
                ->limit(15)
                ->get()
                ->map(function ($lead) {
                    return [
                        'name' => $lead->customer_name,
                        'email' => $lead->customer_email,
                        'phone' => $lead->customer_phone,
                        'source' => 'lead'
                    ];
                });

            $recentClients = $recentLeads;
        }

        return [
            'teams' => $teams,
            'assignees' => $assignees,
            'customerSegments' => $customerSegments,
            'nextNumber' => $nextNumber,
            'documentDefaults' => $documentDefaults,
            'recentClients' => $recentClients,
        ];
    }

    /**
     * Unified search for both customers and leads (for repeat customer detection).
     * Searches customers table first (paid customers), then leads table.
     * Deduplicates by phone number, prioritizing customers over leads.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchCustomersAndLeads(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $searchTerm = $request->q;
        $limit = $request->limit ?: 10;
        $results = [];
        $seenPhones = []; // Track phone numbers to prevent duplicates

        // 1. Search Customers table first (prioritize paying customers)
        $customers = \App\Models\Customer::forCompany()
            ->active()
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('company_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('city', 'LIKE', "%{$searchTerm}%");
            })
            ->with(['customerSegment'])
            ->limit($limit)
            ->get();

        foreach ($customers as $customer) {
            $phone = $this->normalizePhone($customer->phone);

            if (!in_array($phone, $seenPhones)) {
                $seenPhones[] = $phone;
                $results[] = [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'company_name' => $customer->company_name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'city' => $customer->city,
                    'state' => $customer->state,
                    'postal_code' => $customer->postal_code,
                    'customer_segment' => $customer->customerSegment?->name,
                    'customer_segment_id' => $customer->customer_segment_id,
                    'is_new_customer' => $customer->is_new_customer,
                    'source' => 'customer', // Indicates this is from customers table
                    'has_purchase_history' => true,
                    'badge' => [
                        'text' => 'Customer',
                        'class' => 'bg-green-100 text-green-800',
                    ],
                ];
            }
        }

        // 2. Search Leads table (for leads that haven't become customers yet)
        // Only if we haven't reached the limit
        if (count($results) < $limit) {
            $remainingLimit = $limit - count($results);

            $leads = Lead::forCompany()
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('company', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('city', 'LIKE', "%{$searchTerm}%");
                })
                ->whereNotIn('status', ['CONVERTED']) // Exclude converted leads (they should be customers)
                ->limit($remainingLimit)
                ->get();

            foreach ($leads as $lead) {
                $phone = $this->normalizePhone($lead->phone);

                // Only add if we haven't seen this phone number in customers
                if (!in_array($phone, $seenPhones)) {
                    $seenPhones[] = $phone;
                    $results[] = [
                        'id' => $lead->id,
                        'name' => $lead->name,
                        'company_name' => $lead->company,
                        'phone' => $lead->phone,
                        'email' => $lead->email,
                        'address' => $lead->address,
                        'city' => $lead->city,
                        'state' => $lead->state,
                        'postal_code' => $lead->postal_code,
                        'customer_segment' => null, // Leads don't have segments yet
                        'customer_segment_id' => null,
                        'is_new_customer' => true,
                        'source' => 'lead', // Indicates this is from leads table
                        'lead_id' => $lead->id, // Include lead_id for linking
                        'lead_status' => $lead->status,
                        'has_purchase_history' => false,
                        'badge' => [
                            'text' => 'Lead',
                            'class' => 'bg-blue-100 text-blue-800',
                        ],
                    ];
                }
            }
        }

        // 3. Search Quotations table (for quotations without leads or customers)
        // This catches standalone quotations that haven't been linked
        if (count($results) < $limit) {
            $remainingLimit = $limit - count($results);

            $quotations = Quotation::forCompany()
                ->where(function ($q) use ($searchTerm) {
                    $q->where('customer_name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('customer_phone', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('customer_email', 'LIKE', "%{$searchTerm}%");
                })
                ->whereNull('lead_id') // Only quotations without leads
                ->select('customer_name', 'customer_phone', 'customer_email', 'customer_address',
                         'customer_city', 'customer_state', 'customer_postal_code')
                ->distinct()
                ->limit($remainingLimit)
                ->get();

            foreach ($quotations as $quotation) {
                $phone = $this->normalizePhone($quotation->customer_phone);

                // Only add if we haven't seen this phone number
                if (!in_array($phone, $seenPhones) && $quotation->customer_phone) {
                    $seenPhones[] = $phone;
                    $results[] = [
                        'id' => null,
                        'name' => $quotation->customer_name,
                        'company_name' => null,
                        'phone' => $quotation->customer_phone,
                        'email' => $quotation->customer_email,
                        'address' => $quotation->customer_address,
                        'city' => $quotation->customer_city,
                        'state' => $quotation->customer_state,
                        'postal_code' => $quotation->customer_postal_code,
                        'customer_segment' => null,
                        'customer_segment_id' => null,
                        'is_new_customer' => true,
                        'source' => 'quotation', // Indicates this is from quotations
                        'has_purchase_history' => false,
                        'badge' => [
                            'text' => 'Previous Quote',
                            'class' => 'bg-yellow-100 text-yellow-800',
                        ],
                    ];
                }
            }
        }

        return response()->json([
            'customers' => $results,
            'count' => count($results),
            'sources' => [
                'customers' => count(array_filter($results, fn($r) => $r['source'] === 'customer')),
                'leads' => count(array_filter($results, fn($r) => $r['source'] === 'lead')),
                'quotations' => count(array_filter($results, fn($r) => $r['source'] === 'quotation')),
            ],
        ]);
    }

    /**
     * Normalize phone number for comparison (remove formatting).
     *
     * @param string|null $phone
     * @return string
     */
    private function normalizePhone(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        // Remove all non-numeric characters
        return preg_replace('/\D/', '', $phone);
    }
}
