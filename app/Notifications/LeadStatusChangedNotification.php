<?php

namespace App\Notifications;

use App\Models\Lead;

class LeadStatusChangedNotification extends BaseNotification
{
    protected $notificationType = 'lead_status_changed';
    protected Lead $lead;
    protected $oldStatus;
    protected $newStatus;
    protected $changedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Lead $lead, string $oldStatus, string $newStatus, $changedBy = null)
    {
        parent::__construct($lead);
        $this->lead = $lead;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy ?? auth()->user();
    }

    protected function getEmailSubject(object $notifiable): string
    {
        return "Lead Status Updated: {$this->lead->company_name}";
    }

    protected function getEmailContent(object $notifiable): array
    {
        $statusColors = [
            'new' => 'info',
            'contacted' => 'warning',
            'quoted' => 'info',
            'won' => 'success',
            'lost' => 'error',
        ];

        return [
            'type' => $statusColors[$this->newStatus] ?? 'info',
            'title' => 'Lead Status Updated',
            'message' => "The status of lead {$this->lead->company_name} has been updated from " . ucfirst($this->oldStatus) . " to " . ucfirst($this->newStatus) . ".",
            'details' => [
                'Company' => $this->lead->company_name,
                'Contact Person' => $this->lead->contact_name,
                'Phone' => $this->lead->phone,
                'Previous Status' => ucfirst($this->oldStatus),
                'New Status' => ucfirst($this->newStatus),
                'Updated By' => $this->changedBy->name,
                'Updated At' => now()->format('M d, Y \a\t h:i A'),
            ],
            'action_url' => route('leads.show', $this->lead),
            'action_text' => 'View Lead Details',
            'secondary_actions' => [
                [
                    'url' => route('leads.kanban'),
                    'text' => 'View Kanban Board',
                ],
            ],
        ];
    }

    protected function getDatabaseTitle(object $notifiable): string
    {
        return 'Lead Status Updated';
    }

    protected function getDatabaseMessage(object $notifiable): string
    {
        return "Lead {$this->lead->company_name} status changed from {$this->oldStatus} to {$this->newStatus}";
    }

    protected function getDatabaseData(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'lead_company' => $this->lead->company_name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'changed_by' => $this->changedBy->name,
        ];
    }
}