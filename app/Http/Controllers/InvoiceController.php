<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentRecord;
use App\Models\Quotation;
use App\Models\Team;
use App\Models\User;
use App\Models\Lead;
use App\Models\PricingItem;
use App\Models\ServiceTemplate;
use App\Models\CustomerSegment;
use App\Services\PDFService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view,invoice')->only(['show']);
        $this->middleware('can:update,invoice')->only(['edit', 'update']);
        $this->middleware('can:delete,invoice')->only(['destroy']);
    }

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::forCompany()
            ->forUserTeams()
            ->with(['quotation', 'team', 'assignedTo', 'createdBy', 'customerSegment']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
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

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }

        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->due_date_to);
        }

        // Filter for overdue invoices
        if ($request->boolean('overdue_only')) {
            $query->where('status', 'OVERDUE');
        }

        // Filter for unpaid invoices
        if ($request->boolean('unpaid_only')) {
            $query->whereIn('status', ['SENT', 'UNPAID', 'PARTIAL', 'OVERDUE']);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $invoices = $query->paginate(20)->withQueryString();

        // Get filter options
        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator', 'sales_manager', 'finance_manager']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $filters = [
            'statuses' => Invoice::getStatuses(),
            'teams' => $teams,
            'assignees' => $assignees,
        ];

        // Calculate summary statistics
        $baseQuery = Invoice::forCompany()->forUserTeams();
        
        $stats = [
            'total_invoices' => $baseQuery->count(),
            'total_amount' => $baseQuery->sum('total'),
            'paid_amount' => $baseQuery->sum('amount_paid'),
            'outstanding_amount' => $baseQuery->sum('amount_due'),
            'overdue_count' => $baseQuery->overdue()->count(),
        ];

        // Calculate aging bucket statistics
        $agingStats = [
            'current' => [
                'count' => $baseQuery->current()->count(),
                'amount' => $baseQuery->current()->sum('amount_due'),
            ],
            '0-30' => [
                'count' => $baseQuery->aging0To30()->count(),
                'amount' => $baseQuery->aging0To30()->sum('amount_due'),
            ],
            '31-60' => [
                'count' => $baseQuery->aging31To60()->count(),
                'amount' => $baseQuery->aging31To60()->sum('amount_due'),
            ],
            '61-90' => [
                'count' => $baseQuery->aging61To90()->count(),
                'amount' => $baseQuery->aging61To90()->sum('amount_due'),
            ],
            '90+' => [
                'count' => $baseQuery->aging90Plus()->count(),
                'amount' => $baseQuery->aging90Plus()->sum('amount_due'),
            ],
        ];

        return view('invoices.index', compact('invoices', 'filters', 'stats', 'agingStats'));
    }

    /**
     * Show the form for creating a new invoice.
     *
     * Redirects to the appropriate builder based on feature flag and quotation type.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Invoice::class);

        // Check if enhanced builder is enabled
        if (config('features.invoice_builder_v2', false)) {
            // If quotation is specified, route to appropriate builder
            if ($request->filled('quotation_id')) {
                $quotation = Quotation::forCompany()->findOrFail($request->quotation_id);
                $this->authorize('view', $quotation);

                // Route based on quotation type
                if ($quotation->type === 'service') {
                    return redirect()->route('invoices.create.services', ['quotation_id' => $quotation->id]);
                }
            }

            // Default to product builder
            return redirect()->route('invoices.create.products', $request->only(['quotation_id']));
        }

        // Fallback to existing create form if feature flag is disabled
        $quotation = null;
        if ($request->filled('quotation_id')) {
            $quotation = Quotation::forCompany()->findOrFail($request->quotation_id);
            $this->authorize('view', $quotation);
        }

        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator', 'finance_manager']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('invoices.create', compact('quotation', 'teams', 'assignees'));
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $type = $request->input('type', Invoice::TYPE_PRODUCT);

        $validated = $request->validate([
            'type' => ['nullable', Rule::in(array_keys(Invoice::getTypes()))],
            'quotation_id' => 'nullable|exists:quotations,id',
            'team_id' => 'nullable|exists:teams,id',
            'assigned_to' => 'nullable|exists:users,id',
            'customer_segment_id' => 'nullable|exists:customer_segments,id',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'customer_address' => 'nullable|string',
            'customer_city' => 'nullable|string|max:100',
            'customer_state' => 'nullable|string|max:100',
            'customer_postal_code' => 'nullable|string|max:20',
            'title' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'due_date' => 'required|date|after_or_equal:today',
            'payment_terms_days' => 'nullable|integer|min:0|max:365',
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
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
        ]);

        $validated['type'] = $type;
        $validated['payment_terms'] = $validated['payment_terms'] ?? $validated['payment_terms_days'] ?? 30;
        unset($validated['payment_terms_days']);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['created_by'] = auth()->id();
        $validated['status'] = Invoice::STATUS_DRAFT;

        $items = $validated['items'];
        unset($validated['items']);

        $invoice = Invoice::create($validated);

        foreach ($items as $index => $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
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

        $invoice->fresh()->calculateTotals();
        $invoice->save();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load([
            'quotation',
            'team',
            'assignedTo',
            'createdBy',
            'customerSegment',
            'items',
            'paymentRecords' => function ($query) {
                $query->orderBy('payment_date', 'desc');
            }
        ]);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit(Invoice $invoice): View
    {
        $this->authorize('update', $invoice);

        if (!$invoice->canBeEdited()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'This invoice cannot be edited in its current status.');
        }

        $invoice->load(['items']);

        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator', 'finance_manager']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('invoices.edit', compact('invoice', 'teams', 'assignees'));
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        if (!$invoice->canBeEdited()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'This invoice cannot be edited in its current status.');
        }

        $validated = $request->validate([
            'team_id' => 'nullable|exists:teams,id',
            'assigned_to' => 'nullable|exists:users,id',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'customer_address' => 'nullable|string',
            'customer_city' => 'nullable|string|max:100',
            'customer_state' => 'nullable|string|max:100',
            'customer_postal_code' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'notes' => 'nullable|string',
            'due_date' => 'required|date',
            'payment_terms_days' => 'required|integer|min:0|max:365',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'internal_notes' => 'nullable|string',
        ]);

        $invoice->update($validated);
        $invoice->calculateTotals();
        $invoice->save();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        if (!$invoice->canBeDeleted()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'This invoice cannot be deleted in its current status.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Mark invoice as sent.
     */
    public function markAsSent(Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        if (!$invoice->canBeSent()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'This invoice cannot be sent in its current status.');
        }

        $invoice->markAsSent();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice marked as sent.');
    }

    /**
     * Record a payment for the invoice.
     */
    public function recordPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'payment_method' => 'required|string|in:CASH,CHEQUE,BANK_TRANSFER,CREDIT_CARD,ONLINE_BANKING,OTHER',
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->amount_due,
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:255',
            'clearance_date' => 'nullable|date',
        ]);

        // Create payment record
        $payment = PaymentRecord::create([
            'invoice_id' => $invoice->id,
            'company_id' => $invoice->company_id,
            'payment_method' => $validated['payment_method'],
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'reference_number' => $validated['reference_number'],
            'notes' => $validated['notes'],
            'clearance_date' => $validated['clearance_date'] ?? $validated['payment_date'],
            'status' => $validated['clearance_date'] ? 'CLEARED' : 'PENDING',
            'recorded_by' => auth()->id(),
        ]);

        // Update invoice payment status
        $invoice->updatePaymentStatus();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Payment recorded successfully. Receipt #' . $payment->receipt_number);
    }

    /**
     * Show payment recording form.
     */
    public function showPaymentForm(Invoice $invoice): View
    {
        $this->authorize('update', $invoice);

        if ($invoice->status === 'PAID') {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'This invoice is already fully paid.');
        }

        return view('invoices.payment', compact('invoice'));
    }

    /**
     * Create invoice from quotation.
     */
    public function createFromQuotation(Quotation $quotation): RedirectResponse
    {
        $this->authorize('view', $quotation);
        $this->authorize('create', Invoice::class);

        if ($quotation->invoice) {
            return redirect()->route('invoices.show', $quotation->invoice)
                ->with('info', 'Invoice already exists for this quotation.');
        }

        if (!$quotation->canBeConverted()) {
            return redirect()->route('quotations.show', $quotation)
                ->with('error', 'This quotation cannot be converted to an invoice.');
        }

        $invoice = Invoice::createFromQuotation($quotation);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully from quotation.');
    }

    /**
     * Download PDF for invoice.
     */
    public function downloadPDF(Invoice $invoice, PDFService $pdfService)
    {
        $this->authorize('view', $invoice);

        try {
            return $pdfService->downloadPDF($invoice, 'invoice');
        } catch (\Exception $e) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Preview PDF for invoice.
     */
    public function previewPDF(Invoice $invoice, PDFService $pdfService)
    {
        $this->authorize('view', $invoice);

        try {
            return $pdfService->streamPDF($invoice, 'invoice');
        } catch (\Exception $e) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Show the product invoice creation form.
     */
    public function createProduct(Request $request): View
    {
        $this->authorize('create', Invoice::class);

        $quotation = null;
        if ($request->filled('quotation_id')) {
            $quotation = Quotation::forCompany()->findOrFail($request->quotation_id);
            $this->authorize('view', $quotation);

            // Ensure quotation type matches
            if ($quotation->type !== 'product') {
                return redirect()->route('invoices.create.services', ['quotation_id' => $quotation->id])
                    ->with('info', 'Redirected to service invoice builder for service quotation.');
            }
        }

        $formData = $this->buildInvoiceFormPayload($quotation);

        return view('invoices.create-product', array_merge($formData, ['quotation' => $quotation]));
    }

    /**
     * Show the service invoice creation form.
     */
    public function createService(Request $request): View
    {
        $this->authorize('create', Invoice::class);

        $quotation = null;
        if ($request->filled('quotation_id')) {
            $quotation = Quotation::forCompany()->findOrFail($request->quotation_id);
            $this->authorize('view', $quotation);

            // Ensure quotation type matches
            if ($quotation->type !== 'service') {
                return redirect()->route('invoices.create.products', ['quotation_id' => $quotation->id])
                    ->with('info', 'Redirected to product invoice builder for product quotation.');
            }
        }

        $formData = $this->buildInvoiceFormPayload($quotation);

        return view('invoices.create-service', array_merge($formData, ['quotation' => $quotation]));
    }

    /**
     * Build common form payload for invoice creation.
     */
    private function buildInvoiceFormPayload(?Quotation $quotation = null): array
    {
        // Get teams for assignment
        $teams = Team::forCompany()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get assignees (sales and finance staff)
        $assignees = User::forCompany()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['sales_executive', 'sales_coordinator', 'finance_manager']);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get customer segments for pricing
        $customerSegments = CustomerSegment::forCompany()
            ->orderBy('name')
            ->get();

        // Get next invoice number preview
        $nextNumber = Invoice::generateNumber();

        // Get document defaults from company settings
        $company = auth()->user()->company;
        $documentDefaults = $company->settings['document'] ?? [];

        // Get recent quotations/leads for client shortlist
        $recentClients = collect();

        // Add recent quotation customers
        $recentQuotations = Quotation::forCompany()
            ->select('customer_name', 'customer_email', 'customer_phone')
            ->whereNotNull('customer_name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($quotation) {
                return [
                    'name' => $quotation->customer_name,
                    'email' => $quotation->customer_email,
                    'phone' => $quotation->customer_phone,
                    'source' => 'quotation'
                ];
            });

        // Add recent leads
        $recentLeads = Lead::forCompany()
            ->select('name', 'email', 'phone')
            ->whereNotNull('name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($lead) {
                return [
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'source' => 'lead'
                ];
            });

        $recentClients = $recentQuotations->concat($recentLeads)
            ->unique('phone')
            ->take(15);

        return [
            'teams' => $teams,
            'assignees' => $assignees,
            'customerSegments' => $customerSegments,
            'nextNumber' => $nextNumber,
            'documentDefaults' => $documentDefaults,
            'recentClients' => $recentClients,
        ];
    }
}
