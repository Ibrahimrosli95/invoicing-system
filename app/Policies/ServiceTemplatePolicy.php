<?php

namespace App\Policies;

use App\Models\ServiceTemplate;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServiceTemplatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view template lists (filtered by their permissions)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceTemplate $serviceTemplate): bool
    {
        return $serviceTemplate->canBeUsedBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only managers and above can create service templates
        return $user->hasAnyRole([
            'superadmin',
            'company_manager', 
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServiceTemplate $serviceTemplate): bool
    {
        return $serviceTemplate->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceTemplate $serviceTemplate): bool
    {
        return $serviceTemplate->canBeDeletedBy($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ServiceTemplate $serviceTemplate): bool
    {
        // Same permissions as delete
        return $this->delete($user, $serviceTemplate);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ServiceTemplate $serviceTemplate): bool
    {
        // Only superadmin can permanently delete
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can duplicate the template.
     */
    public function duplicate(User $user, ServiceTemplate $serviceTemplate): bool
    {
        // Users who can view the template can duplicate it
        return $this->view($user, $serviceTemplate) && 
               $user->hasAnyRole(['superadmin', 'company_manager', 'sales_manager']);
    }

    /**
     * Determine whether the user can use the template (convert to quotation).
     */
    public function use(User $user, ServiceTemplate $serviceTemplate): bool
    {
        return $serviceTemplate->canBeUsedBy($user);
    }

    /**
     * Determine whether the user can modify template status (activate/deactivate).
     */
    public function toggleStatus(User $user, ServiceTemplate $serviceTemplate): bool
    {
        return $this->update($user, $serviceTemplate);
    }
}
