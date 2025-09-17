<?php

namespace App\Notifications;

use App\Models\Lead;

class LeadAssignedNotification extends BaseNotification
{
    protected $notificationType = 'lead_assigned';
    protected Lead $lead;
    protected $assignedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(Lead $lead, $assignedBy = null)
    {
        parent::__construct($lead);
        $this->lead = $lead;
        $this->assignedBy = $assignedBy ?? auth()->user();
    }

    protected function getEmailSubject(object $notifiable): string
    {
        return "New Lead Assigned: {$this->lead->company_name}";
    }

    protected function getEmailContent(object $notifiable): array
    {
        return [
            'type' => 'info',
            'title' => 'New Lead Assigned to You',
            'message' => "You have been assigned a new lead: {$this->lead->company_name}. Please review the details and take appropriate action.",
            'details' => [
                'Company' => $this->lead->company_name,
                'Contact Person' => $this->lead->contact_name,
                'Phone' => $this->lead->phone,
                'Email' => $this->lead->email ?: 'Not provided',
                'Status' => ucfirst($this->lead->status),
                'Estimated Value' => $this->lead->estimated_value ? 'RM ' . number_format($this->lead->estimated_value, 2) : 'Not specified',
                'Assigned By' => $this->assignedBy->name,
                'Date Assigned' => now()->format('M d, Y \a\t h:i A'),
            ],
            'action_url' => route('leads.show', $this->lead),
            'action_text' => 'View Lead Details',
            'secondary_actions' => [
                [
                    'url' => route('leads.index'),
                    'text' => 'View All Leads',
                ],
            ],
        ];
    }

    protected function getDatabaseTitle(object $notifiable): string
    {
        return 'New Lead Assignment';
    }

    protected function getDatabaseMessage(object $notifiable): string
    {
        return "You have been assigned lead: {$this->lead->company_name}";
    }

    protected function getDatabaseData(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'lead_company' => $this->lead->company_name,
            'lead_status' => $this->lead->status,
            'assigned_by' => $this->assignedBy->name,
        ];
    }
}