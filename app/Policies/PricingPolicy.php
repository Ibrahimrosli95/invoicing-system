<?php

namespace App\Policies;

use App\Models\PricingItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PricingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view pricing list
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PricingItem $pricingItem): bool
    {
        // Must be same company
        return $user->company_id === $pricingItem->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only managers and above can create pricing items
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
    public function update(User $user, PricingItem $pricingItem): bool
    {
        // Must be same company
        if ($user->company_id !== $pricingItem->company_id) {
            return false;
        }

        // Superadmin and company manager can edit any item
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Finance manager can edit pricing and cost information
        if ($user->hasRole('finance_manager')) {
            return true;
        }

        // Sales manager can edit items (limited fields in frontend)
        if ($user->hasRole('sales_manager')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PricingItem $pricingItem): bool
    {
        // Must be same company
        if ($user->company_id !== $pricingItem->company_id) {
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
    public function restore(User $user, PricingItem $pricingItem): bool
    {
        return $this->delete($user, $pricingItem);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PricingItem $pricingItem): bool
    {
        // Only superadmin can permanently delete
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can duplicate the model.
     */
    public function duplicate(User $user, PricingItem $pricingItem): bool
    {
        // Same as create permissions
        return $this->create($user) && $this->view($user, $pricingItem);
    }

    /**
     * Determine whether the user can toggle status.
     */
    public function toggleStatus(User $user, PricingItem $pricingItem): bool
    {
        // Same as update permissions
        return $this->update($user, $pricingItem);
    }

    /**
     * Determine whether the user can view cost prices.
     */
    public function viewCostPrice(User $user, PricingItem $pricingItem): bool
    {
        // Must be same company
        if ($user->company_id !== $pricingItem->company_id) {
            return false;
        }

        // Only managers can view cost prices
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can edit cost prices.
     */
    public function editCostPrice(User $user, PricingItem $pricingItem): bool
    {
        // Must be same company
        if ($user->company_id !== $pricingItem->company_id) {
            return false;
        }

        // Only finance manager and above can edit cost prices
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager'
        ]);
    }

    /**
     * Determine whether the user can export pricing data.
     */
    public function export(User $user): bool
    {
        // Only managers and above can export data
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can import pricing data.
     */
    public function import(User $user): bool
    {
        // Only senior management can import pricing data (excludes sales_manager)
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager'
        ]);
    }
}
