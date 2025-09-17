<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\PaymentRecord;
use App\Models\ExportHistory;
use App\Jobs\ProcessBulkExport;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReportApiController extends Controller
{
    /**
     * Get available report types for the authenticated user
     */
    public function reportTypes(): JsonResponse
    {
        $user = auth()->user();
        
        $reportTypes = [
            'leads' => [
                'name' => 'Leads Report',
                'description' => 'Lead management and conversion tracking',
                'available_fields' => ['id', 'name', 'email', 'phone', 'company_name', 'status', 'source', 'team', 'assigned_to', 'created_at'],
                'filters' => ['status', 'source', 'team_id', 'assigned_to', 'date_range'],
            ],
            'quotations' => [
                'name' => 'Quotations Report',
                'description' => 'Quotation analysis and performance metrics',
                'available_fields' => ['number', 'customer_name', 'customer_email', 'status', 'total', 'created_by', 'created_at'],
                'filters' => ['status', 'amount_range', 'date_range', 'team_id', 'user_id'],
            ],
            'invoices' => [
                'name' => 'Invoices Report',
                'description' => 'Invoice tracking and payment analysis',
                'available_fields' => ['number', 'customer_name', 'status', 'total', 'paid_amount', 'outstanding_amount', 'due_date', 'created_at'],
                'filters' => ['status', 'amount_range', 'date_range', 'overdue'],
            ],
            'payments' => [
                'name' => 'Payments Report',
                'description' => 'Payment records and collection tracking',
                'available_fields' => ['invoice_number', 'amount', 'payment_method', 'reference_number', 'status', 'created_at'],
                'filters' => ['payment_method', 'amount_range', 'date_range'],
            ],
        ];

        // Filter based on user permissions
        $availableTypes = [];
        foreach ($reportTypes as $type => $config) {
            if ($this->canAccessReportType($user, $type)) {
                $availableTypes[$type] = $config;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $availableTypes,
        ]);
    }

    /**
     * Generate a report and return data
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:leads,quotations,invoices,payments',
            'fields' => 'array',
            'filters' => 'array',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'limit' => 'nullable|integer|min:1|max:10000',
            'sort_by' => 'nullable|string',
            'sort_direction' => 'nullable|string|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();
        $reportType = $request->input('type');

        if (!$this->canAccessReportType($user, $reportType)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this report type',
            ], 403);
        }

        try {
            $data = $this->generateReportData($request, $reportType);
            
            return response()->json([
                'success' => true,
                'data' => $data['data'],
                'summary' => $data['summary'],
                'meta' => [
                    'total_records' => $data['summary']['total_records'],
                    'generated_at' => now()->toISOString(),
                    'report_type' => $reportType,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Queue a bulk export
     */
    public function export(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:leads,quotations,invoices,payments',
            'format' => 'required|string|in:csv,xlsx,pdf',
            'fields' => 'array',
            'filters' => 'array',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'filename' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();
        $reportType = $request->input('type');

        if (!$this->canAccessReportType($user, $reportType)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this report type',
            ], 403);
        }

        try {
            // Create export history record
            $filename = $request->input('filename') ?: $this->generateFilename($reportType, $request->input('format'));
            
            $exportHistory = ExportHistory::create([
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'export_type' => 'api',
                'data_type' => $reportType,
                'format' => $request->input('format'),
                'configuration' => [
                    'fields' => $request->input('fields', []),
                    'filters' => $request->input('filters', []),
                    'date_from' => $request->input('date_from'),
                    'date_to' => $request->input('date_to'),
                    'sort_by' => $request->input('sort_by', 'created_at'),
                    'sort_direction' => $request->input('sort_direction', 'desc'),
                ],
                'filename' => $filename,
                'status' => ExportHistory::STATUS_PENDING,
            ]);

            // Queue the export job
            ProcessBulkExport::dispatch($exportHistory);

            return response()->json([
                'success' => true,
                'message' => 'Export queued successfully',
                'data' => [
                    'export_id' => $exportHistory->id,
                    'status' => $exportHistory->status,
                    'filename' => $exportHistory->filename,
                    'estimated_completion' => 'Within 5-10 minutes for large datasets',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to queue export',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get export status
     */
    public function exportStatus($exportId): JsonResponse
    {
        $user = auth()->user();
        
        $exportHistory = ExportHistory::where('id', $exportId)
            ->where('company_id', $user->company_id)
            ->first();

        if (!$exportHistory) {
            return response()->json([
                'success' => false,
                'message' => 'Export not found',
            ], 404);
        }

        $data = [
            'export_id' => $exportHistory->id,
            'status' => $exportHistory->status,
            'progress_percentage' => $exportHistory->progress_percentage,
            'total_records' => $exportHistory->total_records,
            'processed_records' => $exportHistory->processed_records,
            'filename' => $exportHistory->filename,
            'file_size' => $exportHistory->formatted_file_size,
            'processing_time' => $exportHistory->formatted_processing_time,
            'can_download' => $exportHistory->can_download,
            'expires_at' => $exportHistory->expires_at?->toISOString(),
            'created_at' => $exportHistory->created_at->toISOString(),
        ];

        if ($exportHistory->status === ExportHistory::STATUS_COMPLETED && $exportHistory->can_download) {
            $data['download_url'] = route('api.reports.download', $exportHistory->id);
        }

        if ($exportHistory->status === ExportHistory::STATUS_FAILED) {
            $data['error_message'] = $exportHistory->error_message;
            $data['can_retry'] = $exportHistory->can_retry;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Download export file
     */
    public function download($exportId)
    {
        $user = auth()->user();
        
        $exportHistory = ExportHistory::where('id', $exportId)
            ->where('company_id', $user->company_id)
            ->first();

        if (!$exportHistory) {
            return response()->json([
                'success' => false,
                'message' => 'Export not found',
            ], 404);
        }

        if (!$exportHistory->can_download) {
            return response()->json([
                'success' => false,
                'message' => 'File is not available for download',
                'details' => [
                    'status' => $exportHistory->status,
                    'is_expired' => $exportHistory->is_expired,
                    'file_exists' => $exportHistory->file_path && \Storage::exists($exportHistory->file_path),
                ],
            ], 400);
        }

        // Mark as downloaded
        $exportHistory->markAsDownloaded();

        return \Storage::download($exportHistory->file_path, $exportHistory->filename);
    }

    /**
     * Get export history for the user
     */
    public function exportHistory(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = ExportHistory::where('company_id', $user->company_id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('data_type')) {
            $query->where('data_type', $request->input('data_type'));
        }

        if ($request->has('format')) {
            $query->where('format', $request->input('format'));
        }

        $perPage = $request->input('per_page', 20);
        $exports = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $exports->items(),
            'pagination' => [
                'current_page' => $exports->currentPage(),
                'per_page' => $exports->perPage(),
                'total' => $exports->total(),
                'last_page' => $exports->lastPage(),
                'has_more' => $exports->hasMorePages(),
            ],
        ]);
    }

    /**
     * Get dashboard statistics via API
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        
        $dateFrom = $request->input('date_from', now()->subDays(30)->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $stats = [
            'leads' => [
                'total' => Lead::where('company_id', $companyId)->count(),
                'recent' => Lead::where('company_id', $companyId)
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'by_status' => Lead::where('company_id', $companyId)
                    ->selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
            ],
            'quotations' => [
                'total' => Quotation::where('company_id', $companyId)->count(),
                'recent' => Quotation::where('company_id', $companyId)
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'total_value' => Quotation::where('company_id', $companyId)->sum('total'),
                'by_status' => Quotation::where('company_id', $companyId)
                    ->selectRaw('status, count(*) as count, sum(total) as total_value')
                    ->groupBy('status')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [$item->status => [
                            'count' => $item->count,
                            'total_value' => $item->total_value,
                        ]];
                    }),
            ],
            'invoices' => [
                'total' => Invoice::where('company_id', $companyId)->count(),
                'recent' => Invoice::where('company_id', $companyId)
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'total_value' => Invoice::where('company_id', $companyId)->sum('total'),
                'total_paid' => Invoice::where('company_id', $companyId)->sum('paid_amount'),
                'total_outstanding' => Invoice::where('company_id', $companyId)->sum('outstanding_amount'),
                'overdue_count' => Invoice::where('company_id', $companyId)
                    ->where('status', 'OVERDUE')
                    ->count(),
            ],
            'payments' => [
                'total' => PaymentRecord::whereHas('invoice', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->count(),
                'recent' => PaymentRecord::whereHas('invoice', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_amount' => PaymentRecord::whereHas('invoice', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->sum('amount'),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'meta' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'generated_at' => now()->toISOString(),
            ],
        ]);
    }

    // Helper methods
    private function canAccessReportType($user, $reportType)
    {
        // Use existing permission logic from ReportController
        $permissions = [
            'leads' => $user->can('viewAny', Lead::class),
            'quotations' => $user->can('viewAny', Quotation::class),
            'invoices' => $user->can('viewAny', Invoice::class),
            'payments' => $user->hasAnyRole(['finance_manager', 'company_manager', 'superadmin']),
        ];

        return $permissions[$reportType] ?? false;
    }

    private function generateReportData($request, $reportType)
    {
        $config = [
            'fields' => $request->input('fields', []),
            'filters' => $request->input('filters', []),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_direction' => $request->input('sort_direction', 'desc'),
            'limit' => $request->input('limit', 100),
        ];

        // Use similar logic to existing report generation
        switch ($reportType) {
            case 'leads':
                return $this->generateLeadsData($config);
            case 'quotations':
                return $this->generateQuotationsData($config);
            case 'invoices':
                return $this->generateInvoicesData($config);
            case 'payments':
                return $this->generatePaymentsData($config);
            default:
                throw new \Exception("Unsupported report type: {$reportType}");
        }
    }

    private function generateLeadsData($config)
    {
        $query = Lead::where('company_id', auth()->user()->company_id)
            ->with(['team', 'assignedRep']);

        $this->applyCommonFilters($query, $config);
        $this->applySorting($query, $config);

        $leads = $query->limit($config['limit'])->get();

        return [
            'data' => $leads,
            'summary' => [
                'total_records' => $leads->count(),
                'report_type' => 'leads',
            ],
        ];
    }

    private function generateQuotationsData($config)
    {
        $query = Quotation::where('company_id', auth()->user()->company_id)
            ->with(['lead', 'createdBy']);

        $this->applyCommonFilters($query, $config);
        $this->applySorting($query, $config);

        $quotations = $query->limit($config['limit'])->get();

        return [
            'data' => $quotations,
            'summary' => [
                'total_records' => $quotations->count(),
                'total_value' => $quotations->sum('total'),
                'report_type' => 'quotations',
            ],
        ];
    }

    private function generateInvoicesData($config)
    {
        $query = Invoice::where('company_id', auth()->user()->company_id)
            ->with(['quotation', 'createdBy']);

        $this->applyCommonFilters($query, $config);
        $this->applySorting($query, $config);

        $invoices = $query->limit($config['limit'])->get();

        return [
            'data' => $invoices,
            'summary' => [
                'total_records' => $invoices->count(),
                'total_value' => $invoices->sum('total'),
                'total_paid' => $invoices->sum('paid_amount'),
                'total_outstanding' => $invoices->sum('outstanding_amount'),
                'report_type' => 'invoices',
            ],
        ];
    }

    private function generatePaymentsData($config)
    {
        $query = PaymentRecord::whereHas('invoice', function($q) {
            $q->where('company_id', auth()->user()->company_id);
        })->with(['invoice']);

        $this->applyCommonFilters($query, $config);
        $this->applySorting($query, $config);

        $payments = $query->limit($config['limit'])->get();

        return [
            'data' => $payments,
            'summary' => [
                'total_records' => $payments->count(),
                'total_amount' => $payments->sum('amount'),
                'report_type' => 'payments',
            ],
        ];
    }

    private function applyCommonFilters($query, $config)
    {
        if (!empty($config['date_from'])) {
            $query->where('created_at', '>=', $config['date_from']);
        }
        if (!empty($config['date_to'])) {
            $query->where('created_at', '<=', $config['date_to']);
        }

        $filters = $config['filters'] ?? [];
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                $query->where($field, $value);
            }
        }
    }

    private function applySorting($query, $config)
    {
        $sortBy = $config['sort_by'] ?? 'created_at';
        $sortDirection = $config['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);
    }

    private function generateFilename($reportType, $format)
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $dataTypeFormatted = ucwords(str_replace('_', ' ', $reportType));
        return "{$dataTypeFormatted}_Export_{$timestamp}.{$format}";
    }
}