<?php

namespace App\Policies;

use App\Models\PricingCategory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PricingCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view category list
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PricingCategory $pricingCategory): bool
    {
        // Must be same company
        return $user->company_id === $pricingCategory->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only managers and above can create categories
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PricingCategory $pricingCategory): bool
    {
        // Must be same company
        if ($user->company_id !== $pricingCategory->company_id) {
            return false;
        }

        // Superadmin and company manager can edit any category
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Finance manager can edit categories
        if ($user->hasRole('finance_manager')) {
            return true;
        }

        // Sales manager can edit categories
        if ($user->hasRole('sales_manager')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PricingCategory $pricingCategory): bool
    {
        // Must be same company
        if ($user->company_id !== $pricingCategory->company_id) {
            return false;
        }

        // Only superadmin, company manager, and finance manager can delete
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager'
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PricingCategory $pricingCategory): bool
    {
        return $this->delete($user, $pricingCategory);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PricingCategory $pricingCategory): bool
    {
        // Only superadmin can permanently delete
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can duplicate the model.
     */
    public function duplicate(User $user, PricingCategory $pricingCategory): bool
    {
        // Same as create permissions
        return $this->create($user) && $this->view($user, $pricingCategory);
    }

    /**
     * Determine whether the user can toggle status.
     */
    public function toggleStatus(User $user, PricingCategory $pricingCategory): bool
    {
        // Same as update permissions
        return $this->update($user, $pricingCategory);
    }
}
