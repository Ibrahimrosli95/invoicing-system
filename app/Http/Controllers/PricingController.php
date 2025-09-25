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

    /**
     * Display the pricing book with segment-based pricing tabs
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
            } elseif ($request->status === 'segment_pricing') {
                $query->where('use_segment_pricing', true);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('price_min') || $request->filled('price_max')) {
            $query->byPriceRange($request->price_min, $request->price_max);
        }

        // Filter by customer segment
        if ($request->filled('segment')) {
            $query->whereHas('pricingTiers', function ($q) use ($request) {
                $q->where('customer_segment_id', $request->segment);
            });
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

        // Get per-page preference (25, 50, 100, 200)
        $perPage = $request->get('per_page', 100);
        $allowedPerPage = [25, 50, 100, 200];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 100;
        }

        $items = $query->paginate($perPage)->withQueryString();

        // Get customer segments for tabs
        $segments = CustomerSegment::forCompany()
            ->active()
            ->ordered()
            ->get();

        // Get categories for filter dropdown
        $categories = PricingCategory::forCompany()
            ->active()
            ->withCount('activeItems')
            ->ordered()
            ->get();

        // Get statistics including segment pricing
        $stats = [
            'total_items' => PricingItem::forCompany()->count(),
            'active_items' => PricingItem::forCompany()->active()->count(),
            'segment_pricing_items' => PricingItem::forCompany()->where('use_segment_pricing', true)->count(),
            'total_categories' => PricingCategory::forCompany()->count(),
            'needs_price_review' => PricingItem::needsPriceReview()->count(),
        ];

        $viewMode = $request->get('view', 'grid'); // grid or list

        return view('pricing.index', compact(
            'items',
            'segments',
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

        $segments = CustomerSegment::forCompany()
            ->active()
            ->ordered()
            ->get();

        return view('pricing.create', compact('categories', 'segments'));
    }

    /**
     * Store a newly created pricing item in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:pricing_categories,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'item_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('pricing_items')->where('company_id', Auth::user()->company_id)
            ],
            'unit_price' => 'required|numeric|min:0|max:99999999.99',
            'cost_price' => 'nullable|numeric|min:0|max:99999999.99',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'use_segment_pricing' => 'boolean',
            'segment_prices' => 'nullable|array',
            'segment_prices.*' => 'nullable|numeric|min:0|max:99999999.99',
        ]);

        // Validate category belongs to company
        $category = PricingCategory::forCompany()->findOrFail($validated['category_id']);

        try {
            DB::beginTransaction();

            // Debug logging
            \Log::info('Attempting to create pricing item', [
                'user_id' => auth()->id(),
                'company_id' => auth()->user()->company_id ?? null,
                'validated_data' => $validated
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store(
                    'pricing-items/' . Auth::user()->company_id,
                    'public'
                );
                $validated['image_path'] = $imagePath;
            }

            // Handle segment pricing
            if (!empty($validated['segment_prices'])) {
                // Filter out empty prices and format properly
                $segmentPrices = [];
                foreach ($validated['segment_prices'] as $segmentId => $price) {
                    if ($price !== null && $price > 0) {
                        $segmentPrices[$segmentId] = number_format($price, 2, '.', '');
                    }
                }

                if (!empty($segmentPrices)) {
                    $validated['segment_selling_prices'] = $segmentPrices;
                    $validated['segment_prices_updated_at'] = now();
                    $validated['use_segment_pricing'] = true;
                }
            }

            // Remove segment_prices from validated as it's not a database column
            unset($validated['segment_prices']);

            // Map category_id to pricing_category_id for database column
            if (isset($validated['category_id'])) {
                $validated['pricing_category_id'] = $validated['category_id'];
                unset($validated['category_id']);
            }

            // Set defaults
            $validated['is_active'] = $validated['is_active'] ?? true;

            // Debug: Log data about to be created
            \Log::info('Creating pricing item with data', $validated);

            // Create the pricing item
            $item = PricingItem::create($validated);

            \Log::info('Pricing item created successfully', ['item_id' => $item->id]);

            DB::commit();

            return redirect()->route('pricing.index')
                ->with('success', 'Pricing item "' . $item->name . '" created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded image if it exists
            if (isset($validated['image_path'])) {
                Storage::disk('public')->delete($validated['image_path']);
            }

            // Log the full error for debugging
            \Log::error('Pricing item creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'company_id' => auth()->user()->company_id ?? null,
                'validated_data' => $validated
            ]);

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

        return view('pricing.show', [
            'pricingItem' => $pricing,
            'analytics' => $analytics,
            'relatedItems' => $relatedItems
        ]);
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

        return view('pricing.edit', [
            'pricingItem' => $pricing,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified pricing item in storage
     */
    public function update(Request $request, PricingItem $pricing)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:pricing_categories,id',
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
            'unit_price' => 'required|numeric|min:0|max:99999999.99',
            'cost_price' => 'nullable|numeric|min:0|max:99999999.99',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_image' => 'boolean',
        ]);

        // Validate category belongs to company
        $category = PricingCategory::forCompany()->findOrFail($validated['category_id']);

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
                    'unit_price' => $item->unit_price,
                    'category_name' => $item->category->name ?? '',
                    'category_path' => $item->getCategoryPath(),
                    'image_url' => $item->getImageUrl(),
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
                'Item Code', 'Name', 'Description', 'Category',
                'Unit Price', 'Cost Price', 'Minimum Price', 'Markup %',
                'Active', 'Created', 'Last Price Update'
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
        $recentItems = PricingItem::forCompany()
            ->active()
            ->latest()
            ->limit(8)
            ->get();

        return view('pricing.popular', compact(
            'popularItems',
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

    /**
     * Show the import form
     */
    public function import()
    {
        // Restrict bulk import to senior management only
        if (!auth()->user()->hasAnyRole(['superadmin', 'company_manager', 'finance_manager'])) {
            abort(403, 'Unauthorized. Only senior management can access bulk import functionality.');
        }

        return view('pricing.import');
    }

    /**
     * Download CSV template for import
     */
    public function downloadTemplate()
    {
        // Restrict template download to senior management only
        if (!auth()->user()->hasAnyRole(['superadmin', 'company_manager', 'finance_manager'])) {
            abort(403, 'Unauthorized. Only senior management can download import templates.');
        }

        $segments = CustomerSegment::forCompany()->active()->ordered()->get();
        $categories = PricingCategory::forCompany()->active()->ordered()->get();

        // Create CSV header - matching all fields from create form
        $headers = [
            'name',
            'item_code',
            'description',
            'category',
            'cost_price',
            'unit_price',
            'is_active',
        ];

        // Add segment price columns dynamically
        foreach ($segments as $segment) {
            $headers[] = strtolower(str_replace(' ', '_', $segment->name)) . '_price';
        }

        // Sample data row
        $sampleData = [
            'Sample Product',
            'SP001',
            'This is a sample product description',
            $categories->first()->name ?? 'Construction Materials',
            '100.00',
            '120.00',  // Unit price (will be updated to highest segment price)
            'TRUE',
        ];

        // Generate dynamic sample segment prices based on actual segments
        $segmentPrices = [];
        $costPrice = 100.00; // Cost price from sample data
        $basePriceMargin = 20.00; // Base margin above cost
        $segmentIncrement = 15.00; // Price increment between segments

        foreach ($segments as $index => $segment) {
            // Create tiered pricing: higher segment index = higher price
            // Each segment gets progressively higher pricing
            $segmentPrice = $costPrice + $basePriceMargin + ($index * $segmentIncrement);
            $sampleData[] = number_format($segmentPrice, 2);
            $segmentPrices[] = $segmentPrice;
        }

        // Update unit_price to be the highest segment price (follows business rule)
        if (!empty($segmentPrices)) {
            $highestPrice = max($segmentPrices);
            $sampleData[5] = number_format($highestPrice, 2); // Index 5 is unit_price
        }

        // Create CSV content
        $content = implode(',', $headers) . "\n";
        $content .= implode(',', array_map(function($value) {
            return '"' . str_replace('"', '""', $value) . '"';
        }, $sampleData)) . "\n";

        // Add a few more sample rows
        for ($i = 2; $i <= 3; $i++) {
            $row = $sampleData;
            $row[0] = "Sample Product {$i}";
            $row[1] = "SP00{$i}";
            $content .= implode(',', array_map(function($value) {
                return '"' . str_replace('"', '""', $value) . '"';
            }, $row)) . "\n";
        }

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="pricing_import_template.csv"');
    }

    /**
     * Process CSV import
     */
    public function processImport(Request $request)
    {
        // Restrict import processing to senior management only
        if (!auth()->user()->hasAnyRole(['superadmin', 'company_manager', 'finance_manager'])) {
            abort(403, 'Unauthorized. Only senior management can process bulk imports.');
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
            'skip_duplicates' => 'boolean',
            'update_existing' => 'boolean',
            'validate_only' => 'boolean',
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->path()));
            $headers = array_map('strtolower', array_shift($csvData));

            // Validate headers
            $requiredHeaders = ['name', 'category', 'cost_price', 'unit_price'];
            $missingHeaders = array_diff($requiredHeaders, $headers);

            if (!empty($missingHeaders)) {
                return redirect()->back()
                    ->withErrors(['csv_file' => 'Missing required columns: ' . implode(', ', $missingHeaders)])
                    ->withInput();
            }

            $segments = CustomerSegment::forCompany()->active()->get();
            $categories = PricingCategory::forCompany()->active()->pluck('id', 'name');

            $results = [
                'total_rows' => count($csvData),
                'success_count' => 0,
                'error_count' => 0,
                'errors' => [],
                'created_items' => [],
            ];

            $validateOnly = $request->boolean('validate_only');
            $skipDuplicates = $request->boolean('skip_duplicates');
            $updateExisting = $request->boolean('update_existing');

            if (!$validateOnly) {
                DB::beginTransaction();
            }

            foreach ($csvData as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // +2 because we removed header and arrays are 0-indexed

                try {
                    $data = array_combine($headers, $row);

                    // Validate required fields
                    if (empty($data['name']) || empty($data['category']) || empty($data['unit_price'])) {
                        throw new \Exception("Missing required fields");
                    }

                    // Find category
                    $categoryId = $categories->get($data['category']);
                    if (!$categoryId) {
                        throw new \Exception("Category '{$data['category']}' not found");
                    }

                    // Check for duplicate item code
                    if (!empty($data['item_code'])) {
                        $existing = PricingItem::forCompany()
                            ->where('item_code', $data['item_code'])
                            ->first();

                        if ($existing) {
                            if ($skipDuplicates && !$updateExisting) {
                                continue; // Skip this row
                            } elseif (!$updateExisting) {
                                throw new \Exception("Item code '{$data['item_code']}' already exists");
                            }
                        }
                    }

                    // Prepare pricing item data
                    $itemData = [
                        'name' => $data['name'],
                        'item_code' => $data['item_code'] ?? null,
                        'description' => $data['description'] ?? null,
                        'pricing_category_id' => $categoryId,
                        'cost_price' => !empty($data['cost_price']) ? (float) $data['cost_price'] : 0,
                        'unit_price' => !empty($data['unit_price']) ? (float) $data['unit_price'] : 0,
                        'is_active' => !empty($data['is_active']) ? (strtolower($data['is_active']) === 'true' || $data['is_active'] === '1') : true,
                    ];


                    // Handle segment pricing
                    $segmentPrices = [];
                    $hasSegmentPricing = false;

                    foreach ($segments as $segment) {
                        $priceColumn = strtolower(str_replace(' ', '_', $segment->name)) . '_price';
                        if (isset($data[$priceColumn]) && !empty($data[$priceColumn])) {
                            $segmentPrices[$segment->id] = number_format((float) $data[$priceColumn], 2, '.', '');
                            $hasSegmentPricing = true;
                        }
                    }

                    if ($hasSegmentPricing) {
                        $itemData['segment_selling_prices'] = $segmentPrices;
                        $itemData['use_segment_pricing'] = true;
                        $itemData['segment_prices_updated_at'] = now();
                    }

                    if (!$validateOnly) {
                        if (isset($existing) && $updateExisting) {
                            $existing->update($itemData);
                            $results['created_items'][] = "Updated: {$itemData['name']}";
                        } else {
                            PricingItem::create($itemData);
                            $results['created_items'][] = "Created: {$itemData['name']}";
                        }
                    }

                    $results['success_count']++;

                } catch (\Exception $e) {
                    $results['error_count']++;
                    $results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                }
            }

            if (!$validateOnly) {
                if ($results['error_count'] > 0 && $results['success_count'] === 0) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withErrors(['csv_file' => 'Import failed. Please check the errors below.'])
                        ->with('import_results', $results)
                        ->withInput();
                } else {
                    DB::commit();
                }
            }

            $message = $validateOnly
                ? "Validation completed. {$results['success_count']} valid rows, {$results['error_count']} errors found."
                : "Import completed successfully. {$results['success_count']} items processed, {$results['error_count']} errors.";

            return redirect()->route('pricing.index')
                ->with('success', $message)
                ->with('import_results', $results);

        } catch (\Exception $e) {
            if (!$validateOnly) {
                DB::rollBack();
            }

            return redirect()->back()
                ->withErrors(['csv_file' => 'Import failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Search products for enhanced builders (API endpoint).
     */
    public function searchApi(Request $request)
    {
        // Accept both 'q' and 'query' parameters for compatibility
        $query = $request->get('q', '');
        if (empty($query)) {
            $query = $request->get('query', '');
        }
        $category = $request->get('category', '');
        $limit = $request->get('limit', 50);

        if (strlen($query) < 2 && empty($category)) {
            return response()->json(['items' => []]);
        }

        $products = PricingItem::query()
            ->forCompany()
            ->active()
            ->with(['category', 'segmentPricing', 'tierPricing']);

        if (!empty($query)) {
            $products->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('item_code', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
        }

        if (!empty($category)) {
            $products->inCategory($category);
        }

        $items = $products->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'item_code' => $item->item_code,
                    'description' => $item->description,
                    'unit_price' => number_format($item->unit_price, 2),
                    'unit_price_raw' => $item->unit_price,
                    'segment_pricing' => $item->segmentPricing->map(function ($sp) {
                        return [
                            'customer_segment_id' => $sp->customer_segment_id,
                            'unit_price' => $sp->unit_price,
                        ];
                    }),
                    'tier_pricing' => $item->tierPricing->map(function ($tp) {
                        return [
                            'min_quantity' => $tp->min_quantity,
                            'unit_price' => $tp->unit_price,
                        ];
                    }),
                    'category' => $item->category ? $item->category->name : null,
                ];
            });

        return response()->json(['items' => $items]);
    }

    /**
     * Get item segment pricing (API endpoint).
     */
    public function getItemSegmentPricing(Request $request, PricingItem $item)
    {
        $segmentId = $request->get('segment_id');

        if (!$segmentId) {
            return response()->json(['segment_price' => $item->unit_price]);
        }

        $segmentPricing = $item->segmentPricing()
            ->where('customer_segment_id', $segmentId)
            ->first();

        $segmentPrice = $segmentPricing ? $segmentPricing->unit_price : $item->unit_price;

        return response()->json([
            'segment_price' => $segmentPrice,
            'unit_price' => $item->unit_price,
            'has_segment_pricing' => $segmentPricing !== null,
        ]);
    }

    /**
     * Get item tier pricing (API endpoint).
     */
    public function getItemTierPricing(Request $request, PricingItem $item)
    {
        $quantity = (float) $request->get('quantity', 1);

        $tierPricing = $item->tierPricing()
            ->where('min_quantity', '<=', $quantity)
            ->orderBy('min_quantity', 'desc')
            ->first();

        if ($tierPricing && $tierPricing->unit_price < $item->unit_price) {
            $savings = ($item->unit_price - $tierPricing->unit_price) * $quantity;
            return response()->json([
                'tier_price' => $tierPricing->unit_price,
                'unit_price' => $item->unit_price,
                'savings' => $savings,
                'tier_info' => "Tier pricing: Save RM {$savings}",
                'min_quantity' => $tierPricing->min_quantity,
            ]);
        }

        return response()->json([
            'tier_price' => null,
            'unit_price' => $item->unit_price,
            'savings' => 0,
            'tier_info' => null,
        ]);
    }
}
