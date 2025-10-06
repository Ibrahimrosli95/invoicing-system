<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ServiceCategoryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ServiceCategory::class, 'service_category');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ServiceCategory::query()
            ->forCompany()
            ->withCount('serviceTemplates');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sort
        $sortBy = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSorts = ['name', 'sort_order', 'created_at', 'service_templates_count'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->ordered();
        }

        $categories = $query->paginate(20)->withQueryString();

        return view('service-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('service-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6})$/',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Auto-generate slug
        if (!isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set defaults
        $validated['company_id'] = auth()->user()->company_id;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category = ServiceCategory::create($validated);

        // Handle AJAX request (quick-add)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'category' => $category,
            ], 201);
        }

        return redirect()
            ->route('service-categories.index')
            ->with('success', 'Service category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceCategory $serviceCategory)
    {
        $serviceCategory->load(['serviceTemplates' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return view('service-categories.show', compact('serviceCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceCategory $serviceCategory)
    {
        return view('service-categories.edit', compact('serviceCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6})$/',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $serviceCategory->update($validated);

        return redirect()
            ->route('service-categories.index')
            ->with('success', 'Service category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceCategory $serviceCategory)
    {
        if (!$serviceCategory->canBeDeleted()) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete category with existing templates. Please reassign or delete templates first.');
        }

        $serviceCategory->delete();

        return redirect()
            ->route('service-categories.index')
            ->with('success', 'Service category deleted successfully.');
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(ServiceCategory $serviceCategory)
    {
        $this->authorize('update', $serviceCategory);

        $serviceCategory->is_active = !$serviceCategory->is_active;
        $serviceCategory->save();

        return redirect()
            ->back()
            ->with('success', 'Category status updated successfully.');
    }

    /**
     * Quick-add API endpoint for template creation forms
     */
    public function quickAdd(Request $request): JsonResponse
    {
        $this->authorize('create', ServiceCategory::class);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6})$/',
        ]);

        $category = ServiceCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'color' => $validated['color'] ?? '#3B82F6',
            'company_id' => auth()->user()->company_id,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category added successfully',
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'color' => $category->color,
            ],
        ], 201);
    }
}
