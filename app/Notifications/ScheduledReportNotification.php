<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ScheduledReport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;
use Illuminate\Support\Facades\Storage;

class ScheduledReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $scheduledReport;
    protected $reportData;

    /**
     * Create a new notification instance.
     */
    public function __construct(ScheduledReport $scheduledReport, array $reportData)
    {
        $this->scheduledReport = $scheduledReport;
        $this->reportData = $reportData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $company = $this->scheduledReport->company;
        $reportType = ucwords(str_replace('_', ' ', $this->scheduledReport->report_type));
        $summary = $this->reportData['summary'] ?? [];
        
        $message = (new MailMessage)
            ->subject("Scheduled Report: {$this->scheduledReport->name}")
            ->greeting("Hello!")
            ->line("Your scheduled {$reportType} report '{$this->scheduledReport->name}' has been generated.")
            ->line("Report Summary:")
            ->line("• Total Records: " . ($summary['total_records'] ?? 0))
            ->line("• Report Type: {$reportType}")
            ->line("• Generated At: " . now()->format('M j, Y g:i A'));

        // Add type-specific summary information
        if (isset($summary['total_value'])) {
            $message->line("• Total Value: RM " . number_format($summary['total_value'], 2));
        }
        
        if (isset($summary['total_paid'])) {
            $message->line("• Total Paid: RM " . number_format($summary['total_paid'], 2));
        }
        
        if (isset($summary['total_outstanding'])) {
            $message->line("• Outstanding: RM " . number_format($summary['total_outstanding'], 2));
        }

        // Generate and attach Excel file
        try {
            $filename = $this->generateExcelAttachment();
            if ($filename && Storage::exists($filename)) {
                $message->attach(Storage::path($filename), [
                    'as' => $this->getAttachmentName(),
                    'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]);
            }
        } catch (\Exception $e) {
            $message->line("Note: Excel attachment could not be generated due to technical issues.");
        }

        $message->line("This report was generated automatically based on your schedule.")
                ->line("To modify this scheduled report, please log in to your account.")
                ->action('View Reports', url('/reports'))
                ->line("Thank you for using {$company->name} Sales System!");

        return $message;
    }

    /**
     * Generate Excel attachment and return filename
     */
    private function generateExcelAttachment()
    {
        $reportType = $this->scheduledReport->report_type;
        $data = $this->reportData['data'] ?? collect();
        
        // Create a temporary filename
        $filename = 'temp/scheduled-reports/' . uniqid() . '-' . $reportType . '.xlsx';
        
        // Ensure directory exists
        Storage::makeDirectory('temp/scheduled-reports');
        
        // Generate Excel file
        Excel::store(new ReportExport($data, $reportType), $filename);
        
        return $filename;
    }

    /**
     * Get the attachment filename for the email
     */
    private function getAttachmentName()
    {
        $reportType = ucwords(str_replace('_', ' ', $this->scheduledReport->report_type));
        $date = now()->format('Y-m-d');
        return "{$reportType}_Report_{$date}.xlsx";
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'scheduled_report_id' => $this->scheduledReport->id,
            'report_name' => $this->scheduledReport->name,
            'report_type' => $this->scheduledReport->report_type,
            'total_records' => $this->reportData['summary']['total_records'] ?? 0,
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Clean up temporary files after sending
     */
    public function __destruct()
    {
        // Clean up temporary Excel files older than 1 hour
        $tempPath = 'temp/scheduled-reports';
        if (Storage::exists($tempPath)) {
            $files = Storage::files($tempPath);
            foreach ($files as $file) {
                if (Storage::lastModified($file) < now()->subHour()->timestamp) {
                    Storage::delete($file);
                }
            }
        }
    }
}
