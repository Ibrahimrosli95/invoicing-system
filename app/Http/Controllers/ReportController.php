<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\PaymentRecord;
use App\Models\User;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;

class ReportController extends Controller
{
    /**
     * Display the report builder interface
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get available report types based on user permissions
        $reportTypes = $this->getAvailableReportTypes($user);
        
        // Get recent reports
        $recentReports = $this->getRecentReports($user);
        
        // Get report templates
        $templates = $this->getReportTemplates($user);
        
        return view('reports.index', compact('reportTypes', 'recentReports', 'templates'));
    }
    
    /**
     * Show the report builder interface
     */
    public function builder(Request $request)
    {
        $reportType = $request->get('type', 'leads');
        $user = auth()->user();
        
        // Validate report type access
        if (!$this->canAccessReportType($user, $reportType)) {
            abort(403, 'Unauthorized access to this report type');
        }
        
        // Get available fields for the report type
        $availableFields = $this->getAvailableFields($reportType, $user);
        
        // Get filter options
        $filterOptions = $this->getFilterOptions($reportType, $user);
        
        // Get chart options
        $chartOptions = $this->getChartOptions($reportType);
        
        return view('reports.builder', compact(
            'reportType', 
            'availableFields', 
            'filterOptions', 
            'chartOptions'
        ));
    }
    
