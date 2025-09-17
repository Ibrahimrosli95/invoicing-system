<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\PaymentRecord;
use App\Models\User;
use App\Models\Team;
use App\Models\CustomerSegment;
use App\Models\PricingItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Role-based dashboard routing
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return $this->executiveDashboard();
        } elseif ($user->hasAnyRole(['finance_manager'])) {
            return $this->financeDashboard();
        } elseif ($user->hasAnyRole(['sales_manager'])) {
            return $this->salesManagerDashboard();
        } elseif ($user->hasAnyRole(['sales_coordinator'])) {
            return $this->teamDashboard();
        } else {
            return $this->individualDashboard();
        }
    }

    public function executiveDashboard()
    {
        $metrics = $this->getExecutiveMetrics();
        $charts = $this->getExecutiveCharts();
        
        return view('dashboard.executive', compact('metrics', 'charts'));
    }

    public function salesManagerDashboard()
    {
        $user = auth()->user();
        $managedTeams = Team::where('manager_id', $user->id)->pluck('id');
        
        $metrics = $this->getTeamMetrics($managedTeams);
        $charts = $this->getTeamCharts($managedTeams);
        
        return view('dashboard.sales-manager', compact('metrics', 'charts'));
    }

    public function teamDashboard()
    {
        $user = auth()->user();
        $coordinatedTeams = Team::where('coordinator_id', $user->id)->pluck('id');
        
        $metrics = $this->getTeamMetrics($coordinatedTeams);
        $charts = $this->getTeamCharts($coordinatedTeams);
        
        return view('dashboard.team', compact('metrics', 'charts'));
    }

    public function individualDashboard()
    {
        $user = auth()->user();
        
        $metrics = $this->getIndividualMetrics($user);
        $charts = $this->getIndividualCharts($user);
        
        return view('dashboard.individual', compact('metrics', 'charts'));
    }

    public function financeDashboard()
    {
        $metrics = $this->getFinancialMetrics();
        $charts = $this->getFinancialCharts();
        
        return view('dashboard.finance', compact('metrics', 'charts'));
    }

    private function getExecutiveMetrics()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfYear = Carbon::now()->startOfYear();
        
        return [
            // Revenue Metrics
            'monthly_revenue' => Invoice::forCompany()
                ->where('status', 'PAID')
                ->where('created_at', '>=', $startOfMonth)
                ->sum('total'),
            'yearly_revenue' => Invoice::forCompany()
                ->where('status', 'PAID')
                ->where('created_at', '>=', $startOfYear)
                ->sum('total'),
            'outstanding_amount' => Invoice::forCompany()
                ->whereIn('status', ['SENT', 'PARTIAL', 'OVERDUE'])
                ->sum('amount_due'),
            
            // Pipeline Metrics
            'active_leads' => Lead::forCompany()->whereIn('status', ['NEW', 'CONTACTED'])->count(),
            'pending_quotations' => Quotation::forCompany()->whereIn('status', ['DRAFT', 'SENT'])->count(),
            'overdue_invoices' => Invoice::forCompany()->where('status', 'OVERDUE')->count(),
            
            // Conversion Metrics
            'lead_conversion_rate' => $this->calculateConversionRate('leads', 'quotations'),
            'quotation_conversion_rate' => $this->calculateConversionRate('quotations', 'invoices'),
            'average_deal_size' => Invoice::forCompany()->where('status', 'PAID')->avg('total') ?: 0,
            
            // Team Performance
            'total_teams' => Team::forCompany()->count(),
            'active_users' => User::forCompany()->where('is_active', true)->count(),
            'top_performer' => $this->getTopPerformer(),
        ];
    }

    private function getExecutiveCharts()
    {
        return [
            'monthly_revenue_trend' => $this->getMonthlyRevenueTrend(),
            'lead_conversion_funnel' => $this->getConversionFunnel(),
            'team_performance_ranking' => $this->getTeamPerformanceRanking(),
            'customer_segment_revenue' => $this->getSegmentRevenueBreakdown(),
            'invoice_aging_chart' => $this->getInvoiceAgingChart(),
        ];
    }

    private function getTeamMetrics($teamIds)
    {
        if ($teamIds->isEmpty()) {
            return $this->getEmptyMetrics();
        }

        $startOfMonth = Carbon::now()->startOfMonth();
        
        return [
            'team_leads' => Lead::forCompany()->whereIn('team_id', $teamIds)->count(),
            'team_quotations' => Quotation::forCompany()->whereIn('team_id', $teamIds)->count(),
            'team_revenue' => Invoice::forCompany()
                ->whereIn('team_id', $teamIds)
                ->where('status', 'PAID')
                ->where('created_at', '>=', $startOfMonth)
                ->sum('total'),
            'team_conversion_rate' => $this->calculateTeamConversionRate($teamIds),
            'pending_follow_ups' => Lead::forCompany()
                ->whereIn('team_id', $teamIds)
                ->whereIn('status', ['NEW', 'CONTACTED'])
                ->count(),
        ];
    }

    private function getTeamCharts($teamIds)
    {
        return [
            'team_pipeline' => $this->getTeamPipeline($teamIds),
            'individual_performance' => $this->getIndividualPerformanceInTeam($teamIds),
            'lead_sources' => $this->getLeadSourceBreakdown($teamIds),
        ];
    }

    private function getIndividualMetrics($user)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        
        return [
            'my_leads' => Lead::forCompany()->where('assigned_to', $user->id)->count(),
            'my_quotations' => Quotation::forCompany()->where('assigned_to', $user->id)->count(),
            'my_revenue' => Invoice::forCompany()
                ->where('assigned_to', $user->id)
                ->where('status', 'PAID')
                ->where('created_at', '>=', $startOfMonth)
                ->sum('total'),
            'pending_tasks' => $this->getPendingTasks($user),
            'conversion_rate' => $this->calculateIndividualConversionRate($user->id),
        ];
    }

    private function getIndividualCharts($user)
    {
        return [
            'personal_pipeline' => $this->getPersonalPipeline($user->id),
            'monthly_performance' => $this->getMonthlyPerformance($user->id),
            'activity_calendar' => $this->getActivityCalendar($user->id),
        ];
    }

    private function getFinancialMetrics()
    {
        return [
            'total_outstanding' => Invoice::forCompany()->whereIn('status', ['SENT', 'PARTIAL', 'OVERDUE'])->sum('amount_due'),
            'overdue_amount' => Invoice::forCompany()->where('status', 'OVERDUE')->sum('amount_due'),
            'paid_this_month' => Invoice::forCompany()
                ->where('status', 'PAID')
                ->where('updated_at', '>=', Carbon::now()->startOfMonth())
                ->sum('total'),
            'average_payment_time' => $this->getAveragePaymentTime(),
            'aging_30_days' => $this->getAgingAmount(30),
            'aging_60_days' => $this->getAgingAmount(60),
            'aging_90_days' => $this->getAgingAmount(90),
        ];
    }

    private function getFinancialCharts()
    {
        return [
            'payment_trends' => $this->getPaymentTrends(),
            'aging_buckets' => $this->getAgingBuckets(),
            'customer_payment_history' => $this->getCustomerPaymentHistory(),
        ];
    }

    // Helper Methods
    private function calculateConversionRate($from, $to)
    {
        $fromCount = $this->getModelCount($from);
        $toCount = $this->getModelCount($to);
        
        return $fromCount > 0 ? round(($toCount / $fromCount) * 100, 1) : 0;
    }

    private function getModelCount($model)
    {
        $startOfYear = Carbon::now()->startOfYear();
        
        return match($model) {
            'leads' => Lead::forCompany()->where('created_at', '>=', $startOfYear)->count(),
            'quotations' => Quotation::forCompany()->where('created_at', '>=', $startOfYear)->count(),
            'invoices' => Invoice::forCompany()->where('created_at', '>=', $startOfYear)->count(),
            default => 0
        };
    }

    private function getTopPerformer()
    {
        return User::forCompany()
            ->whereHas('assignedInvoices', function($query) {
                $query->where('status', 'PAID')
                      ->where('created_at', '>=', Carbon::now()->startOfMonth());
            })
            ->withSum(['assignedInvoices' => function($query) {
                $query->where('status', 'PAID')
                      ->where('created_at', '>=', Carbon::now()->startOfMonth());
            }], 'total')
            ->orderByDesc('assigned_invoices_sum_total')
            ->first();
    }

    private function getMonthlyRevenueTrend()
    {
        return Invoice::forCompany()
            ->where('status', 'PAID')
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total) as revenue')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->map(function($item) {
                return [
                    'period' => Carbon::createFromDate($item->year, $item->month, 1)->format('M Y'),
                    'revenue' => (float) $item->revenue
                ];
            });
    }

    private function getConversionFunnel()
    {
        $startOfYear = Carbon::now()->startOfYear();
        
        return [
            'leads' => Lead::forCompany()->where('created_at', '>=', $startOfYear)->count(),
            'quotations' => Quotation::forCompany()->where('created_at', '>=', $startOfYear)->count(),
            'accepted_quotations' => Quotation::forCompany()->where('status', 'ACCEPTED')->where('created_at', '>=', $startOfYear)->count(),
            'invoices' => Invoice::forCompany()->where('created_at', '>=', $startOfYear)->count(),
            'paid_invoices' => Invoice::forCompany()->where('status', 'PAID')->where('created_at', '>=', $startOfYear)->count(),
        ];
    }

    private function getTeamPerformanceRanking()
    {
        return Team::forCompany()
            ->withSum(['quotations' => function($query) {
                $query->whereHas('invoice', function($subQuery) {
                    $subQuery->where('status', 'PAID')
                             ->where('created_at', '>=', Carbon::now()->startOfMonth());
                });
            }], 'total')
            ->orderByDesc('quotations_sum_total')
            ->limit(10)
            ->get()
            ->map(function($team) {
                return [
                    'name' => $team->name,
                    'revenue' => (float) ($team->quotations_sum_total ?: 0),
                    'member_count' => $team->users()->count()
                ];
            });
    }

    private function getSegmentRevenueBreakdown()
    {
        return CustomerSegment::forCompany()
            ->withSum(['quotations.invoice' => function($query) {
                $query->where('status', 'PAID')
                      ->where('created_at', '>=', Carbon::now()->startOfYear());
            }], 'total')
            ->get()
            ->map(function($segment) {
                return [
                    'name' => $segment->name,
                    'revenue' => (float) ($segment->quotations_invoice_sum_total ?: 0),
                    'color' => $segment->color ?: '#6B7280'
                ];
            });
    }

    private function getInvoiceAgingChart()
    {
        $overdue = Invoice::forCompany()->where('status', 'OVERDUE');
        
        return [
            '0-30' => (clone $overdue)->where('due_date', '>', Carbon::now()->subDays(30))->sum('amount_due'),
            '31-60' => (clone $overdue)->whereBetween('due_date', [Carbon::now()->subDays(60), Carbon::now()->subDays(30)])->sum('amount_due'),
            '61-90' => (clone $overdue)->whereBetween('due_date', [Carbon::now()->subDays(90), Carbon::now()->subDays(60)])->sum('amount_due'),
            '90+' => (clone $overdue)->where('due_date', '<', Carbon::now()->subDays(90))->sum('amount_due'),
        ];
    }

    private function calculateTeamConversionRate($teamIds)
    {
        $quotations = Quotation::forCompany()->whereIn('team_id', $teamIds)->count();
        $invoices = Invoice::forCompany()->whereIn('team_id', $teamIds)->count();
        
        return $quotations > 0 ? round(($invoices / $quotations) * 100, 1) : 0;
    }

    private function calculateIndividualConversionRate($userId)
    {
        $quotations = Quotation::forCompany()->where('assigned_to', $userId)->count();
        $invoices = Invoice::forCompany()->where('assigned_to', $userId)->count();
        
        return $quotations > 0 ? round(($invoices / $quotations) * 100, 1) : 0;
    }

    private function getPendingTasks($user)
    {
        return Lead::forCompany()
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['NEW', 'CONTACTED'])
            ->count() + 
            Quotation::forCompany()
            ->where('assigned_to', $user->id)
            ->where('status', 'DRAFT')
            ->count();
    }

    private function getEmptyMetrics()
    {
        return [
            'team_leads' => 0,
            'team_quotations' => 0,
            'team_revenue' => 0,
            'team_conversion_rate' => 0,
            'pending_follow_ups' => 0,
        ];
    }

    private function getTeamPipeline($teamIds)
    {
        return Lead::forCompany()
            ->whereIn('team_id', $teamIds)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
    }

    private function getIndividualPerformanceInTeam($teamIds)
    {
        return User::forCompany()
            ->whereHas('teams', function($query) use ($teamIds) {
                $query->whereIn('team_id', $teamIds);
            })
            ->withCount(['assignedQuotations', 'assignedInvoices'])
            ->withSum(['assignedInvoices' => function($query) {
                $query->where('status', 'PAID')
                      ->where('created_at', '>=', Carbon::now()->startOfMonth());
            }], 'total')
            ->orderByDesc('assigned_invoices_sum_total')
            ->limit(10)
            ->get();
    }

    private function getLeadSourceBreakdown($teamIds)
    {
        return Lead::forCompany()
            ->whereIn('team_id', $teamIds)
            ->selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->get()
            ->pluck('count', 'source');
    }

    private function getPersonalPipeline($userId)
    {
        return [
            'leads' => Lead::forCompany()->where('assigned_to', $userId)->count(),
            'quotations' => Quotation::forCompany()->where('assigned_to', $userId)->count(),
            'invoices' => Invoice::forCompany()->where('assigned_to', $userId)->count(),
        ];
    }

    private function getMonthlyPerformance($userId)
    {
        return Invoice::forCompany()
            ->where('assigned_to', $userId)
            ->where('status', 'PAID')
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total) as revenue')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();
    }

    private function getActivityCalendar($userId)
    {
        // This would return recent activities for calendar display
        return Lead::forCompany()
            ->where('assigned_to', $userId)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get(['id', 'customer_name', 'status', 'created_at']);
    }

    private function getAveragePaymentTime()
    {
        return PaymentRecord::forCompany()
            ->whereHas('invoice')
            ->selectRaw('AVG(DATEDIFF(payment_date, invoice.created_at)) as avg_days')
            ->join('invoices as invoice', 'payment_records.invoice_id', '=', 'invoice.id')
            ->value('avg_days') ?: 0;
    }

    private function getAgingAmount($days)
    {
        return Invoice::forCompany()
            ->where('status', 'OVERDUE')
            ->where('due_date', '<', Carbon::now()->subDays($days))
            ->sum('amount_due');
    }

    private function getPaymentTrends()
    {
        return PaymentRecord::forCompany()
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(amount) as total')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get();
    }

    private function getAgingBuckets()
    {
        return $this->getInvoiceAgingChart(); // Reuse the same method
    }

    private function getCustomerPaymentHistory()
    {
        return Invoice::forCompany()
            ->selectRaw('customer_name, COUNT(*) as invoice_count, AVG(DATEDIFF(COALESCE(payments.payment_date, NOW()), invoices.created_at)) as avg_payment_days')
            ->leftJoin('payment_records as payments', 'invoices.id', '=', 'payments.invoice_id')
            ->where('invoices.created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('customer_name')
            ->orderByDesc('invoice_count')
            ->limit(10)
            ->get();
    }
}
