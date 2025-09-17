<?php

namespace App\Policies;

use App\Models\CustomerSegment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerSegmentPolicy
{
    /**
     * Determine whether the user can view any customer segments.
     */
    public function viewAny(User $user): bool
    {
        // Finance managers and above can view segments
        return $user->hasAnyRole([
            'superadmin',
            'company_manager', 
            'finance_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can view the customer segment.
     */
    public function view(User $user, CustomerSegment $segment): bool
    {
        // Must be same company
        if ($user->company_id !== $segment->company_id) {
            return false;
        }

        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager', 
            'sales_manager',
            'sales_coordinator'
        ]);
    }

    /**
     * Determine whether the user can create customer segments.
     */
    public function create(User $user): bool
    {
        // Only managers and above can create segments
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager'
        ]);
    }

    /**
     * Determine whether the user can update the customer segment.
     */
    public function update(User $user, CustomerSegment $segment): bool
    {
        // Must be same company
        if ($user->company_id !== $segment->company_id) {
            return false;
        }

        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager'
        ]);
    }

    /**
     * Determine whether the user can delete the customer segment.
     */
    public function delete(User $user, CustomerSegment $segment): bool
    {
        // Must be same company
        if ($user->company_id !== $segment->company_id) {
            return false;
        }

        // Only company managers and above can delete
        return $user->hasAnyRole([
            'superadmin',
            'company_manager'
        ]);
    }
}
