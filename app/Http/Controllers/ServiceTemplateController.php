<?php

namespace App\Http\Controllers;

use App\Models\ServiceTemplate;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ServiceTemplate::query()
            ->forCompany()
            ->forUserTeams()
            ->with(['company', 'createdBy', 'sections.items']);

        // Apply filters
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        $allowedSorts = ['name', 'category', 'usage_count', 'last_used_at', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }

        $templates = $query->paginate(20)->withQueryString();

        // Get categories for filter dropdown
        $categories = ServiceTemplate::getCategories();
        
        // Get teams for the current user
        $availableTeams = Team::forCompany()->get();

        return view('service-templates.index', compact(
            'templates', 
            'categories', 
            'availableTeams'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ServiceTemplate::getCategories();
        $teams = Team::forCompany()->get();

        return view('service-templates.create', compact('categories', 'teams'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'category' => ['nullable', Rule::in(array_keys(ServiceTemplate::getCategories()))],
            'applicable_teams' => 'nullable|array',
            'applicable_teams.*' => 'exists:teams,id',
            'estimated_hours' => 'nullable|numeric|min:0|max:9999.99',
            'base_price' => 'nullable|numeric|min:0|max:99999999.99',
            'requires_approval' => 'boolean',
            'sections' => 'required|array|min:1',
            'sections.*.name' => 'required|string|max:200',
            'sections.*.description' => 'nullable|string',
            'sections.*.default_discount_percentage' => 'numeric|min:0|max:100',
            'sections.*.sort_order' => 'integer|min:0',
            'sections.*.is_required' => 'boolean',
            'sections.*.estimated_hours' => 'nullable|numeric|min:0|max:9999.99',
            'sections.*.instructions' => 'nullable|string',
            'sections.*.items' => 'required|array|min:1',
            'sections.*.items.*.description' => 'required|string|max:500',
            'sections.*.items.*.unit' => 'string|max:20',
            'sections.*.items.*.default_quantity' => 'required|numeric|min:0.01|max:999999.99',
            'sections.*.items.*.default_unit_price' => 'required|numeric|min:0|max:99999999.99',
            'sections.*.items.*.item_code' => 'nullable|string|max:50',
            'sections.*.items.*.specifications' => 'nullable|string',
            'sections.*.items.*.notes' => 'nullable|string',
            'sections.*.items.*.sort_order' => 'integer|min:0',
            'sections.*.items.*.is_required' => 'boolean',
            'sections.*.items.*.quantity_editable' => 'boolean',
            'sections.*.items.*.price_editable' => 'boolean',
            'sections.*.items.*.cost_price' => 'nullable|numeric|min:0|max:99999999.99',
            'sections.*.items.*.minimum_price' => 'nullable|numeric|min:0|max:99999999.99',
        ]);

        try {
            DB::beginTransaction();

            // Create the template
            $template = ServiceTemplate::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'category' => $validated['category'],
                'applicable_teams' => $validated['applicable_teams'] ?? null,
                'estimated_hours' => $validated['estimated_hours'],
                'base_price' => $validated['base_price'],
                'requires_approval' => $validated['requires_approval'] ?? false,
            ]);

            // Create sections and items
            foreach ($validated['sections'] as $sectionData) {
                $section = $template->sections()->create([
                    'name' => $sectionData['name'],
                    'description' => $sectionData['description'],
                    'default_discount_percentage' => $sectionData['default_discount_percentage'] ?? 0,
                    'sort_order' => $sectionData['sort_order'] ?? 0,
                    'is_required' => $sectionData['is_required'] ?? true,
                    'estimated_hours' => $sectionData['estimated_hours'],
                    'instructions' => $sectionData['instructions'],
                ]);

                foreach ($sectionData['items'] as $itemData) {
                    $section->items()->create([
                        'description' => $itemData['description'],
                        'unit' => $itemData['unit'] ?? 'Nos',
                        'default_quantity' => $itemData['default_quantity'],
                        'default_unit_price' => $itemData['default_unit_price'],
                        'item_code' => $itemData['item_code'],
                        'specifications' => $itemData['specifications'],
                        'notes' => $itemData['notes'],
                        'sort_order' => $itemData['sort_order'] ?? 0,
                        'is_required' => $itemData['is_required'] ?? true,
                        'quantity_editable' => $itemData['quantity_editable'] ?? true,
                        'price_editable' => $itemData['price_editable'] ?? true,
                        'cost_price' => $itemData['cost_price'],
                        'minimum_price' => $itemData['minimum_price'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('service-templates.show', $template)
                ->with('success', 'Service template created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create service template: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceTemplate $serviceTemplate)
    {
        $serviceTemplate->load(['sections.items', 'company', 'createdBy', 'updatedBy']);

        // Check authorization
        if (!$serviceTemplate->canBeUsedBy(Auth::user())) {
            abort(403, 'You do not have permission to view this template.');
        }

        $analytics = [
            'total_sections' => $serviceTemplate->sections()->count(),
            'total_items' => $serviceTemplate->sections()->withCount('items')->get()->sum('items_count'),
            'estimated_total' => $serviceTemplate->calculateEstimatedTotal(),
            'complexity_score' => $serviceTemplate->getComplexityScore(),
        ];

        return view('service-templates.show', compact('serviceTemplate', 'analytics'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceTemplate $serviceTemplate)
    {
        // Check authorization
        if (!$serviceTemplate->canBeEditedBy(Auth::user())) {
            abort(403, 'You do not have permission to edit this template.');
        }

        $serviceTemplate->load(['sections.items']);
        
        $categories = ServiceTemplate::getCategories();
        $teams = Team::forCompany()->get();

        return view('service-templates.edit', compact('serviceTemplate', 'categories', 'teams'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceTemplate $serviceTemplate)
    {
        // Check authorization
        if (!$serviceTemplate->canBeEditedBy(Auth::user())) {
            abort(403, 'You do not have permission to edit this template.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'category' => ['nullable', Rule::in(array_keys(ServiceTemplate::getCategories()))],
            'applicable_teams' => 'nullable|array',
            'applicable_teams.*' => 'exists:teams,id',
            'estimated_hours' => 'nullable|numeric|min:0|max:9999.99',
            'base_price' => 'nullable|numeric|min:0|max:99999999.99',
            'requires_approval' => 'boolean',
            'is_active' => 'boolean',
        ]);

        try {
            $serviceTemplate->update($validated);

            return redirect()->route('service-templates.show', $serviceTemplate)
                ->with('success', 'Service template updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update service template: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceTemplate $serviceTemplate)
    {
        // Check authorization
        if (!$serviceTemplate->canBeDeletedBy(Auth::user())) {
            abort(403, 'You do not have permission to delete this template.');
        }

        try {
            $serviceTemplate->delete();

            return redirect()->route('service-templates.index')
                ->with('success', 'Service template deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete service template: ' . $e->getMessage()]);
        }
    }

    /**
     * Duplicate a service template
     */
    public function duplicate(ServiceTemplate $serviceTemplate)
    {
        // Check authorization
        if (!$serviceTemplate->canBeUsedBy(Auth::user())) {
            abort(403, 'You do not have permission to duplicate this template.');
        }

        try {
            $duplicatedTemplate = $serviceTemplate->duplicate();

            return redirect()->route('service-templates.show', $duplicatedTemplate)
                ->with('success', 'Service template duplicated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to duplicate service template: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle template active status
     */
    public function toggleStatus(ServiceTemplate $serviceTemplate)
    {
        // Check authorization
        if (!$serviceTemplate->canBeEditedBy(Auth::user())) {
            abort(403, 'You do not have permission to modify this template.');
        }

        try {
            $serviceTemplate->update([
                'is_active' => !$serviceTemplate->is_active
            ]);

            $status = $serviceTemplate->is_active ? 'activated' : 'deactivated';
            
            return redirect()->back()
                ->with('success', "Template {$status} successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update template status: ' . $e->getMessage()]);
        }
    }

    /**
     * Convert template to quotation
     */
    public function convertToQuotation(Request $request, ServiceTemplate $serviceTemplate)
    {
        // Check authorization
        if (!$serviceTemplate->canBeUsedBy(Auth::user())) {
            abort(403, 'You do not have permission to use this template.');
        }

        // Check if template needs approval
        if ($serviceTemplate->needsApproval(Auth::user())) {
            return redirect()->back()
                ->withErrors(['error' => 'This template requires manager approval before use.']);
        }

        try {
            // Record template usage
            $serviceTemplate->recordUsage();

            // Get template data for quotation conversion
            $templateData = $serviceTemplate->toQuotationData($request->get('customizations', []));

            // Redirect to quotation creation with template data
            return redirect()->route('quotations.create')
                ->with('template_data', $templateData)
                ->with('template_id', $serviceTemplate->id)
                ->with('success', 'Template loaded for quotation creation.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to convert template: ' . $e->getMessage()]);
        }
    }
}