    /**
     * Generate and display a custom report
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|string|in:leads,quotations,invoices,payments,sales_performance,financial',
            'fields' => 'required|array|min:1',
            'fields.*' => 'required|string',
            'filters' => 'sometimes|array',
            'date_range' => 'sometimes|array',
            'date_range.from' => 'sometimes|date',
            'date_range.to' => 'sometimes|date',
            'group_by' => 'sometimes|string',
            'sort_by' => 'sometimes|string',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'chart_type' => 'sometimes|string|in:table,bar,line,pie,doughnut',
            'limit' => 'sometimes|integer|min:1|max:10000'
        ]);
        
        $user = auth()->user();
        
        // Validate access to report type
        if (!$this->canAccessReportType($user, $validated['report_type'])) {
            abort(403, 'Unauthorized access to this report type');
        }
        
        // Generate the report data
        $reportData = $this->generateReportData($validated, $user);
        
        // Prepare chart data if needed
        $chartData = null;
        if (isset($validated['chart_type']) && $validated['chart_type'] !== 'table') {
            $chartData = $this->prepareChartData($reportData, $validated);
        }
        
        // Store report in session for export
        session(['last_report' => $validated, 'last_report_data' => $reportData]);
        
        return view('reports.results', compact(
            'reportData', 
            'validated', 
            'chartData'
        ));
    }
    
    /**
     * Export report to various formats
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $reportConfig = session('last_report');
        $reportData = session('last_report_data');
        
        if (!$reportConfig || !$reportData) {
            return redirect()->route('reports.index')
                ->with('error', 'No report data available for export. Please generate a report first.');
        }
        
        $filename = 'report_' . $reportConfig['report_type'] . '_' . now()->format('Y_m_d_His');
        
        switch ($format) {
            case 'csv':
                return $this->exportToCsv($reportData, $reportConfig, $filename);
            case 'excel':
            case 'xlsx':
                return $this->exportToExcel($reportData, $reportConfig, $filename);
            case 'pdf':
                return $this->exportToPdf($reportData, $reportConfig, $filename);
            default:
                return redirect()->back()->with('error', 'Invalid export format');
        }
    }
    
    /**
     * Save a report template
     */
    public function saveTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'sometimes|string|max:500',
            'report_config' => 'required|array'
        ]);
        
        $user = auth()->user();
        
        // Store template in database (you'll need to create a report_templates table)
        // For now, we'll store in session as example
        $templates = session('report_templates', []);
        $templates[] = [
            'id' => count($templates) + 1,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'config' => $validated['report_config'],
            'user_id' => $user->id,
            'created_at' => now()
        ];
        session(['report_templates' => $templates]);
        
        return response()->json(['success' => true, 'message' => 'Template saved successfully']);
    }
    
    /**
     * Load a report template
     */
    public function loadTemplate($templateId)
    {
        $templates = session('report_templates', []);
        $template = collect($templates)->firstWhere('id', $templateId);
        
        if (!$template) {
            return response()->json(['error' => 'Template not found'], 404);
        }
        
        return response()->json(['template' => $template]);
    }
    
    /**
     * Get available report types based on user permissions
     */
    private function getAvailableReportTypes($user)
    {
        $reportTypes = [];
        
        if ($user->can('viewAny', Lead::class)) {
            $reportTypes['leads'] = [
                'name' => 'Lead Reports',
                'description' => 'Generate reports on leads, conversions, and pipeline performance',
                'icon' => 'users'
            ];
        }
        
        if ($user->can('viewAny', Quotation::class)) {
            $reportTypes['quotations'] = [
                'name' => 'Quotation Reports', 
                'description' => 'Analyze quotation performance, conversion rates, and revenue',
                'icon' => 'document-text'
            ];
        }
        
        if ($user->can('viewAny', Invoice::class)) {
            $reportTypes['invoices'] = [
                'name' => 'Invoice Reports',
                'description' => 'Track invoice status, payment collection, and aging',
                'icon' => 'receipt-tax'
            ];
        }
        
        if ($user->hasAnyRole(['finance_manager', 'company_manager', 'superadmin'])) {
            $reportTypes['payments'] = [
                'name' => 'Payment Reports',
                'description' => 'Payment collection analysis and cash flow reports',
                'icon' => 'cash'
            ];
            
            $reportTypes['financial'] = [
                'name' => 'Financial Reports',
                'description' => 'Comprehensive financial analysis and revenue tracking',
                'icon' => 'chart-bar'
            ];
        }
        
        if ($user->hasAnyRole(['sales_manager', 'company_manager', 'superadmin'])) {
            $reportTypes['sales_performance'] = [
                'name' => 'Sales Performance',
                'description' => 'Team and individual sales performance analysis',
                'icon' => 'trending-up'
            ];
        }
        
        return $reportTypes;
    }
    
    /**
     * Get recent reports for the user
     */
    private function getRecentReports($user)
    {
        // This would come from a reports table in a full implementation
        // For now, return sample data
        return [
            [
                'name' => 'Monthly Lead Report',
                'type' => 'leads',
                'generated_at' => now()->subDays(2),
                'records' => 156
            ],
            [
                'name' => 'Quotation Conversion Analysis',
                'type' => 'quotations', 
                'generated_at' => now()->subDays(5),
                'records' => 89
            ]
        ];
    }
    
    /**
     * Get report templates available to the user
     */
    private function getReportTemplates($user)
    {
        return session('report_templates', []);
    }
    
    /**
     * Check if user can access a specific report type
     */
    private function canAccessReportType($user, $reportType)
    {
        switch ($reportType) {
            case 'leads':
                return $user->can('viewAny', Lead::class);
            case 'quotations':
                return $user->can('viewAny', Quotation::class);
            case 'invoices':
                return $user->can('viewAny', Invoice::class);
            case 'payments':
            case 'financial':
                return $user->hasAnyRole(['finance_manager', 'company_manager', 'superadmin']);
            case 'sales_performance':
                return $user->hasAnyRole(['sales_manager', 'company_manager', 'superadmin']);
            default:
                return false;
        }
    }
    
    /**
     * Get available fields for a report type
     */
    private function getAvailableFields($reportType, $user)
    {
        $fields = [];
        
        switch ($reportType) {
            case 'leads':
                $fields = [
                    'company_name' => 'Company Name',
                    'contact_person' => 'Contact Person',
                    'phone' => 'Phone Number',
                    'email' => 'Email',
                    'status' => 'Status',
                    'source' => 'Lead Source',
                    'estimated_value' => 'Estimated Value',
                    'team_name' => 'Assigned Team',
                    'user_name' => 'Assigned Rep',
                    'created_at' => 'Created Date',
                    'last_activity_at' => 'Last Activity',
                ];
                break;
                
            case 'quotations':
                $fields = [
                    'number' => 'Quotation Number',
                    'customer_name' => 'Customer Name',
                    'phone' => 'Phone',
                    'status' => 'Status',
                    'subtotal' => 'Subtotal',
                    'total' => 'Total Amount',
                    'created_by' => 'Created By',
                    'team_name' => 'Team',
                    'created_at' => 'Created Date',
                    'valid_until' => 'Valid Until',
                    'lead_source' => 'Lead Source',
                ];
                break;
                
            case 'invoices':
                $fields = [
                    'number' => 'Invoice Number',
                    'customer_name' => 'Customer Name',
                    'status' => 'Status',
                    'subtotal' => 'Subtotal',
                    'total' => 'Total Amount',
                    'paid_amount' => 'Paid Amount',
                    'balance' => 'Outstanding Balance',
                    'due_date' => 'Due Date',
                    'days_overdue' => 'Days Overdue',
                    'created_by' => 'Created By',
                    'created_at' => 'Created Date',
                ];
                break;
                
            case 'payments':
                $fields = [
                    'receipt_number' => 'Receipt Number',
                    'invoice_number' => 'Invoice Number',
                    'customer_name' => 'Customer',
                    'amount' => 'Amount',
                    'method' => 'Payment Method',
                    'status' => 'Status',
                    'reference_number' => 'Reference',
                    'recorded_by' => 'Recorded By',
                    'recorded_at' => 'Payment Date',
                ];
                break;
                
            case 'sales_performance':
                $fields = [
                    'rep_name' => 'Sales Rep',
                    'team_name' => 'Team',
                    'leads_count' => 'Total Leads',
                    'quotations_count' => 'Quotations Sent',
                    'won_count' => 'Deals Won',
                    'conversion_rate' => 'Conversion Rate (%)',
                    'total_revenue' => 'Total Revenue',
                    'avg_deal_size' => 'Average Deal Size',
                    'period' => 'Period',
                ];
                break;
                
            case 'financial':
                $fields = [
                    'period' => 'Period',
                    'total_revenue' => 'Total Revenue',
                    'outstanding_amount' => 'Outstanding',
                    'overdue_amount' => 'Overdue Amount',
                    'collection_rate' => 'Collection Rate (%)',
                    'avg_payment_days' => 'Avg Payment Days',
                    'new_customers' => 'New Customers',
                ];
                break;
        }
        
        return $fields;
    }
    
    /**
     * Get filter options for a report type
     */
    private function getFilterOptions($reportType, $user)
    {
        $filters = [];
        
        // Common date filters
        $filters['date_range'] = [
            'type' => 'date_range',
            'label' => 'Date Range',
            'options' => [
                'today' => 'Today',
                'yesterday' => 'Yesterday', 
                'this_week' => 'This Week',
                'last_week' => 'Last Week',
                'this_month' => 'This Month',
                'last_month' => 'Last Month',
                'this_quarter' => 'This Quarter',
                'this_year' => 'This Year',
                'custom' => 'Custom Range'
            ]
        ];
        
        // Type-specific filters
        switch ($reportType) {
            case 'leads':
                $filters['status'] = [
                    'type' => 'select',
                    'label' => 'Status',
                    'options' => Lead::getStatuses()
                ];
                $filters['source'] = [
                    'type' => 'select',
                    'label' => 'Lead Source',
                    'options' => Lead::getSources()
                ];
                break;
                
            case 'quotations':
                $filters['status'] = [
                    'type' => 'select',
                    'label' => 'Status',
                    'options' => Quotation::getStatuses()
                ];
                break;
                
            case 'invoices':
                $filters['status'] = [
                    'type' => 'select', 
                    'label' => 'Status',
                    'options' => Invoice::getStatuses()
                ];
                break;
        }
        
        // Team and user filters based on permissions
        if ($user->hasAnyRole(['company_manager', 'superadmin'])) {
            $teams = Team::forCompany()->get();
            $filters['team'] = [
                'type' => 'select',
                'label' => 'Team',
                'options' => $teams->pluck('name', 'id')->toArray()
            ];
        }
        
        return $filters;
    }
    
    /**
     * Get chart options for a report type
     */
    private function getChartOptions($reportType)
    {
        return [
            'table' => 'Data Table',
            'bar' => 'Bar Chart',
            'line' => 'Line Chart',
            'pie' => 'Pie Chart',
            'doughnut' => 'Doughnut Chart'
        ];
    }
    
    /**
     * Generate report data based on configuration
     */
    private function generateReportData($config, $user)
    {
        $query = $this->buildBaseQuery($config['report_type'], $user);
        
        // Apply filters
        if (isset($config['filters'])) {
            $query = $this->applyFilters($query, $config['filters'], $config['report_type']);
        }
        
        // Apply date range
        if (isset($config['date_range'])) {
            $query = $this->applyDateRange($query, $config['date_range'], $config['report_type']);
        }
        
        // Apply sorting
        if (isset($config['sort_by'])) {
            $direction = $config['sort_direction'] ?? 'asc';
            $query = $query->orderBy($config['sort_by'], $direction);
        }
        
        // Apply limit
        $limit = $config['limit'] ?? 1000;
        $query = $query->limit($limit);
        
        return $query->get();
    }
    
    /**
     * Build base query for report type
     */
    private function buildBaseQuery($reportType, $user)
    {
        switch ($reportType) {
            case 'leads':
                return Lead::forCompany()
                    ->with(['team', 'assignedRep'])
                    ->select([
                        'leads.*',
                        DB::raw('teams.name as team_name'),
                        DB::raw('users.name as user_name')
                    ])
                    ->leftJoin('teams', 'leads.team_id', '=', 'teams.id')
                    ->leftJoin('users', 'leads.assigned_to', '=', 'users.id');
                    
            case 'quotations':
                return Quotation::forCompany()
                    ->with(['team', 'createdBy', 'lead'])
                    ->select([
                        'quotations.*',
                        DB::raw('teams.name as team_name'),
                        DB::raw('users.name as created_by'),
                        DB::raw('leads.source as lead_source')
                    ])
                    ->leftJoin('teams', 'quotations.team_id', '=', 'teams.id')
                    ->leftJoin('users', 'quotations.created_by', '=', 'users.id')
                    ->leftJoin('leads', 'quotations.lead_id', '=', 'leads.id');
                    
            case 'invoices':
                return Invoice::forCompany()
                    ->with(['payments'])
                    ->select([
                        'invoices.*',
                        DB::raw('COALESCE(payments_sum.paid_amount, 0) as paid_amount'),
                        DB::raw('(invoices.total - COALESCE(payments_sum.paid_amount, 0)) as balance'),
                        DB::raw('CASE WHEN invoices.due_date < NOW() THEN DATEDIFF(NOW(), invoices.due_date) ELSE 0 END as days_overdue')
                    ])
                    ->leftJoin(
                        DB::raw('(SELECT invoice_id, SUM(amount) as paid_amount FROM payment_records WHERE status = "CLEARED" GROUP BY invoice_id) as payments_sum'),
                        'invoices.id', '=', 'payments_sum.invoice_id'
                    );
                    
            default:
                throw new \Exception('Invalid report type');
        }
    }
    
    /**
     * Apply filters to query
     */
    private function applyFilters($query, $filters, $reportType)
    {
        foreach ($filters as $field => $value) {
            if (empty($value)) continue;
            
            switch ($field) {
                case 'status':
                case 'source':
                case 'method':
                    $query = $query->where($field, $value);
                    break;
                case 'team':
                    $query = $query->where('team_id', $value);
                    break;
                // Add more filter cases as needed
            }
        }
        
        return $query;
    }
    
    /**
     * Apply date range to query
     */
    private function applyDateRange($query, $dateRange, $reportType)
    {
        $field = 'created_at'; // Default date field
        
        if (isset($dateRange['from']) && isset($dateRange['to'])) {
            $query = $query->whereBetween($field, [
                Carbon::parse($dateRange['from'])->startOfDay(),
                Carbon::parse($dateRange['to'])->endOfDay()
            ]);
        }
        
        return $query;
    }
    
    /**
     * Prepare chart data from report data
     */
    private function prepareChartData($reportData, $config)
    {
        // This would prepare data for Chart.js based on the chart type
        // Implementation depends on specific requirements
        return [
            'labels' => [],
            'datasets' => []
        ];
    }
    
    /**
     * Export report to CSV
     */
    private function exportToCsv($data, $config, $filename)
    {
        $headers = array_keys($config['fields']);
        $csvData = [];
        
        // Add header row
        $csvData[] = array_values($config['fields']);
        
        // Add data rows
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($headers as $field) {
                $csvRow[] = $row->{$field} ?? '';
            }
            $csvData[] = $csvRow;
        }
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"'
        ]);
    }
    
    /**
     * Export report to Excel
     */
    private function exportToExcel($data, $config, $filename)
    {
        $fieldLabels = $this->getAvailableFields($config['report_type'], auth()->user());
        
        return Excel::download(
            new ReportExport(collect($data), $config, $fieldLabels), 
            $filename . '.xlsx'
        );
    }
    
    /**
     * Export report to PDF
     */
    private function exportToPdf($data, $config, $filename)
    {
        $fieldLabels = $this->getAvailableFields($config['report_type'], auth()->user());
        $user = auth()->user();
        
        // Prepare data for PDF template
        $reportData = [
            'report_type' => $config['report_type'],
            'title' => ucfirst($config['report_type']) . ' Report',
            'generated_by' => $user->name,
            'generated_at' => now(),
            'company' => $user->company,
            'total_records' => count($data),
            'fields' => $config['fields'],
            'field_labels' => $fieldLabels,
            'data' => collect($data)->take(500), // Limit for PDF performance
            'config' => $config
        ];
        
        // Generate PDF using existing PDF service
        try {
            $html = view('reports.pdf', $reportData)->render();
            
            $pdf = \Spatie\Browsershot\Browsershot::html($html)
                ->format('A4')
                ->landscape() // Better for reports with many columns
                ->margins(10, 10, 10, 10)
                ->showBackground()
                ->waitUntilNetworkIdle()
                ->pdf();
                
            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '.pdf"');
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }
}