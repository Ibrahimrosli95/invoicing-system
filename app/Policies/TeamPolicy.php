<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine whether the user can view any teams.
     */
    public function viewAny(User $user): bool
    {
        // Superadmin has access to everything
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->can('view teams') ||
               $user->can('manage teams');
    }

    /**
     * Determine whether the user can view the team.
     */
    public function view(User $user, Team $team): bool
    {
        // Must be same company
        if ($user->company_id !== $team->company_id) {
            return false;
        }

        // Superadmin and company manager can view all teams
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Sales managers can view their teams
        if ($user->hasRole('sales_manager')) {
            return $team->manager_id === $user->id;
        }

        // Coordinators can view their teams
        if ($user->hasRole('sales_coordinator')) {
            return $team->coordinator_id === $user->id;
        }

        // Sales executives can view teams they belong to
        if ($user->hasRole('sales_executive')) {
            return $user->teams->contains($team);
        }

        return false;
    }

    /**
     * Determine whether the user can create teams.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'company_manager']) ||
               $user->can('teams.create');
    }

    /**
     * Determine whether the user can update the team.
     */
    public function update(User $user, Team $team): bool
    {
        // Must be same company
        if ($user->company_id !== $team->company_id) {
            return false;
        }

        // Superadmin and company manager can update all teams
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Sales managers can update their teams
        if ($user->hasRole('sales_manager')) {
            return $team->manager_id === $user->id;
        }

        return $user->can('teams.update');
    }

    /**
     * Determine whether the user can delete the team.
     */
    public function delete(User $user, Team $team): bool
    {
        // Must be same company
        if ($user->company_id !== $team->company_id) {
            return false;
        }

        // Only superadmin and company manager can delete teams
        return $user->hasAnyRole(['superadmin', 'company_manager']) ||
               $user->can('teams.delete');
    }
}