<?php

namespace App\Policies;

use App\Models\CustomerSegment;
use App\Models\User;

class CustomerSegmentPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        \Log::info('CustomerSegmentPolicy@before called', [
            'user_id' => $user->id,
            'ability' => $ability,
            'has_superadmin' => $user->hasRole('superadmin'),
            'user_roles' => $user->getRoleNames()->toArray(),
        ]);

        if ($user->hasRole('superadmin')) {
            \Log::info('Superadmin bypass - returning true');
            return true;
        }

        return null; // Fall through to individual policy methods
    }

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
        // Superadmin bypass in before() method
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
        // Superadmin bypass in before() method
        return $user->can('create customer segments');
    }

    /**
     * Determine whether the user can update the customer segment.
     * Note: Laravel's authorizeResource() uses this for both edit and update routes.
     */
    public function update(User $user, CustomerSegment $segment): bool
    {
        // Superadmin bypass in before() method
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
        // Superadmin bypass in before() method
        if ($user->company_id !== $segment->company_id) {
            return false;
        }

        return $user->can('delete customer segments');
    }
}
