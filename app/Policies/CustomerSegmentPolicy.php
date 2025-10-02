<?php

namespace App\Policies;

use App\Models\CustomerSegment;
use App\Models\User;

class CustomerSegmentPolicy
{
    /**
     * Determine whether the user can view any customer segments.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view customer segments');
    }

    /**
     * Determine whether the user can view the customer segment.
     */
    public function view(User $user, CustomerSegment $segment): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->company_id !== $segment->company_id) {
            return false;
        }

        return $user->can('view customer segments');
    }

    /**
     * Determine whether the user can create customer segments.
     */
    public function create(User $user): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->can('create customer segments');
    }

    /**
     * Determine whether the user can edit the customer segment (show edit form).
     */
    public function edit(User $user, CustomerSegment $segment): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->company_id !== $segment->company_id) {
            return false;
        }

        return $user->can('edit customer segments');
    }

    /**
     * Determine whether the user can update the customer segment.
     */
    public function update(User $user, CustomerSegment $segment): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->company_id !== $segment->company_id) {
            return false;
        }

        return $user->can('edit customer segments');
    }

    /**
     * Determine whether the user can delete the customer segment.
     */
    public function delete(User $user, CustomerSegment $segment): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->company_id !== $segment->company_id) {
            return false;
        }

        return $user->can('delete customer segments');
    }
}
