<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\CustomerSegment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of customers.
     */
    public function index(Request $request): View
    {
        $query = Customer::forCompany()
            ->with(['customerSegment', 'createdBy'])
            ->withCount(['invoices', 'quotations']);

        // Apply filters
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('segment')) {
            $query->where('customer_segment_id', $request->segment);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('type')) {
            $query->where('is_new_customer', $request->type === 'new');
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(20);

        $customerSegments = CustomerSegment::forCompany()->get();

        return view('customers.index', compact('customers', 'customerSegments'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): View
    {
        $customerSegments = CustomerSegment::forCompany()->get();
        $leads = Lead::forCompany()
            ->whereIn('status', ['NEW', 'CONTACTED', 'QUALIFIED'])
            ->whereDoesntHave('customer')
            ->orderBy('name')
            ->get();

        return view('customers.create', compact('customerSegments', 'leads'));
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_segment_id' => 'nullable|exists:customer_segments,id',
            'is_new_customer' => 'boolean',
            'notes' => 'nullable|string',
            'lead_id' => 'nullable|exists:leads,id',
        ]);

        $customer = Customer::create($validated);

        // If created from a lead, mark lead as converted
        if ($validated['lead_id'] ?? false) {
            $lead = Lead::find($validated['lead_id']);
            if ($lead) {
                $lead->update([
                    'status' => 'CONVERTED',
                    'converted_at' => now(),
                ]);

                // Create lead activity
                $lead->activities()->create([
                    'type' => 'converted_to_customer',
                    'description' => "Lead converted to customer: {$customer->name}",
                    'user_id' => auth()->id(),
                    'metadata' => ['customer_id' => $customer->id],
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'customer' => $customer->toSearchResult(),
                'message' => 'Customer created successfully.'
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        $customer->load([
            'customerSegment',
            'lead',
            'invoices.items',
            'quotations.items',
            'createdBy',
            'updatedBy'
        ]);

        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        $customerSegments = CustomerSegment::forCompany()->get();

        return view('customers.edit', compact('customer', 'customerSegments'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $customer);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_segment_id' => 'nullable|exists:customer_segments,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $customer->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'customer' => $customer->toSearchResult(),
                'message' => 'Customer updated successfully.'
            ]);
        }

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $customer);

        // Check if customer has invoices or quotations
        if ($customer->hasTransactions()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete customer with existing transactions.'
                ], 422);
            }

            return back()->with('error', 'Cannot delete customer with existing transactions.');
        }

        $customer->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully.'
            ]);
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Search customers for AJAX autocomplete
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $customers = Customer::forCompany()
            ->active()
            ->search($request->q)
            ->with(['customerSegment'])
            ->limit($request->limit ?: 10)
            ->get()
            ->map(fn($customer) => $customer->toSearchResult());

        return response()->json([
            'customers' => $customers,
            'count' => $customers->count(),
        ]);
    }

    /**
     * Convert lead to customer
     */
    public function convertLead(Request $request): JsonResponse
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'customer_segment_id' => 'nullable|exists:customer_segments,id',
            'additional_notes' => 'nullable|string',
        ]);

        $lead = Lead::forCompany()->findOrFail($request->lead_id);

        // Check if lead already has a customer
        if ($lead->customer()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Lead has already been converted to a customer.'
            ], 422);
        }

        $customerData = [];
        if ($request->filled('customer_segment_id')) {
            $customerData['customer_segment_id'] = $request->customer_segment_id;
        }
        if ($request->filled('additional_notes')) {
            $customerData['notes'] = $request->additional_notes;
        }

        $customer = Customer::createFromLead($lead, $customerData);

        return response()->json([
            'success' => true,
            'customer' => $customer->toSearchResult(),
            'message' => 'Lead converted to customer successfully.'
        ]);
    }

    /**
     * Get customer details for invoice population
     */
    public function getForInvoice(Customer $customer): JsonResponse
    {
        $this->authorize('view', $customer);

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'address' => $customer->address,
                'city' => $customer->city,
                'state' => $customer->state,
                'postal_code' => $customer->postal_code,
                'customer_segment_id' => $customer->customer_segment_id,
                'is_new_customer' => $customer->is_new_customer,
                'type_badge' => $customer->type_badge,
                'status_badge' => $customer->status_badge,
            ]
        ]);
    }

    /**
     * Check for duplicate customers by phone
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'exclude_id' => 'nullable|integer|exists:customers,id',
        ]);

        $query = Customer::forCompany()
            ->where('phone', $request->phone)
            ->active();

        if ($request->filled('exclude_id')) {
            $query->where('id', '!=', $request->exclude_id);
        }

        $existingCustomer = $query->first();

        if ($existingCustomer) {
            return response()->json([
                'duplicate' => true,
                'customer' => $existingCustomer->toSearchResult(),
                'message' => 'A customer with this phone number already exists.'
            ]);
        }

        return response()->json([
            'duplicate' => false,
        ]);
    }
}
