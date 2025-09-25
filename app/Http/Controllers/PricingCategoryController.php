<?php

namespace App\Http\Controllers;

use App\Models\PricingCategory;
use App\Models\PricingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PricingCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', PricingCategory::class);

        $query = PricingCategory::forCompany()
            ->with(['parent', 'children', 'createdBy'])
            ->withCount(['activeItems']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

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

        $allowedSorts = ['name', 'sort_order', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->ordered();
        }

        $categories = $query->paginate(20)->withQueryString();

        // Get statistics
        $stats = [
            'total_categories' => PricingCategory::forCompany()->count(),
            'active_categories' => PricingCategory::forCompany()->active()->count(),
            'parent_categories' => PricingCategory::forCompany()->whereNull('parent_id')->count(),
            'total_items' => PricingItem::forCompany()->count(),
        ];

        return view('pricing.categories.index', compact('categories', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', PricingCategory::class);

        // Get parent categories for dropdown
        $parentCategories = PricingCategory::forCompany()
            ->whereNull('parent_id')
            ->active()
            ->ordered()
            ->get();

        return view('pricing.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', PricingCategory::class);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('pricing_categories')->where('company_id', auth()->user()->company_id)
            ],
            'parent_id' => 'nullable|exists:pricing_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        // Validate parent category belongs to company
        if (!empty($validated['parent_id'])) {
            $parentCategory = PricingCategory::forCompany()->findOrFail($validated['parent_id']);
        }

        try {
            DB::beginTransaction();

            $category = PricingCategory::create($validated);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'category' => $category,
                    'message' => 'Category created successfully.'
                ]);
            }

            return redirect()->route('pricing.categories.index')
                ->with('success', 'Category created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create category: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Failed to create category: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PricingCategory $category)
    {
        $this->authorize('view', $category);

        $category->load(['parent', 'children.activeItems', 'createdBy', 'updatedBy']);

        // Get category items with pagination
        $items = $category->activeItems()
            ->with(['category', 'createdBy'])
            ->ordered()
            ->paginate(12);

        // Get category statistics
        $stats = [
            'total_items' => $category->items()->count(),
            'active_items' => $category->activeItems()->count(),
            'subcategories' => $category->children()->count(),
            'active_subcategories' => $category->children()->active()->count(),
        ];

        return view('pricing.categories.show', compact('category', 'items', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PricingCategory $category)
    {
        $this->authorize('update', $category);

        // Get parent categories for dropdown (excluding self and descendants)
        $parentCategories = PricingCategory::forCompany()
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->active()
            ->ordered()
            ->get();

        return view('pricing.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PricingCategory $category)
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('pricing_categories')
                    ->where('company_id', auth()->user()->company_id)
                    ->ignore($category->id)
            ],
            'parent_id' => 'nullable|exists:pricing_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        // Validate parent category
        if (!empty($validated['parent_id'])) {
            // Cannot be self-parent or descendant
            if ($validated['parent_id'] == $category->id) {
                return redirect()->back()
                    ->withErrors(['parent_id' => 'Category cannot be its own parent.'])
                    ->withInput();
            }

            $parentCategory = PricingCategory::forCompany()->findOrFail($validated['parent_id']);
        }

        try {
            $category->update($validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'category' => $category,
                    'message' => 'Category updated successfully.'
                ]);
            }

            return redirect()->route('pricing.categories.show', $category)
                ->with('success', 'Category updated successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update category: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Failed to update category: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PricingCategory $category)
    {
        $this->authorize('delete', $category);

        // Check if category has items
        $itemsCount = $category->items()->count();
        if ($itemsCount > 0) {
            return redirect()->back()
                ->withErrors(['error' => "Cannot delete category with {$itemsCount} items. Please move or delete items first."]);
        }

        // Check if category has subcategories
        $subcategoriesCount = $category->children()->count();
        if ($subcategoriesCount > 0) {
            return redirect()->back()
                ->withErrors(['error' => "Cannot delete category with {$subcategoriesCount} subcategories. Please move or delete subcategories first."]);
        }

        try {
            $category->delete();

            return redirect()->route('pricing.categories.index')
                ->with('success', 'Category deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete category: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle category active status
     */
    public function toggleStatus(PricingCategory $category)
    {
        $this->authorize('update', $category);

        try {
            $category->update(['is_active' => !$category->is_active]);

            $status = $category->is_active ? 'activated' : 'deactivated';

            return redirect()->back()
                ->with('success', "Category {$status} successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update category status: ' . $e->getMessage()]);
        }
    }

    /**
     * Duplicate a category
     */
    public function duplicate(PricingCategory $category)
    {
        $this->authorize('create', PricingCategory::class);
        $this->authorize('view', $category);

        try {
            $duplicatedCategory = $category->duplicate();

            return redirect()->route('pricing.categories.edit', $duplicatedCategory)
                ->with('success', 'Category duplicated successfully. Please review and update as needed.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to duplicate category: ' . $e->getMessage()]);
        }
    }

    /**
     * Get category data for AJAX requests (for quick add)
     */
    public function ajaxStore(Request $request)
    {
        $this->authorize('create', PricingCategory::class);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:pricing_categories,id',
        ]);

        try {
            $category = PricingCategory::create([
                'name' => $validated['name'],
                'parent_id' => $validated['parent_id'] ?? null,
                'is_active' => true,
                'sort_order' => 0,
            ]);

            return response()->json([
                'success' => true,
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'full_name' => $category->getFullPath(),
                ],
                'message' => 'Category created successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: ' . $e->getMessage()
            ], 422);
        }
    }
}
