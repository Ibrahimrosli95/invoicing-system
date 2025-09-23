<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;
use App\Models\Team;
use Illuminate\Auth\Access\Response;

class QuotationPolicy
{
    /**
     * Determine whether the user can view any quotations.
     */
    public function viewAny(User $user): bool
    {
        // Superadmin has access to everything
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->can('view quotations') ||
               $user->can('manage quotations') ||
               $user->hasAnyRole(['company_manager', 'finance_manager', 'sales_manager', 'sales_coordinator', 'sales_executive']);
    }

    /**
     * Determine whether the user can view the quotation.
     */
    public function view(User $user, Quotation $quotation): bool
    {
        // Must be same company
        if ($user->company_id !== $quotation->company_id) {
            return false;
        }

        // Superadmin and company manager can view all quotations
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Finance manager can view all quotations
        if ($user->hasRole('finance_manager')) {
            return true;
        }

        // Sales managers can view quotations from teams they manage
        if ($user->hasRole('sales_manager')) {
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $managedTeamIds->contains($quotation->team_id);
        }

        // Sales coordinators can view quotations from teams they coordinate
        if ($user->hasRole('sales_coordinator')) {
            $coordinatedTeamIds = Team::where('coordinator_id', $user->id)->pluck('id');
            return $coordinatedTeamIds->contains($quotation->team_id);
        }

        // Sales executives can only view quotations assigned to them or created by them
        if ($user->hasRole('sales_executive')) {
            return $quotation->assigned_to === $user->id || $quotation->created_by === $user->id;
        }

        return $user->can('view quotations');
    }

    /**
     * Determine whether the user can create quotations.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'company_manager', 'sales_manager', 'sales_coordinator', 'sales_executive']) ||
               $user->can('quotations.create');
    }

    /**
     * Determine whether the user can update the quotation.
     */
    public function update(User $user, Quotation $quotation): bool
    {
        // Must be same company
        if ($user->company_id !== $quotation->company_id) {
            return false;
        }

        // Superadmin and company manager can update all quotations
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Finance manager can update quotation status (for acceptance/rejection)
        if ($user->hasRole('finance_manager')) {
            return true;
        }

        // Sales managers can update quotations from teams they manage
        if ($user->hasRole('sales_manager')) {
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $managedTeamIds->contains($quotation->team_id);
        }

        // Sales coordinators can update quotations from teams they coordinate
        if ($user->hasRole('sales_coordinator')) {
            $coordinatedTeamIds = Team::where('coordinator_id', $user->id)->pluck('id');
            return $coordinatedTeamIds->contains($quotation->team_id);
        }

        // Sales executives can only update quotations assigned to them or created by them
        if ($user->hasRole('sales_executive')) {
            return $quotation->assigned_to === $user->id || $quotation->created_by === $user->id;
        }

        return $user->can('quotations.update');
    }

    /**
     * Determine whether the user can delete the quotation.
     */
    public function delete(User $user, Quotation $quotation): bool
    {
        // Must be same company
        if ($user->company_id !== $quotation->company_id) {
            return false;
        }

        // Only superadmin, company manager, and sales manager can delete quotations
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Sales managers can only delete quotations from teams they manage
        if ($user->hasRole('sales_manager')) {
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $managedTeamIds->contains($quotation->team_id);
        }

        return $user->can('quotations.delete');
    }

    /**
     * Determine whether the user can restore the quotation.
     */
    public function restore(User $user, Quotation $quotation): bool
    {
        return $this->delete($user, $quotation);
    }

    /**
     * Determine whether the user can permanently delete the quotation.
     */
    public function forceDelete(User $user, Quotation $quotation): bool
    {
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can send quotations.
     */
    public function send(User $user, Quotation $quotation): bool
    {
        // Must be same company
        if ($user->company_id !== $quotation->company_id) {
            return false;
        }

        // Superadmin, company manager, and sales roles can send quotations
        if ($user->hasAnyRole(['superadmin', 'company_manager', 'sales_manager', 'sales_coordinator'])) {
            return true;
        }

        // Sales executives can send their own quotations
        if ($user->hasRole('sales_executive')) {
            return $quotation->assigned_to === $user->id || $quotation->created_by === $user->id;
        }

        return $user->can('quotations.send');
    }

    /**
     * Determine whether the user can approve quotations.
     */
    public function approve(User $user, Quotation $quotation): bool
    {
        // Must be same company
        if ($user->company_id !== $quotation->company_id) {
            return false;
        }

        // Superadmin, company manager, finance manager, and sales manager can approve
        if ($user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager', 'sales_manager'])) {
            return true;
        }

        return $user->can('quotations.approve');
    }

    /**
     * Determine whether the user can convert quotations to invoices.
     */
    public function convert(User $user, Quotation $quotation): bool
    {
        // Must be same company
        if ($user->company_id !== $quotation->company_id) {
            return false;
        }

        // Superadmin, company manager, and finance manager can convert
        if ($user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager'])) {
            return true;
        }

        // Sales managers can convert quotations from their teams
        if ($user->hasRole('sales_manager')) {
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $managedTeamIds->contains($quotation->team_id);
        }

        return $user->can('quotations.convert');
    }

    /**
     * Determine whether the user can view quotation analytics.
     */
    public function viewAnalytics(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager', 'sales_manager']) ||
               $user->can('quotations.analytics');
    }
}
