<?php

namespace App\Http\Controllers\CustomerPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('customer-portal')->user();
        
        // Get accessible quotations
        $quotations = $user->getAccessibleQuotations()
            ->limit(5)
            ->get();
        
        // Get accessible invoices
        $invoices = $user->getAccessibleInvoices()
            ->limit(5)
            ->get();
        
        // Calculate dashboard statistics
        $stats = [
            'total_quotations' => $user->quotations_count,
            'total_invoices' => $user->invoices_count,
            'outstanding_balance' => $user->outstanding_balance,
            'total_paid' => $user->total_paid,
            'overdue_invoices' => $user->overdue_invoices_count,
        ];
        
        return view('customer-portal.dashboard', compact('user', 'quotations', 'invoices', 'stats'));
    }
}