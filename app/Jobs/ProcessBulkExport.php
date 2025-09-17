<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ExportHistory;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessBulkExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800; // 30 minutes for large exports
    public $tries = 3;

    protected $exportHistory;

    /**
     * Create a new job instance.
     */
    public function __construct(ExportHistory $exportHistory)
    {
        $this->exportHistory = $exportHistory;
        $this->onQueue('exports'); // Use dedicated queue for exports
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Starting bulk export processing", [
                'export_id' => $this->exportHistory->id,
                'data_type' => $this->exportHistory->data_type,
                'format' => $this->exportHistory->format,
            ]);

            // Mark as started
            $this->exportHistory->markAsStarted();

            // Generate the export file
            $filePath = $this->generateExportFile();

            // Mark as completed
            $this->exportHistory->markAsCompleted($filePath);

            Log::info("Bulk export completed successfully", [
                'export_id' => $this->exportHistory->id,
                'file_path' => $filePath,
                'processing_time' => $this->exportHistory->processing_time,
            ]);

        } catch (Exception $e) {
            Log::error("Bulk export failed", [
                'export_id' => $this->exportHistory->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->exportHistory->markAsFailed($e->getMessage(), [
                'error_class' => get_class($e),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    /**
     * Generate the export file based on configuration
     */
    private function generateExportFile()
    {
        $config = $this->exportHistory->configuration;
        $dataType = $this->exportHistory->data_type;
        $format = $this->exportHistory->format;

        // Set user context for data access
        auth()->setUser($this->exportHistory->user);

        // Get the data
        $data = $this->fetchData($dataType, $config);

        // Update total records
        $this->exportHistory->update(['total_records' => $data->count()]);

        // Generate filename
        $filename = $this->generateFilename($dataType, $format);
        $filePath = "exports/{$this->exportHistory->company_id}/{$filename}";

        // Ensure directory exists
        Storage::makeDirectory("exports/{$this->exportHistory->company_id}");

        // Process based on format
        switch ($format) {
            case 'xlsx':
                $this->generateExcelFile($data, $filePath, $dataType);
                break;
            case 'csv':
                $this->generateCsvFile($data, $filePath, $dataType);
                break;
            case 'pdf':
                $this->generatePdfFile($data, $filePath, $dataType, $config);
                break;
            default:
                throw new Exception("Unsupported export format: {$format}");
        }

        return $filePath;
    }

    /**
     * Fetch data based on type and configuration
     */
    private function fetchData($dataType, $config)
    {
        $chunkSize = 1000; // Process in chunks for memory efficiency

        switch ($dataType) {
            case 'leads':
                return $this->fetchLeadsData($config, $chunkSize);
            case 'quotations':
                return $this->fetchQuotationsData($config, $chunkSize);
            case 'invoices':
                return $this->fetchInvoicesData($config, $chunkSize);
            case 'payments':
                return $this->fetchPaymentsData($config, $chunkSize);
            default:
                throw new Exception("Unsupported data type: {$dataType}");
        }
    }

    private function fetchLeadsData($config, $chunkSize)
    {
        $query = \App\Models\Lead::query()
            ->where('company_id', $this->exportHistory->company_id)
            ->with(['team', 'assignedRep', 'quotations']);

        $this->applyFilters($query, $config);
        return $this->processInChunks($query, $chunkSize);
    }

    private function fetchQuotationsData($config, $chunkSize)
    {
        $query = \App\Models\Quotation::query()
            ->where('company_id', $this->exportHistory->company_id)
            ->with(['lead', 'createdBy', 'items']);

        $this->applyFilters($query, $config);
        return $this->processInChunks($query, $chunkSize);
    }

    private function fetchInvoicesData($config, $chunkSize)
    {
        $query = \App\Models\Invoice::query()
            ->where('company_id', $this->exportHistory->company_id)
            ->with(['quotation', 'createdBy', 'items', 'paymentRecords']);

        $this->applyFilters($query, $config);
        return $this->processInChunks($query, $chunkSize);
    }

    private function fetchPaymentsData($config, $chunkSize)
    {
        $query = \App\Models\PaymentRecord::query()
            ->whereHas('invoice', function($q) {
                $q->where('company_id', $this->exportHistory->company_id);
            })
            ->with(['invoice']);

        $this->applyFilters($query, $config);
        return $this->processInChunks($query, $chunkSize);
    }

    /**
     * Apply filters to query based on configuration
     */
    private function applyFilters($query, $config)
    {
        // Date filters
        if (!empty($config['date_from'])) {
            $query->where('created_at', '>=', $config['date_from']);
        }
        if (!empty($config['date_to'])) {
            $query->where('created_at', '<=', $config['date_to']);
        }

        // Status filters
        if (!empty($config['status'])) {
            $query->where('status', $config['status']);
        }

        // Team filters
        if (!empty($config['team_id'])) {
            $query->where('team_id', $config['team_id']);
        }

        // User filters
        if (!empty($config['user_id'])) {
            $query->where('created_by', $config['user_id']);
        }

        // Amount filters
        if (!empty($config['amount_min'])) {
            $query->where('total', '>=', $config['amount_min']);
        }
        if (!empty($config['amount_max'])) {
            $query->where('total', '<=', $config['amount_max']);
        }

        // Apply sorting
        $sortBy = $config['sort_by'] ?? 'created_at';
        $sortDirection = $config['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);
    }

    /**
     * Process query in chunks and update progress
     */
    private function processInChunks($query, $chunkSize)
    {
        $allData = collect();
        $processedCount = 0;
        $totalEstimate = $query->count();

        $query->chunk($chunkSize, function ($chunk) use (&$allData, &$processedCount, $totalEstimate) {
            $allData = $allData->concat($chunk);
            $processedCount += $chunk->count();
            
            // Update progress
            $this->exportHistory->updateProgress($processedCount, $totalEstimate);
        });

        return $allData;
    }

    /**
     * Generate Excel file
     */
    private function generateExcelFile($data, $filePath, $dataType)
    {
        Excel::store(new ReportExport($data, $dataType), $filePath);
    }

    /**
     * Generate CSV file
     */
    private function generateCsvFile($data, $filePath, $dataType)
    {
        $file = fopen(Storage::path($filePath), 'w');
        
        // Write headers
        $headers = $this->getHeaders($dataType);
        fputcsv($file, $headers);

        // Write data rows
        foreach ($data as $item) {
            $row = $this->formatDataRow($item, $dataType);
            fputcsv($file, $row);
        }

        fclose($file);
    }

    /**
     * Generate PDF file (for summary reports)
     */
    private function generatePdfFile($data, $filePath, $dataType, $config)
    {
        // For now, use existing PDF service
        // This could be expanded for more complex PDF layouts
        $service = app(\App\Services\PDFService::class);
        $html = view('reports.pdf.bulk-export', [
            'data' => $data,
            'dataType' => $dataType,
            'config' => $config,
            'exportHistory' => $this->exportHistory,
        ])->render();

        $pdf = $service->generateFromHtml($html);
        Storage::put($filePath, $pdf);
    }

    /**
     * Get headers for CSV export
     */
    private function getHeaders($dataType)
    {
        return match($dataType) {
            'leads' => ['ID', 'Name', 'Email', 'Phone', 'Company', 'Status', 'Source', 'Team', 'Assigned To', 'Created At'],
            'quotations' => ['Number', 'Customer', 'Email', 'Phone', 'Status', 'Total', 'Created By', 'Created At'],
            'invoices' => ['Number', 'Customer', 'Status', 'Total', 'Paid Amount', 'Outstanding', 'Due Date', 'Created At'],
            'payments' => ['Invoice Number', 'Amount', 'Method', 'Reference', 'Status', 'Paid At'],
            default => ['ID', 'Data', 'Created At'],
        };
    }

    /**
     * Format data row for CSV export
     */
    private function formatDataRow($item, $dataType)
    {
        return match($dataType) {
            'leads' => [
                $item->id,
                $item->name,
                $item->email,
                $item->phone,
                $item->company_name,
                $item->status,
                $item->source,
                $item->team->name ?? '',
                $item->assignedRep->name ?? '',
                $item->created_at->format('Y-m-d H:i:s'),
            ],
            'quotations' => [
                $item->number,
                $item->customer_name,
                $item->customer_email,
                $item->customer_phone,
                $item->status,
                $item->total,
                $item->createdBy->name ?? '',
                $item->created_at->format('Y-m-d H:i:s'),
            ],
            'invoices' => [
                $item->number,
                $item->customer_name,
                $item->status,
                $item->total,
                $item->paid_amount,
                $item->outstanding_amount,
                $item->due_date ? $item->due_date->format('Y-m-d') : '',
                $item->created_at->format('Y-m-d H:i:s'),
            ],
            'payments' => [
                $item->invoice->number ?? '',
                $item->amount,
                $item->payment_method,
                $item->reference_number,
                $item->status,
                $item->created_at->format('Y-m-d H:i:s'),
            ],
            default => [
                $item->id ?? '',
                json_encode($item->toArray()),
                $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '',
            ],
        };
    }

    /**
     * Generate filename for export
     */
    private function generateFilename($dataType, $format)
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $dataTypeFormatted = ucwords(str_replace('_', ' ', $dataType));
        return "{$dataTypeFormatted}_Export_{$timestamp}.{$format}";
    }

    /**
     * Failed job handling
     */
    public function failed(Exception $exception)
    {
        Log::error("Bulk export job failed permanently", [
            'export_id' => $this->exportHistory->id,
            'error' => $exception->getMessage(),
        ]);

        $this->exportHistory->markAsFailed(
            "Export failed after {$this->tries} attempts: " . $exception->getMessage(),
            [
                'final_attempt' => true,
                'error_class' => get_class($exception),
            ]
        );
    }
}
