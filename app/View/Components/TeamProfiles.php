<?php

namespace App\View\Components;

use App\Models\User;
use App\Models\Team;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TeamProfiles extends Component
{
    public $team;
    public $users;
    public $layout;
    public $showStats;
    public $showContact;
    public $limit;
    public $featuredOnly;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?Team $team = null,
        string $layout = 'grid',
        bool $showStats = true,
        bool $showContact = false,
        int $limit = 12,
        bool $featuredOnly = false
    ) {
        $this->team = $team;
        $this->layout = $layout; // grid, row, card, compact
        $this->showStats = $showStats;
        $this->showContact = $showContact;
        $this->limit = $limit;
        $this->featuredOnly = $featuredOnly;
        
        $this->users = $this->getTeamMembers();
    }

    /**
     * Get team members for display
     */
    private function getTeamMembers()
    {
        $companyId = auth()->user()?->company_id;
        
        if (!$companyId) {
            return collect();
        }

        $query = User::where('company_id', $companyId)
            ->where('status', 'active')
            ->with(['teams', 'leads', 'quotations']);

        // If specific team provided, filter by team
        if ($this->team) {
            $query->whereHas('teams', function ($q) {
                $q->where('teams.id', $this->team->id);
            });
        }

        // Show only featured members if requested
        if ($this->featuredOnly) {
            $query->where('is_featured', true);
        }

        return $query->orderBy('is_featured', 'desc')
            ->orderBy('name')
            ->limit($this->limit)
            ->get();
    }

    /**
     * Get user performance statistics
     */
    public function getUserStats(User $user): array
    {
        if (!$this->showStats) {
            return [];
        }

        $stats = [
            'leads' => $user->leads()->count(),
            'quotations' => $user->quotations()->count(),
            'conversions' => $user->quotations()->whereIn('status', ['ACCEPTED', 'CONVERTED'])->count(),
            'active_leads' => $user->leads()->whereIn('status', ['NEW', 'CONTACTED', 'QUOTED'])->count(),
        ];

        // Calculate conversion rate
        $stats['conversion_rate'] = $stats['quotations'] > 0 
            ? round(($stats['conversions'] / $stats['quotations']) * 100, 1) 
            : 0;

        return $stats;
    }

    /**
     * Get user role display name
     */
    public function getUserRole(User $user): string
    {
        return $user->roles->first()?->name ?? 'Team Member';
    }

    /**
     * Get user role color class
     */
    public function getRoleColor(User $user): string
    {
        $role = $this->getUserRole($user);
        
        return match (strtolower($role)) {
            'company_manager' => 'purple',
            'sales_manager' => 'blue',
            'sales_coordinator' => 'green',
            'finance_manager' => 'orange',
            'sales_executive' => 'indigo',
            default => 'gray'
        };
    }

    /**
     * Get display name for role
     */
    public function getRoleDisplayName(User $user): string
    {
        $role = $this->getUserRole($user);
        
        return match (strtolower($role)) {
            'company_manager' => 'Company Manager',
            'sales_manager' => 'Sales Manager',
            'sales_coordinator' => 'Sales Coordinator',
            'finance_manager' => 'Finance Manager',
            'sales_executive' => 'Sales Executive',
            default => 'Team Member'
        };
    }

    /**
     * Get user's primary team
     */
    public function getPrimaryTeam(User $user): ?Team
    {
        return $user->teams->first();
    }

    /**
     * Format phone number for display
     */
    public function formatPhone(?string $phone): ?string
    {
        if (!$phone) return null;
        
        // Simple Malaysian phone formatting
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 10 && str_starts_with($phone, '0')) {
            return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . ' ' . substr($phone, 6);
        } elseif (strlen($phone) === 11 && str_starts_with($phone, '60')) {
            return '+60 ' . substr($phone, 2, 2) . '-' . substr($phone, 4, 3) . ' ' . substr($phone, 7);
        }
        
        return $phone;
    }

    /**
     * Get user avatar URL or generate initials
     */
    public function getAvatarUrl(User $user): ?string
    {
        if ($user->avatar && \Storage::exists($user->avatar)) {
            return \Storage::url($user->avatar);
        }
        
        return null;
    }

    /**
     * Get user initials for avatar
     */
    public function getUserInitials(User $user): string
    {
        $names = explode(' ', $user->name);
        $initials = '';
        
        foreach (array_slice($names, 0, 2) as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
        }
        
        return $initials ?: 'U';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.team-profiles');
    }
}