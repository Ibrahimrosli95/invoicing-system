<?php

namespace App\View\Components;

use App\Models\Certification;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CertificationBadges extends Component
{
    public $certifications;
    public $layout;
    public $showExpiration;
    public $showVerification;
    public $limit;
    public $category;
    public $hideExpired;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $layout = 'grid',
        bool $showExpiration = true,
        bool $showVerification = true,
        int $limit = 12,
        ?string $category = null,
        bool $hideExpired = false
    ) {
        $this->layout = $layout; // grid, row, compact
        $this->showExpiration = $showExpiration;
        $this->showVerification = $showVerification;
        $this->limit = $limit;
        $this->category = $category;
        $this->hideExpired = $hideExpired;
        
        $this->certifications = $this->getCertifications();
    }

    /**
     * Get certifications for display
     */
    private function getCertifications()
    {
        $companyId = auth()->user()?->company_id;
        
        if (!$companyId) {
            return collect();
        }

        $query = Certification::where('company_id', $companyId)
            ->where('status', 'active');

        // Filter by category if specified
        if ($this->category) {
            $query->where('category', $this->category);
        }

        // Hide expired certifications if requested
        if ($this->hideExpired) {
            $query->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            });
        }

        return $query->orderBy('is_featured', 'desc')
            ->orderBy('issue_date', 'desc')
            ->limit($this->limit)
            ->get();
    }

    /**
     * Get badge color based on certification status
     */
    public function getBadgeColor(Certification $certification): string
    {
        if (!$certification->expiry_date) {
            return 'blue'; // Never expires
        }

        $daysUntilExpiry = now()->diffInDays($certification->expiry_date, false);

        if ($daysUntilExpiry < 0) {
            return 'red'; // Expired
        } elseif ($daysUntilExpiry <= 30) {
            return 'yellow'; // Expiring soon
        } elseif ($daysUntilExpiry <= 90) {
            return 'orange'; // Needs attention
        } else {
            return 'green'; // Valid
        }
    }

    /**
     * Get verification icon based on status
     */
    public function getVerificationIcon(Certification $certification): string
    {
        switch ($certification->verification_status) {
            case 'verified':
                return 'check-circle';
            case 'pending':
                return 'clock';
            case 'failed':
                return 'x-circle';
            default:
                return 'question-mark-circle';
        }
    }

    /**
     * Get verification color class
     */
    public function getVerificationColor(Certification $certification): string
    {
        switch ($certification->verification_status) {
            case 'verified':
                return 'text-green-600';
            case 'pending':
                return 'text-yellow-600';
            case 'failed':
                return 'text-red-600';
            default:
                return 'text-gray-400';
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.certification-badges');
    }
}