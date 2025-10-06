<?php

namespace App\Policies;

use App\Models\ServiceCategory;
use App\Models\User;

class ServiceCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Sales managers and above can view categories
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceCategory $serviceCategory): bool
    {
        // Must be same company
        if ($user->company_id !== $serviceCategory->company_id) {
            return false;
        }

        // Sales managers and above can view
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Sales managers and above can create categories
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServiceCategory $serviceCategory): bool
    {
        // Must be same company
        if ($user->company_id !== $serviceCategory->company_id) {
            return false;
        }

        // Sales managers and above can update
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceCategory $serviceCategory): bool
    {
        // Must be same company
        if ($user->company_id !== $serviceCategory->company_id) {
            return false;
        }

        // Only company managers and superadmin can delete
        return $user->hasAnyRole([
            'superadmin',
            'company_manager'
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ServiceCategory $serviceCategory): bool
    {
        return $this->delete($user, $serviceCategory);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ServiceCategory $serviceCategory): bool
    {
        return $this->delete($user, $serviceCategory);
    }
}
