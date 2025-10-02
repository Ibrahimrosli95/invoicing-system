<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerSegment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerSegmentController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        // Note: authorizeResource has issues with before() method
        // Using manual authorization in each method instead
        // $this->authorizeResource(CustomerSegment::class, 'customerSegment');
    }

    /**
     * Display a listing of customer segments.
     */
    public function index(Request $request)
    {
        $query = CustomerSegment::query()
            ->forCompany()
            ->with(['createdBy', 'updatedBy']);

        // Apply filters
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
        $sortBy = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');

        $allowedSorts = ['name', 'default_discount_percentage', 'sort_order', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->ordered();
        }

        $segments = $query->paginate(20)->withQueryString();

        // Get statistics
        $stats = [
            'total_segments' => CustomerSegment::forCompany()->count(),
            'active_segments' => CustomerSegment::forCompany()->active()->count(),
            'total_customers' => Customer::forCompany()->withSegment()->count(),
            'customers_without_segment' => Customer::forCompany()->whereNull('customer_segment_id')->count(),
        ];

        return view('customer-segments.index', compact('segments', 'stats'));
    }

    /**
     * Show the form for creating a new customer segment.
     */
    public function create()
    {
        return view('customer-segments.create');
    }

    /**
     * Store a newly created customer segment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('customer_segments')->where('company_id', Auth::user()->company_id)
            ],
            'description' => 'nullable|string|max:500',
            'default_discount_percentage' => 'required|numeric|min:0|max:100',
            'color' => 'required|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Set sort order if not provided
        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = CustomerSegment::forCompany()->max('sort_order') + 1;
        }

        $segment = CustomerSegment::create($validated);

        return redirect()->route('customer-segments.index')
                        ->with('success', 'Customer segment created successfully.');
    }

    /**
     * Display the specified customer segment.
     */
    public function show(CustomerSegment $customerSegment)
    {
        $this->authorize('view', $customerSegment);

        $customerSegment->load(['createdBy', 'updatedBy']);

        // Get customers in this segment
        $customers = Customer::forCompany()
                            ->where('customer_segment_id', $customerSegment->id)
                            ->with(['createdBy'])
                            ->paginate(20);

        // Get segment statistics
        $stats = $customerSegment->getStatistics();
        $stats['customer_count'] = $customers->total();

        return view('customer-segments.show', compact('customerSegment', 'customers', 'stats'));
    }

    /**
     * Show the form for editing the specified customer segment.
     */
    public function edit(CustomerSegment $customerSegment)
    {
        $this->authorize('update', $customerSegment);

        return view('customer-segments.edit', compact('customerSegment'));
    }

    /**
     * Update the specified customer segment.
     */
    public function update(Request $request, CustomerSegment $customerSegment)
    {
        $this->authorize('update', $customerSegment);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('customer_segments')
                    ->where('company_id', Auth::user()->company_id)
                    ->ignore($customerSegment->id)
            ],
            'description' => 'nullable|string|max:500',
            'default_discount_percentage' => 'required|numeric|min:0|max:100',
            'color' => 'required|string|max:7|regex:/^#[0-9A-F]{6}$/i',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $customerSegment->update($validated);

        return redirect()->route('customer-segments.index')
                        ->with('success', 'Customer segment updated successfully.');
    }

    /**
     * Remove the specified customer segment.
     */
    public function destroy(CustomerSegment $customerSegment)
    {
        $this->authorize('delete', $customerSegment);

        // Check if segment has customers
        $customerCount = Customer::forCompany()
                               ->where('customer_segment_id', $customerSegment->id)
                               ->count();

        if ($customerCount > 0) {
            return redirect()->route('customer-segments.index')
                           ->with('error', "Cannot delete segment: {$customerCount} customers are assigned to this segment. Please reassign customers first.");
        }

        // Check if segment has pricing tiers
        $tiersCount = $customerSegment->pricingTiers()->count();
        if ($tiersCount > 0) {
            return redirect()->route('customer-segments.index')
                           ->with('error', "Cannot delete segment: {$tiersCount} pricing tiers exist for this segment. Please remove pricing tiers first.");
        }

        $customerSegment->delete();

        return redirect()->route('customer-segments.index')
                        ->with('success', 'Customer segment deleted successfully.');
    }

    /**
     * Toggle segment status (active/inactive).
     */
    public function toggleStatus(CustomerSegment $customerSegment)
    {
        $customerSegment->update([
            'is_active' => !$customerSegment->is_active
        ]);

        $status = $customerSegment->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
                        ->with('success', "Customer segment {$status} successfully.");
    }

    /**
     * Duplicate a customer segment.
     */
    public function duplicate(CustomerSegment $customerSegment)
    {
        $newSegment = $customerSegment->replicate();
        $newSegment->name = $customerSegment->name . ' (Copy)';
        $newSegment->sort_order = CustomerSegment::forCompany()->max('sort_order') + 1;
        $newSegment->save();

        return redirect()->route('customer-segments.edit', $newSegment)
                        ->with('success', 'Customer segment duplicated successfully.');
    }

    /**
     * Bulk update segment sort orders.
     */
    public function updateSortOrders(Request $request)
    {
        $validated = $request->validate([
            'segments' => 'required|array',
            'segments.*.id' => 'required|exists:customer_segments,id',
            'segments.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['segments'] as $segmentData) {
                CustomerSegment::forCompany()
                              ->where('id', $segmentData['id'])
                              ->update(['sort_order' => $segmentData['sort_order']]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sort orders updated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sort orders.'
            ], 500);
        }
    }

    /**
     * Get segment statistics for dashboard.
     */
    public function statistics()
    {
        $segments = CustomerSegment::forCompany()
                                   ->active()
                                   ->with(['quotations', 'pricingTiers'])
                                   ->get();

        $statistics = $segments->map(function ($segment) {
            $stats = $segment->getStatistics();
            $stats['customer_count'] = Customer::forCompany()
                                              ->where('customer_segment_id', $segment->id)
                                              ->count();
            return array_merge(['segment' => $segment], $stats);
        });

        return response()->json($statistics);
    }
}