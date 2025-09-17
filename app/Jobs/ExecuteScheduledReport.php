<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ScheduledReport;
use App\Http\Controllers\ReportController;
use App\Notifications\ScheduledReportNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Exception;

class ExecuteScheduledReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    protected $scheduledReport;

    /**
     * Create a new job instance.
     */
    public function __construct(ScheduledReport $scheduledReport)
    {
        $this->scheduledReport = $scheduledReport;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Executing scheduled report: {$this->scheduledReport->name}", [
                'scheduled_report_id' => $this->scheduledReport->id,
                'report_type' => $this->scheduledReport->report_type,
            ]);

            // Generate the report
            $reportData = $this->generateReport();
            
            // Send the report to recipients
            $this->sendReport($reportData);
            
            // Mark as successfully executed
            $this->scheduledReport->markAsExecuted(true, [
                'recipients_count' => count($this->scheduledReport->recipients),
                'data_rows' => count($reportData['data'] ?? []),
                'message' => 'Report executed and sent successfully',
            ]);

            Log::info("Scheduled report executed successfully: {$this->scheduledReport->name}");

        } catch (Exception $e) {
            Log::error("Failed to execute scheduled report: {$this->scheduledReport->name}", [
                'error' => $e->getMessage(),
                'scheduled_report_id' => $this->scheduledReport->id,
            ]);

            // Mark as failed
            $this->scheduledReport->markAsExecuted(false, [
                'error' => $e->getMessage(),
                'message' => 'Report execution failed',
            ]);

            throw $e;
        }
    }

    /**
     * Generate the report data
     */
    private function generateReport()
    {
        $config = $this->scheduledReport->configuration;
        $reportType = $this->scheduledReport->report_type;

        // Create a mock request with the configuration
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'type' => $reportType,
            'fields' => $config['fields'] ?? [],
            'filters' => $config['filters'] ?? [],
            'date_from' => $config['date_from'] ?? null,
            'date_to' => $config['date_to'] ?? null,
            'sort_by' => $config['sort_by'] ?? 'created_at',
            'sort_direction' => $config['sort_direction'] ?? 'desc',
            'limit' => $config['limit'] ?? 1000,
        ]);

        // Use the existing report controller logic
        $reportController = new ReportController();
        $reportData = $this->generateReportData($request, $reportType);

        return $reportData;
    }

    /**
     * Generate report data using similar logic to ReportController
     */
    private function generateReportData($request, $reportType)
    {
        $user = $this->scheduledReport->user;
        $config = $this->scheduledReport->configuration;

        // Set the authenticated user context
        auth()->setUser($user);

        switch ($reportType) {
            case 'leads':
                return $this->generateLeadsReport($config);
            case 'quotations':
                return $this->generateQuotationsReport($config);
            case 'invoices':
                return $this->generateInvoicesReport($config);
            case 'payments':
                return $this->generatePaymentsReport($config);
            default:
                throw new Exception("Unsupported report type: {$reportType}");
        }
    }

    private function generateLeadsReport($config)
    {
        $query = \App\Models\Lead::query()
            ->where('company_id', $this->scheduledReport->company_id)
            ->with(['team', 'assignedRep', 'quotations']);

        // Apply filters
        if (!empty($config['filters'])) {
            foreach ($config['filters'] as $filter) {
                if (!empty($filter['value'])) {
                    switch ($filter['field']) {
                        case 'status':
                            $query->where('status', $filter['value']);
                            break;
                        case 'source':
                            $query->where('source', $filter['value']);
                            break;
                        case 'team_id':
                            $query->where('team_id', $filter['value']);
                            break;
                        case 'assigned_to':
                            $query->where('assigned_to', $filter['value']);
                            break;
                        case 'created_at':
                            if ($filter['operator'] === 'between' && is_array($filter['value'])) {
                                $query->whereBetween('created_at', $filter['value']);
                            }
                            break;
                    }
                }
            }
        }

        // Apply date range
        if (!empty($config['date_from'])) {
            $query->where('created_at', '>=', $config['date_from']);
        }
        if (!empty($config['date_to'])) {
            $query->where('created_at', '<=', $config['date_to']);
        }

        // Apply sorting
        $query->orderBy($config['sort_by'] ?? 'created_at', $config['sort_direction'] ?? 'desc');

        // Apply limit
        $limit = $config['limit'] ?? 1000;
        $leads = $query->limit($limit)->get();

        return [
            'data' => $leads,
            'summary' => [
                'total_records' => $leads->count(),
                'report_type' => 'leads',
                'generated_at' => now()->toISOString(),
            ]
        ];
    }

    private function generateQuotationsReport($config)
    {
        $query = \App\Models\Quotation::query()
            ->where('company_id', $this->scheduledReport->company_id)
            ->with(['lead', 'createdBy']);

        // Apply similar filtering logic as leads
        if (!empty($config['filters'])) {
            foreach ($config['filters'] as $filter) {
                if (!empty($filter['value'])) {
                    switch ($filter['field']) {
                        case 'status':
                            $query->where('status', $filter['value']);
                            break;
                        case 'total':
                            if ($filter['operator'] === 'gte') {
                                $query->where('total', '>=', $filter['value']);
                            } elseif ($filter['operator'] === 'lte') {
                                $query->where('total', '<=', $filter['value']);
                            }
                            break;
                    }
                }
            }
        }

        if (!empty($config['date_from'])) {
            $query->where('created_at', '>=', $config['date_from']);
        }
        if (!empty($config['date_to'])) {
            $query->where('created_at', '<=', $config['date_to']);
        }

        $query->orderBy($config['sort_by'] ?? 'created_at', $config['sort_direction'] ?? 'desc');
        $limit = $config['limit'] ?? 1000;
        $quotations = $query->limit($limit)->get();

        return [
            'data' => $quotations,
            'summary' => [
                'total_records' => $quotations->count(),
                'total_value' => $quotations->sum('total'),
                'report_type' => 'quotations',
                'generated_at' => now()->toISOString(),
            ]
        ];
    }

    private function generateInvoicesReport($config)
    {
        $query = \App\Models\Invoice::query()
            ->where('company_id', $this->scheduledReport->company_id)
            ->with(['quotation', 'createdBy', 'paymentRecords']);

        // Apply filtering and date range similar to other reports
        if (!empty($config['filters'])) {
            foreach ($config['filters'] as $filter) {
                if (!empty($filter['value'])) {
                    switch ($filter['field']) {
                        case 'status':
                            $query->where('status', $filter['value']);
                            break;
                        case 'total':
                            if ($filter['operator'] === 'gte') {
                                $query->where('total', '>=', $filter['value']);
                            } elseif ($filter['operator'] === 'lte') {
                                $query->where('total', '<=', $filter['value']);
                            }
                            break;
                    }
                }
            }
        }

        if (!empty($config['date_from'])) {
            $query->where('created_at', '>=', $config['date_from']);
        }
        if (!empty($config['date_to'])) {
            $query->where('created_at', '<=', $config['date_to']);
        }

        $query->orderBy($config['sort_by'] ?? 'created_at', $config['sort_direction'] ?? 'desc');
        $limit = $config['limit'] ?? 1000;
        $invoices = $query->limit($limit)->get();

        return [
            'data' => $invoices,
            'summary' => [
                'total_records' => $invoices->count(),
                'total_value' => $invoices->sum('total'),
                'total_paid' => $invoices->sum('paid_amount'),
                'total_outstanding' => $invoices->sum('outstanding_amount'),
                'report_type' => 'invoices',
                'generated_at' => now()->toISOString(),
            ]
        ];
    }

    private function generatePaymentsReport($config)
    {
        $query = \App\Models\PaymentRecord::query()
            ->whereHas('invoice', function($q) {
                $q->where('company_id', $this->scheduledReport->company_id);
            })
            ->with(['invoice']);

        if (!empty($config['date_from'])) {
            $query->where('created_at', '>=', $config['date_from']);
        }
        if (!empty($config['date_to'])) {
            $query->where('created_at', '<=', $config['date_to']);
        }

        $query->orderBy($config['sort_by'] ?? 'created_at', $config['sort_direction'] ?? 'desc');
        $limit = $config['limit'] ?? 1000;
        $payments = $query->limit($limit)->get();

        return [
            'data' => $payments,
            'summary' => [
                'total_records' => $payments->count(),
                'total_amount' => $payments->sum('amount'),
                'report_type' => 'payments',
                'generated_at' => now()->toISOString(),
            ]
        ];
    }

    /**
     * Send the report to recipients
     */
    private function sendReport($reportData)
    {
        $recipients = $this->scheduledReport->recipients;
        
        foreach ($recipients as $email) {
            try {
                Notification::route('mail', $email)
                    ->notify(new ScheduledReportNotification($this->scheduledReport, $reportData));
            } catch (Exception $e) {
                Log::error("Failed to send scheduled report to {$email}", [
                    'error' => $e->getMessage(),
                    'scheduled_report_id' => $this->scheduledReport->id,
                ]);
                // Continue sending to other recipients
            }
        }
    }
}
