<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any customers.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view customers in their company
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager',
            'sales_manager',
            'sales_coordinator',
            'sales_executive'
        ]);
    }

    /**
     * Determine whether the user can view the customer.
     */
    public function view(User $user, Customer $customer): bool
    {
        // Multi-tenant check
        if ($user->company_id !== $customer->company_id) {
            return false;
        }

        // All roles can view customers in their company
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager',
            'sales_manager',
            'sales_coordinator',
            'sales_executive'
        ]);
    }

    /**
     * Determine whether the user can create customers.
     */
    public function create(User $user): bool
    {
        // Sales roles and above can create customers
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager',
            'sales_manager',
            'sales_coordinator',
            'sales_executive'
        ]);
    }

    /**
     * Determine whether the user can update the customer.
     */
    public function update(User $user, Customer $customer): bool
    {
        // Multi-tenant check
        if ($user->company_id !== $customer->company_id) {
            return false;
        }

        // Managers and coordinators can update any customer
        if ($user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager', 'sales_manager', 'sales_coordinator'])) {
            return true;
        }

        // Sales executives can update customers they created or are associated with their invoices/quotations
        if ($user->hasRole('sales_executive')) {
            return $customer->created_by === $user->id ||
                   $customer->invoices()->where('assigned_to', $user->id)->exists() ||
                   $customer->quotations()->where('assigned_to', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can delete the customer.
     */
    public function delete(User $user, Customer $customer): bool
    {
        // Multi-tenant check
        if ($user->company_id !== $customer->company_id) {
            return false;
        }

        // Only managers can delete customers
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can restore the customer.
     */
    public function restore(User $user, Customer $customer): bool
    {
        return $this->delete($user, $customer);
    }

    /**
     * Determine whether the user can permanently delete the customer.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        // Only superadmin and company managers can permanently delete
        return $user->hasAnyRole(['superadmin', 'company_manager']);
    }

    /**
     * Determine whether the user can convert leads to customers.
     */
    public function convertLead(User $user): bool
    {
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager',
            'sales_coordinator',
            'sales_executive'
        ]);
    }

    /**
     * Determine whether the user can search customers.
     */
    public function search(User $user): bool
    {
        return $this->viewAny($user);
    }
}
