<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        // Company managers and above can view users in their company
        return $user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager']);
    }

    /**
     * Determine whether the user can view a specific user.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view themselves
        if ($user->id === $model->id) {
            return true;
        }

        // Different company = no access
        if ($user->company_id !== $model->company_id) {
            return false;
        }

        // Company managers and above can view users in their company
        if ($user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager'])) {
            return true;
        }

        // Sales managers can view users in their teams
        if ($user->hasRole('sales_manager')) {
            $userTeamIds = $user->teams->pluck('id');
            $modelTeamIds = $model->teams->pluck('id');
            
            return $userTeamIds->intersect($modelTeamIds)->isNotEmpty();
        }

        return false;
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        // Only company managers and above can create users
        return $user->hasAnyRole(['superadmin', 'company_manager']);
    }

    /**
     * Determine whether the user can update a specific user.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update themselves (but with restricted fields in controller)
        if ($user->id === $model->id) {
            return true;
        }

        // Different company = no access
        if ($user->company_id !== $model->company_id) {
            return false;
        }

        // Superadmin can update anyone
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Company managers can update users in their company (except superadmins and other company managers)
        if ($user->hasRole('company_manager')) {
            return !$model->hasAnyRole(['superadmin', 'company_manager']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete a specific user.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Different company = no access
        if ($user->company_id !== $model->company_id) {
            return false;
        }

        // Superadmin can delete anyone (except themselves, handled above)
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Company managers can delete users in their company (except superadmins and other company managers)
        if ($user->hasRole('company_manager')) {
            return !$model->hasAnyRole(['superadmin', 'company_manager']);
        }

        return false;
    }

    /**
     * Determine whether the user can assign roles to other users.
     */
    public function assignRole(User $user, User $model): bool
    {
        // Different company = no access
        if ($user->company_id !== $model->company_id) {
            return false;
        }

        // Superadmin can assign any role
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Company managers can assign roles to users in their company (except superadmin role)
        if ($user->hasRole('company_manager')) {
            return !$model->hasRole('superadmin');
        }

        return false;
    }

    /**
     * Determine whether the user can manage team assignments.
     */
    public function manageTeams(User $user, User $model): bool
    {
        // Different company = no access
        if ($user->company_id !== $model->company_id) {
            return false;
        }

        // Company managers and above can manage team assignments
        return $user->hasAnyRole(['superadmin', 'company_manager', 'sales_manager']);
    }

    /**
     * Determine whether the user can activate/deactivate other users.
     */
    public function toggleStatus(User $user, User $model): bool
    {
        // Cannot toggle own status
        if ($user->id === $model->id) {
            return false;
        }

        // Different company = no access
        if ($user->company_id !== $model->company_id) {
            return false;
        }

        // Company managers and above can toggle user status
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            // Company managers cannot deactivate other company managers or superadmins
            if ($user->hasRole('company_manager')) {
                return !$model->hasAnyRole(['superadmin', 'company_manager']);
            }
            return true;
        }

        return false;
    }
}
