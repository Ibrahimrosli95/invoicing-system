<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Only superadmins can view all companies
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Company $company): bool
    {
        // Users can view their own company
        return $user->company_id === $company->id || $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only superadmins can create companies
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Company $company): bool
    {
        // Company managers and superadmins can update their company
        return ($user->company_id === $company->id && 
                ($user->hasRole(['superadmin', 'company_manager']))) ||
               $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can manage company settings.
     */
    public function manage(User $user): bool
    {
        // Company managers and superadmins can manage settings
        return $user->hasRole(['superadmin', 'company_manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Company $company): bool
    {
        // Only superadmins can delete companies
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Company $company): bool
    {
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return $user->hasRole('superadmin');
    }
}
