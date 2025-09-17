<?php

namespace App\Http\Controllers;

use App\Models\CustomerSegment;
use App\Models\PricingCategory;
use App\Models\PricingItem;
use App\Models\PricingTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PricingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the pricing book with categories and items
     */
    public function index(Request $request)
    {
        $query = PricingItem::query()
            ->forCompany()
            ->with(['category', 'createdBy']);

        // Apply filters
        if ($request->filled('category')) {
            $query->inCategory($request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'featured') {
                $query->featured();
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('specifications', 'like', "%{$search}%");
            });
        }

        if ($request->filled('price_min') || $request->filled('price_max')) {
            $query->byPriceRange($request->price_min, $request->price_max);
        }

        // Sort
        $sortBy = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        $allowedSorts = ['name', 'item_code', 'unit_price', 'created_at', 'last_price_update'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->ordered();
        }

        $items = $query->paginate(24)->withQueryString();

        // Get categories for filter dropdown
        $categories = PricingCategory::forCompany()
            ->active()
            ->withCount('activeItems')
            ->ordered()
            ->get();

        // Get statistics
        $stats = [
            'total_items' => PricingItem::forCompany()->count(),
            'active_items' => PricingItem::forCompany()->active()->count(),
            'featured_items' => PricingItem::forCompany()->featured()->count(),
            'total_categories' => PricingCategory::forCompany()->count(),
            'needs_price_review' => PricingItem::needsPriceReview()->count(),
        ];

        $viewMode = $request->get('view', 'grid'); // grid or list

        return view('pricing.index', compact(
            'items', 
            'categories', 
            'stats',
            'viewMode'
        ));
    }

    /**
     * Show the form for creating a new pricing item
     */
    public function create()
    {
        $categories = PricingCategory::forCompany()
            ->active()
            ->ordered()
            ->get();

        return view('pricing.create', compact('categories'));
    }

    /**
     * Store a newly created pricing item in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pricing_category_id' => 'required|exists:pricing_categories,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'item_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('pricing_items')->where('company_id', Auth::user()->company_id)
            ],
            'unit' => 'required|string|max:20',
            'unit_price' => 'required|numeric|min:0|max:99999999.99',
            'cost_price' => 'nullable|numeric|min:0|max:99999999.99',
            'minimum_price' => 'nullable|numeric|min:0|max:99999999.99',
            'specifications' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'is_featured' => 'boolean',
            'track_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Validate category belongs to company
        $category = PricingCategory::forCompany()->findOrFail($validated['pricing_category_id']);

        try {
            DB::beginTransaction();

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store(
                    'pricing-items/' . Auth::user()->company_id,
                    'public'
                );
                $validated['image_path'] = $imagePath;
            }

            // Create the pricing item
            $item = PricingItem::create($validated);

            DB::commit();

            return redirect()->route('pricing.show', $item)
                ->with('success', 'Pricing item created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded image if it exists
            if (isset($validated['image_path'])) {
                Storage::disk('public')->delete($validated['image_path']);
            }
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create pricing item: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified pricing item
     */
    public function show(PricingItem $pricing)
    {
        $pricing->load(['category', 'createdBy', 'updatedBy']);

        // Get pricing analytics
        $analytics = $pricing->getPricingAnalytics();

        // Get related items in same category
        $relatedItems = PricingItem::inCategory($pricing->pricing_category_id)
            ->where('id', '!=', $pricing->id)
            ->active()
            ->limit(6)
            ->get();

        return view('pricing.show', compact('pricing', 'analytics', 'relatedItems'));
    }

    /**
     * Show the form for editing the specified pricing item
     */
    public function edit(PricingItem $pricing)
    {
        $categories = PricingCategory::forCompany()
            ->active()
            ->ordered()
            ->get();

        return view('pricing.edit', compact('pricing', 'categories'));
    }

    /**
     * Update the specified pricing item in storage
     */
    public function update(Request $request, PricingItem $pricing)
    {
        $validated = $request->validate([
            'pricing_category_id' => 'required|exists:pricing_categories,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'item_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('pricing_items')
                    ->where('company_id', Auth::user()->company_id)
                    ->ignore($pricing->id)
            ],
            'unit' => 'required|string|max:20',
            'unit_price' => 'required|numeric|min:0|max:99999999.99',
            'cost_price' => 'nullable|numeric|min:0|max:99999999.99',
            'minimum_price' => 'nullable|numeric|min:0|max:99999999.99',
            'specifications' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'track_stock' => 'boolean',
            'stock_quantity' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_image' => 'boolean',
        ]);

        // Validate category belongs to company
        $category = PricingCategory::forCompany()->findOrFail($validated['pricing_category_id']);

        try {
            DB::beginTransaction();

            $oldImagePath = $pricing->image_path;

            // Handle image removal
            if ($validated['remove_image'] ?? false) {
                $validated['image_path'] = null;
            }

            // Handle new image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store(
                    'pricing-items/' . Auth::user()->company_id,
                    'public'
                );
                $validated['image_path'] = $imagePath;
            }

            // Update the pricing item
            unset($validated['image'], $validated['remove_image']);
            $pricing->update($validated);

            // Delete old image if replaced or removed
            if ($oldImagePath && ($validated['image_path'] ?? null) !== $oldImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }

            DB::commit();

            return redirect()->route('pricing.show', $pricing)
                ->with('success', 'Pricing item updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded image if it exists and there was an error
            if (isset($validated['image_path']) && $validated['image_path'] !== $oldImagePath) {
                Storage::disk('public')->delete($validated['image_path']);
            }
            
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update pricing item: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified pricing item from storage
     */
    public function destroy(PricingItem $pricing)
    {
        try {
            // Delete associated image
            if ($pricing->image_path) {
                Storage::disk('public')->delete($pricing->image_path);
            }

            $pricing->delete();

            return redirect()->route('pricing.index')
                ->with('success', 'Pricing item deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete pricing item: ' . $e->getMessage()]);
        }
    }

    /**
     * Duplicate a pricing item
     */
    public function duplicate(PricingItem $pricing)
    {
        try {
            $duplicatedItem = $pricing->duplicate();

            return redirect()->route('pricing.show', $duplicatedItem)
                ->with('success', 'Pricing item duplicated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to duplicate pricing item: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle item active status
     */
    public function toggleStatus(PricingItem $pricing)
    {
        try {
            $pricing->update(['is_active' => !$pricing->is_active]);

            $status = $pricing->is_active ? 'activated' : 'deactivated';
            
            return redirect()->back()
                ->with('success', "Item {$status} successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update item status: ' . $e->getMessage()]);
        }
    }

    /**
     * Search items for AJAX requests (for quotation integration)
     */
    public function search(Request $request)
    {
        $term = $request->get('q', '');
        
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $items = PricingItem::search($term)
            ->limit(10)
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
                ];
            });

        return response()->json($items);
    }

    /**
     * Export pricing items to CSV
     */
    public function export(Request $request)
    {
        $query = PricingItem::forCompany()
            ->with(['category'])
            ->active();

        // Apply same filters as index
        if ($request->filled('category')) {
            $query->inCategory($request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $items = $query->ordered()->get();

        $filename = 'pricing-items-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($items) {
            $handle = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($handle, [
                'Item Code', 'Name', 'Description', 'Category', 'Unit',
                'Unit Price', 'Cost Price', 'Minimum Price', 'Markup %',
                'Specifications', 'Active', 'Featured', 'Stock Qty',
                'Created', 'Last Price Update'
            ]);

            // Add data rows
            foreach ($items as $item) {
                fputcsv($handle, $item->toExportArray());
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Get popular/featured items for dashboard
     */
    public function popular()
    {
        $popularItems = PricingItem::getPopular(null, 8);
        $featuredItems = PricingItem::getFeatured(null, 8);
        $recentItems = PricingItem::forCompany()
            ->active()
            ->latest()
            ->limit(8)
            ->get();

        return view('pricing.popular', compact(
            'popularItems',
            'featuredItems', 
            'recentItems'
        ));
    }

    /**
     * Show tier pricing management for a specific item
     */
    public function manageTiers(PricingItem $item)
    {
        $this->authorize('update', $item);

        $segments = CustomerSegment::forCompany()->active()->ordered()->get();
        $tiersBySegment = $item->getTiersBySegment();
        $analytics = $item->getTierPricingAnalytics();

        return view('pricing.tiers.manage', compact(
            'item',
            'segments',
            'tiersBySegment',
            'analytics'
        ));
    }

    /**
     * Store new pricing tier
     */
    public function storeTier(Request $request, PricingItem $item)
    {
        $this->authorize('update', $item);

        $request->validate([
            'customer_segment_id' => 'required|exists:customer_segments,id',
            'min_quantity' => 'required|integer|min:1',
            'max_quantity' => 'nullable|integer|gt:min_quantity',
            'unit_price' => 'required|numeric|min:0.01',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $item) {
            $tier = new PricingTier($request->all());
            $tier->pricing_item_id = $item->id;
            
            // Validate business rules
            $validation = $tier->validateTier();
            if (!$validation['valid']) {
                throw new \Exception(implode(', ', $validation['issues']));
            }
            
            $tier->save();
        });

        return redirect()
            ->route('pricing.manage-tiers', $item)
            ->with('success', 'Pricing tier created successfully');
    }

    /**
     * Update pricing tier
     */
    public function updateTier(Request $request, PricingItem $item, PricingTier $tier)
    {
        $this->authorize('update', $item);

        $request->validate([
            'min_quantity' => 'required|integer|min:1',
            'max_quantity' => 'nullable|integer|gt:min_quantity',
            'unit_price' => 'required|numeric|min:0.01',
            'discount_percentage' => 'nullable|numeric|between:0,100',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $tier) {
            $tier->fill($request->all());
            
            // Validate business rules
            $validation = $tier->validateTier();
            if (!$validation['valid']) {
                throw new \Exception(implode(', ', $validation['issues']));
            }
            
            $tier->save();
        });

        return redirect()
            ->route('pricing.manage-tiers', $item)
            ->with('success', 'Pricing tier updated successfully');
    }

    /**
     * Delete pricing tier
     */
    public function destroyTier(PricingItem $item, PricingTier $tier)
    {
        $this->authorize('update', $item);

        $tier->delete();

        return redirect()
            ->route('pricing.manage-tiers', $item)
            ->with('success', 'Pricing tier deleted successfully');
    }

    /**
     * Generate suggested tiers for an item/segment combination
     */
    public function generateSuggestedTiers(Request $request, PricingItem $item)
    {
        $this->authorize('update', $item);

        $request->validate([
            'customer_segment_id' => 'required|exists:customer_segments,id',
        ]);

        $segment = CustomerSegment::find($request->customer_segment_id);
        $suggestedTiers = PricingTier::generateSuggestedTiers($item, $segment);

        return response()->json([
            'success' => true,
            'tiers' => $suggestedTiers,
        ]);
    }

    /**
     * Get pricing for specific segment and quantity (AJAX endpoint)
     */
    public function getSegmentPricing(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:pricing_items,id',
            'segment_id' => 'required|exists:customer_segments,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $item = PricingItem::findOrFail($request->item_id);
        $this->authorize('view', $item);

        $pricing = $item->getPriceForSegment($request->segment_id, $request->quantity);

        return response()->json([
            'success' => true,
            'pricing' => $pricing,
        ]);
    }

    /**
     * Bulk create tiers from suggestions
     */
    public function bulkCreateTiers(Request $request, PricingItem $item)
    {
        $this->authorize('update', $item);

        $request->validate([
            'customer_segment_id' => 'required|exists:customer_segments,id',
            'tiers' => 'required|array|min:1',
            'tiers.*.min_quantity' => 'required|integer|min:1',
            'tiers.*.max_quantity' => 'nullable|integer',
            'tiers.*.unit_price' => 'required|numeric|min:0.01',
            'tiers.*.discount_percentage' => 'nullable|numeric|between:0,100',
        ]);

        DB::transaction(function () use ($request, $item) {
            foreach ($request->tiers as $tierData) {
                $tier = new PricingTier([
                    'pricing_item_id' => $item->id,
                    'customer_segment_id' => $request->customer_segment_id,
                    'min_quantity' => $tierData['min_quantity'],
                    'max_quantity' => $tierData['max_quantity'],
                    'unit_price' => $tierData['unit_price'],
                    'discount_percentage' => $tierData['discount_percentage'] ?? null,
                ]);

                // Validate each tier
                $validation = $tier->validateTier();
                if (!$validation['valid']) {
                    throw new \Exception(implode(', ', $validation['issues']));
                }

                $tier->save();
            }
        });

        return redirect()
            ->route('pricing.manage-tiers', $item)
            ->with('success', count($request->tiers) . ' pricing tiers created successfully');
    }

    /**
     * Customer segment management
     */
    public function segments()
    {
        $this->authorize('viewAny', CustomerSegment::class);

        $segments = CustomerSegment::forCompany()
            ->with(['createdBy'])
            ->withCount(['pricingTiers as active_tiers' => function ($query) {
                $query->where('is_active', true);
            }])
            ->ordered()
            ->get();

        return view('pricing.segments.index', compact('segments'));
    }

    /**
     * Store new customer segment
     */
    public function storeSegment(Request $request)
    {
        $this->authorize('create', CustomerSegment::class);

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'default_discount_percentage' => 'required|numeric|between:0,100',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $segment = CustomerSegment::create($request->all());

        return redirect()
            ->route('pricing.segments')
            ->with('success', 'Customer segment created successfully');
    }

    /**
     * Update customer segment
     */
    public function updateSegment(Request $request, CustomerSegment $segment)
    {
        $this->authorize('update', $segment);

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'default_discount_percentage' => 'required|numeric|between:0,100',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $segment->update($request->all());

        return redirect()
            ->route('pricing.segments')
            ->with('success', 'Customer segment updated successfully');
    }

    /**
     * Toggle segment active status
     */
    public function toggleSegment(CustomerSegment $segment)
    {
        $this->authorize('update', $segment);

        $segment->update([
            'is_active' => !$segment->is_active
        ]);

        $status = $segment->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('pricing.segments')
            ->with('success', "Customer segment {$status} successfully");
    }
}
