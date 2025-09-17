<?php

namespace App\Http\Controllers\CustomerPortal;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Services\PDFService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    protected $pdfService;

    public function __construct(PDFService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function index(Request $request)
    {
        $user = Auth::guard('customer-portal')->user();
        
        $query = $user->getAccessibleQuotations();
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }
        
        // Sort by created date, newest first
        $query->orderBy('created_at', 'desc');
        
        $quotations = $query->paginate(10)->withQueryString();
        
        // Get status counts for filter badges
        $statusCounts = [
            'all' => $user->getAccessibleQuotations()->count(),
            'DRAFT' => $user->getAccessibleQuotations()->where('status', 'DRAFT')->count(),
            'SENT' => $user->getAccessibleQuotations()->where('status', 'SENT')->count(),
            'VIEWED' => $user->getAccessibleQuotations()->where('status', 'VIEWED')->count(),
            'ACCEPTED' => $user->getAccessibleQuotations()->where('status', 'ACCEPTED')->count(),
            'REJECTED' => $user->getAccessibleQuotations()->where('status', 'REJECTED')->count(),
            'EXPIRED' => $user->getAccessibleQuotations()->where('status', 'EXPIRED')->count(),
        ];
        
        return view('customer-portal.quotations.index', compact('quotations', 'statusCounts'));
    }

    public function show(Quotation $quotation)
    {
        $user = Auth::guard('customer-portal')->user();
        
        // Check if user can access this quotation
        if (!$user->canAccessQuotation($quotation->id)) {
            abort(403, 'You do not have permission to view this quotation.');
        }
        
        // Mark quotation as viewed if it was sent
        if ($quotation->status === 'SENT') {
            $quotation->update(['status' => 'VIEWED']);
        }
        
        // Load relationships
        $quotation->load(['items', 'sections.items', 'createdBy', 'company']);
        
        return view('customer-portal.quotations.show', compact('quotation'));
    }

    public function downloadPDF(Quotation $quotation)
    {
        $user = Auth::guard('customer-portal')->user();
        
        // Check if user can access this quotation
        if (!$user->canAccessQuotation($quotation->id)) {
            abort(403, 'You do not have permission to download this quotation.');
        }
        
        // Check if user can download PDFs
        if (!$user->can_download_pdfs) {
            abort(403, 'PDF download is not enabled for your account.');
        }
        
        try {
            $pdf = $this->pdfService->generateQuotationPDF($quotation);
            $filename = "quotation-{$quotation->number}.pdf";
            
            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        } catch (\Exception $e) {
            return back()->withErrors(['pdf' => 'Unable to generate PDF. Please try again later.']);
        }
    }

    public function accept(Request $request, Quotation $quotation)
    {
        $user = Auth::guard('customer-portal')->user();
        
        // Check if user can access this quotation
        if (!$user->canAccessQuotation($quotation->id)) {
            abort(403, 'You do not have permission to accept this quotation.');
        }
        
        // Validate the quotation can be accepted
        if (!in_array($quotation->status, ['SENT', 'VIEWED'])) {
            return back()->withErrors(['status' => 'This quotation cannot be accepted in its current status.']);
        }
        
        // Update quotation status
        $quotation->update([
            'status' => 'ACCEPTED',
            'accepted_at' => now(),
            'customer_notes' => $request->input('notes'),
        ]);
        
        // Log activity (if you have activity logging)
        // QuotationActivity::create([...]);
        
        return redirect()->route('customer-portal.quotations.show', $quotation)
            ->with('success', 'Quotation accepted successfully!');
    }

    public function reject(Request $request, Quotation $quotation)
    {
        $user = Auth::guard('customer-portal')->user();
        
        // Check if user can access this quotation
        if (!$user->canAccessQuotation($quotation->id)) {
            abort(403, 'You do not have permission to reject this quotation.');
        }
        
        // Validate the quotation can be rejected
        if (!in_array($quotation->status, ['SENT', 'VIEWED'])) {
            return back()->withErrors(['status' => 'This quotation cannot be rejected in its current status.']);
        }
        
        // Validate rejection reason is provided
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        
        // Update quotation status
        $quotation->update([
            'status' => 'REJECTED',
            'rejected_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'customer_notes' => $request->input('notes'),
        ]);
        
        return redirect()->route('customer-portal.quotations.show', $quotation)
            ->with('success', 'Quotation rejected. Our team will contact you soon.');
    }
}