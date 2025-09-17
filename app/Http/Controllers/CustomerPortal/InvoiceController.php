<?php

namespace App\Http\Controllers\CustomerPortal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\PDFService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    protected $pdfService;

    public function __construct(PDFService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function index(Request $request)
    {
        $user = Auth::guard('customer-portal')->user();
        
        $query = $user->getAccessibleInvoices();
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }
        
        // Sort by created date, newest first
        $query->orderBy('created_at', 'desc');
        
        $invoices = $query->paginate(10)->withQueryString();
        
        // Get status counts for filter badges
        $statusCounts = [
            'all' => $user->getAccessibleInvoices()->count(),
            'DRAFT' => $user->getAccessibleInvoices()->where('status', 'DRAFT')->count(),
            'SENT' => $user->getAccessibleInvoices()->where('status', 'SENT')->count(),
            'UNPAID' => $user->getAccessibleInvoices()->where('status', 'UNPAID')->count(),
            'PARTIAL' => $user->getAccessibleInvoices()->where('status', 'PARTIAL')->count(),
            'PAID' => $user->getAccessibleInvoices()->where('status', 'PAID')->count(),
            'OVERDUE' => $user->getAccessibleInvoices()->where('status', 'OVERDUE')->count(),
        ];
        
        // Calculate financial summary
        $financialSummary = [
            'total_amount' => $user->getAccessibleInvoices()->sum('total'),
            'paid_amount' => $user->getAccessibleInvoices()->sum('paid_amount'),
            'outstanding_amount' => $user->getAccessibleInvoices()->sum('outstanding_amount'),
            'overdue_amount' => $user->getAccessibleInvoices()->where('status', 'OVERDUE')->sum('outstanding_amount'),
        ];
        
        return view('customer-portal.invoices.index', compact('invoices', 'statusCounts', 'financialSummary'));
    }

    public function show(Invoice $invoice)
    {
        $user = Auth::guard('customer-portal')->user();
        
        // Check if user can access this invoice
        if (!$user->canAccessInvoice($invoice->id)) {
            abort(403, 'You do not have permission to view this invoice.');
        }
        
        // Load relationships
        $invoice->load(['items', 'quotation', 'paymentRecords', 'createdBy', 'company']);
        
        return view('customer-portal.invoices.show', compact('invoice'));
    }

    public function downloadPDF(Invoice $invoice)
    {
        $user = Auth::guard('customer-portal')->user();
        
        // Check if user can access this invoice
        if (!$user->canAccessInvoice($invoice->id)) {
            abort(403, 'You do not have permission to download this invoice.');
        }
        
        // Check if user can download PDFs
        if (!$user->can_download_pdfs) {
            abort(403, 'PDF download is not enabled for your account.');
        }
        
        try {
            $pdf = $this->pdfService->generateInvoicePDF($invoice);
            $filename = "invoice-{$invoice->number}.pdf";
            
            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        } catch (\Exception $e) {
            return back()->withErrors(['pdf' => 'Unable to generate PDF. Please try again later.']);
        }
    }

    public function payments(Request $request)
    {
        $user = Auth::guard('customer-portal')->user();
        
        // Check if user can view payment history
        if (!$user->can_view_payment_history) {
            abort(403, 'Payment history access is not enabled for your account.');
        }
        
        // Get all payment records for accessible invoices
        $query = \App\Models\PaymentRecord::whereHas('invoice', function ($q) use ($user) {
            $accessibleInvoiceIds = $user->getAccessibleInvoices()->pluck('id');
            $q->whereIn('id', $accessibleInvoiceIds);
        });
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('invoice', function ($invoiceQuery) use ($search) {
                      $invoiceQuery->where('number', 'like', "%{$search}%");
                  });
            });
        }
        
        // Sort by payment date, newest first
        $query->orderBy('payment_date', 'desc');
        
        $payments = $query->with(['invoice'])->paginate(15)->withQueryString();
        
        // Calculate payment summary
        $paymentSummary = [
            'total_payments' => $query->sum('amount'),
            'cleared_payments' => $query->where('status', 'CLEARED')->sum('amount'),
            'pending_payments' => $query->where('status', 'PENDING')->sum('amount'),
            'payment_count' => $query->count(),
        ];
        
        return view('customer-portal.payments.index', compact('payments', 'paymentSummary'));
    }
}