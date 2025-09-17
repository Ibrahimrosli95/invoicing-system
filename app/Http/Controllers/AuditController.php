<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display audit logs index with filtering and search.
     */
    public function index(Request $request): View
    {
        // Check authorization
        if (!auth()->user()->can('view audit logs')) {
            abort(403, 'Unauthorized to view audit logs');
        }

        $query = AuditLog::query()
            ->forCompany()
            ->with(['user', 'auditable'])
            ->latest();

        // Apply filters
        $this->applyFilters($query, $request);

        // Get filtered results with pagination
        $auditLogs = $query->paginate(25)->withQueryString();

        // Get filter statistics
        $stats = $this->getAuditStats($request);

        // Get filter options
        $filterOptions = $this->getFilterOptions();

        return view('audit.index', compact(
            'auditLogs',
            'stats',
            'filterOptions'
        ));
    }

    /**
     * Show detailed view of a specific audit log.
     */
    public function show(AuditLog $auditLog): View
    {
        // Check authorization
        if (!auth()->user()->can('view audit logs')) {
            abort(403, 'Unauthorized to view audit logs');
        }

        // Ensure user can only view logs from their company
        if ($auditLog->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized to view this audit log');
        }

        $auditLog->load(['user', 'auditable']);

        // Get related audit logs (same model and batch)
        $relatedLogs = AuditLog::query()
            ->forCompany()
            ->where(function ($query) use ($auditLog) {
                $query->where(function ($q) use ($auditLog) {
                    $q->where('auditable_type', $auditLog->auditable_type)
                      ->where('auditable_id', $auditLog->auditable_id);
                });

                if ($auditLog->batch_id) {
                    $query->orWhere('batch_id', $auditLog->batch_id);
                }
            })
            ->where('id', '!=', $auditLog->id)
            ->with(['user'])
            ->latest()
            ->limit(10)
            ->get();

        return view('audit.show', compact('auditLog', 'relatedLogs'));
    }

    /**
     * Get audit logs for a specific model via AJAX.
     */
    public function model(Request $request): JsonResponse
    {
        // Check authorization
        if (!auth()->user()->can('view audit logs')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        $auditLogs = AuditLog::query()
            ->forCompany()
            ->forModel($request->model_type, $request->model_id)
            ->with(['user'])
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'audit_logs' => $auditLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'event' => $log->getEventDisplayName(),
                    'action' => $log->getActionDisplayName(),
                    'user_name' => $log->getUserDisplayName(),
                    'changes_summary' => $log->getChangesSummary(),
                    'created_at' => $log->created_at->format('M j, Y g:i A'),
                    'created_at_human' => $log->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    /**
     * Get audit statistics dashboard.
     */
    public function dashboard(Request $request): View
    {
        // Check authorization
        if (!auth()->user()->can('view audit dashboard')) {
            abort(403, 'Unauthorized to view audit dashboard');
        }

        $days = $request->input('days', 30);
        $companyId = auth()->user()->company_id;

        // Get comprehensive audit statistics
        $stats = AuditLog::getActivityStats($companyId, $days);

        // Get top users by activity
        $topUsers = AuditLog::query()
            ->forCompany($companyId)
            ->where('created_at', '>=', now()->subDays($days))
            ->select('user_id', 'user_name')
            ->selectRaw('count(*) as activity_count')
            ->groupBy('user_id', 'user_name')
            ->orderByDesc('activity_count')
            ->limit(10)
            ->get();

        // Get most modified models
        $topModels = AuditLog::query()
            ->forCompany($companyId)
            ->where('created_at', '>=', now()->subDays($days))
            ->select('auditable_type')
            ->selectRaw('count(*) as modification_count')
            ->groupBy('auditable_type')
            ->orderByDesc('modification_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'model' => class_basename($item->auditable_type),
                    'count' => $item->modification_count,
                ];
            });

        // Get recent critical activities (deletions, failed logins, etc.)
        $criticalActivities = AuditLog::query()
            ->forCompany($companyId)
            ->whereIn('event', [
                AuditLog::EVENT_DELETED,
                AuditLog::EVENT_FAILED_LOGIN,
                AuditLog::EVENT_PERMISSION_DENIED
            ])
            ->with(['user'])
            ->latest()
            ->limit(20)
            ->get();

        return view('audit.dashboard', compact(
            'stats',
            'topUsers',
            'topModels',
            'criticalActivities',
            'days'
        ));
    }

    /**
     * Export audit logs to CSV.
     */
    public function export(Request $request): Response
    {
        // Check authorization
        if (!auth()->user()->can('export audit logs')) {
            abort(403, 'Unauthorized to export audit logs');
        }

        $query = AuditLog::query()
            ->forCompany()
            ->with(['user']);

        // Apply same filters as index
        $this->applyFilters($query, $request);

        $auditLogs = $query->get();

        $filename = 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($auditLogs) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID',
                'Date/Time',
                'User',
                'Event',
                'Action',
                'Model Type',
                'Model ID',
                'Changes Summary',
                'IP Address',
                'User Agent',
            ]);

            // CSV data
            foreach ($auditLogs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->getUserDisplayName(),
                    $log->getEventDisplayName(),
                    $log->getActionDisplayName(),
                    class_basename($log->auditable_type),
                    $log->auditable_id,
                    $log->getChangesSummary(),
                    $log->ip_address,
                    $log->user_agent,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get audit comparison between two log entries.
     */
    public function compare(Request $request): JsonResponse
    {
        // Check authorization
        if (!auth()->user()->can('view audit logs')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'log1_id' => 'required|integer|exists:audit_logs,id',
            'log2_id' => 'required|integer|exists:audit_logs,id',
        ]);

        $log1 = AuditLog::forCompany()->findOrFail($request->log1_id);
        $log2 = AuditLog::forCompany()->findOrFail($request->log2_id);

        // Ensure both logs are for the same model
        if ($log1->auditable_type !== $log2->auditable_type ||
            $log1->auditable_id !== $log2->auditable_id) {
            return response()->json([
                'error' => 'Cannot compare logs for different models'
            ], 400);
        }

        $comparison = [
            'model' => class_basename($log1->auditable_type),
            'model_id' => $log1->auditable_id,
            'log1' => [
                'id' => $log1->id,
                'event' => $log1->getEventDisplayName(),
                'user' => $log1->getUserDisplayName(),
                'date' => $log1->created_at->format('M j, Y g:i A'),
                'values' => $log1->new_values ?? $log1->old_values ?? [],
            ],
            'log2' => [
                'id' => $log2->id,
                'event' => $log2->getEventDisplayName(),
                'user' => $log2->getUserDisplayName(),
                'date' => $log2->created_at->format('M j, Y g:i A'),
                'values' => $log2->new_values ?? $log2->old_values ?? [],
            ],
            'differences' => $this->calculateDifferences(
                $log1->new_values ?? $log1->old_values ?? [],
                $log2->new_values ?? $log2->old_values ?? []
            ),
        ];

        return response()->json($comparison);
    }

    /**
     * Cleanup old audit logs.
     */
    public function cleanup(Request $request): JsonResponse
    {
        // Check authorization
        if (!auth()->user()->can('manage audit logs')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'retention_days' => 'required|integer|min:30|max:3650',
        ]);

        $deletedCount = AuditLog::cleanupOldLogs($request->retention_days);

        return response()->json([
            'message' => "Successfully deleted {$deletedCount} old audit log entries",
            'deleted_count' => $deletedCount,
        ]);
    }

    /**
     * Apply filters to audit log query.
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Event filter
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Model type filter
        if ($request->filled('model_type')) {
            $query->where('auditable_type', $request->model_type);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'LIKE', "%{$search}%")
                  ->orWhere('auditable_type', 'LIKE', "%{$search}%")
                  ->orWhere('event', 'LIKE', "%{$search}%")
                  ->orWhere('action', 'LIKE', "%{$search}%")
                  ->orWhereJsonContains('metadata', $search);
            });
        }

        // IP address filter
        if ($request->filled('ip_address')) {
            $query->where('ip_address', $request->ip_address);
        }
    }

    /**
     * Get audit statistics for the current filters.
     */
    private function getAuditStats(Request $request): array
    {
        $baseQuery = AuditLog::query()->forCompany();
        $this->applyFilters($baseQuery, $request);

        return [
            'total_logs' => (clone $baseQuery)->count(),
            'total_users' => (clone $baseQuery)->distinct('user_id')->count('user_id'),
            'total_models' => (clone $baseQuery)->distinct('auditable_type')->count('auditable_type'),
            'date_range' => [
                'from' => (clone $baseQuery)->min('created_at'),
                'to' => (clone $baseQuery)->max('created_at'),
            ],
        ];
    }

    /**
     * Get filter options for the form.
     */
    private function getFilterOptions(): array
    {
        $companyId = auth()->user()->company_id;

        return [
            'users' => User::where('company_id', $companyId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
            'events' => [
                AuditLog::EVENT_CREATED => 'Created',
                AuditLog::EVENT_UPDATED => 'Updated',
                AuditLog::EVENT_DELETED => 'Deleted',
                AuditLog::EVENT_RESTORED => 'Restored',
                AuditLog::EVENT_LOGIN => 'Login',
                AuditLog::EVENT_LOGOUT => 'Logout',
                AuditLog::EVENT_FAILED_LOGIN => 'Failed Login',
                AuditLog::EVENT_PERMISSION_DENIED => 'Permission Denied',
            ],
            'actions' => [
                AuditLog::ACTION_QUOTATION_SENT => 'Quotation Sent',
                AuditLog::ACTION_QUOTATION_ACCEPTED => 'Quotation Accepted',
                AuditLog::ACTION_QUOTATION_REJECTED => 'Quotation Rejected',
                AuditLog::ACTION_INVOICE_SENT => 'Invoice Sent',
                AuditLog::ACTION_INVOICE_PAID => 'Invoice Paid',
                AuditLog::ACTION_PAYMENT_RECORDED => 'Payment Recorded',
                AuditLog::ACTION_LEAD_ASSIGNED => 'Lead Assigned',
                AuditLog::ACTION_LEAD_STATUS_CHANGED => 'Lead Status Changed',
                AuditLog::ACTION_ASSESSMENT_COMPLETED => 'Assessment Completed',
                AuditLog::ACTION_ASSESSMENT_REPORTED => 'Assessment Reported',
            ],
            'model_types' => AuditLog::forCompany()
                ->distinct('auditable_type')
                ->orderBy('auditable_type')
                ->pluck('auditable_type')
                ->map(function ($type) {
                    return [
                        'value' => $type,
                        'label' => class_basename($type),
                    ];
                })
                ->values()
                ->toArray(),
        ];
    }

    /**
     * Calculate differences between two value arrays.
     */
    private function calculateDifferences(array $values1, array $values2): array
    {
        $differences = [];
        $allKeys = array_unique(array_merge(array_keys($values1), array_keys($values2)));

        foreach ($allKeys as $key) {
            $value1 = $values1[$key] ?? null;
            $value2 = $values2[$key] ?? null;

            if ($value1 !== $value2) {
                $differences[$key] = [
                    'old' => $value1,
                    'new' => $value2,
                    'changed' => true,
                ];
            } else {
                $differences[$key] = [
                    'value' => $value1,
                    'changed' => false,
                ];
            }
        }

        return $differences;
    }
}