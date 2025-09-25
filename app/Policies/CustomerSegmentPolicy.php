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
        return $user->can('view customer segments');
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

        return $user->can('view customer segments');
    }

    /**
     * Determine whether the user can create customer segments.
     */
    public function create(User $user): bool
    {
        return $user->can('create customer segments');
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

        return $user->can('edit customer segments');
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

        return $user->can('delete customer segments');
    }
}
