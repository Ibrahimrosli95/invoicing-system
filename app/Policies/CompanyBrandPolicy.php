<?php

namespace App\Policies;

use App\Models\CompanyBrand;
use App\Models\User;

class CompanyBrandPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Company managers and above can view brands
        return $user->hasPermissionTo('manage company settings');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CompanyBrand $companyBrand): bool
    {
        // Can view if same company and has permission
        return $user->company_id === $companyBrand->company_id
            && $user->hasPermissionTo('manage company settings');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Company managers and above can create brands
        return $user->hasPermissionTo('manage company settings');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CompanyBrand $companyBrand): bool
    {
        // Can update if same company and has permission
        return $user->company_id === $companyBrand->company_id
            && $user->hasPermissionTo('manage company settings');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompanyBrand $companyBrand): bool
    {
        // Can delete if same company, has permission, and not used in documents
        return $user->company_id === $companyBrand->company_id
            && $user->hasPermissionTo('manage company settings')
            && !$companyBrand->isUsedInDocuments();
    }

    /**
     * Determine whether the user can set brand as default.
     */
    public function setDefault(User $user, CompanyBrand $companyBrand): bool
    {
        // Can set as default if same company and has permission
        return $user->company_id === $companyBrand->company_id
            && $user->hasPermissionTo('manage company settings');
    }

    /**
     * Determine whether the user can toggle brand status.
     */
    public function toggleStatus(User $user, CompanyBrand $companyBrand): bool
    {
        // Can toggle status if same company and has permission
        return $user->company_id === $companyBrand->company_id
            && $user->hasPermissionTo('manage company settings');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CompanyBrand $companyBrand): bool
    {
        return $user->company_id === $companyBrand->company_id
            && $user->hasPermissionTo('manage company settings');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CompanyBrand $companyBrand): bool
    {
        return $user->company_id === $companyBrand->company_id
            && $user->hasPermissionTo('manage company settings')
            && !$companyBrand->isUsedInDocuments();
    }
}
