<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Team;
use Illuminate\Auth\Access\Response;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any invoices.
     */
    public function viewAny(User $user): bool
    {
        // Superadmin has access to everything
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->can('view invoices') ||
               $user->can('manage invoices') ||
               $user->hasAnyRole(['company_manager', 'finance_manager', 'sales_manager', 'sales_coordinator', 'sales_executive']);
    }

    /**
     * Determine whether the user can view the invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        // Must be same company
        if ($user->company_id !== $invoice->company_id) {
            return false;
        }

        // Superadmin and company manager can view all invoices
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Finance manager can view all invoices
        if ($user->hasRole('finance_manager')) {
            return true;
        }

        // Sales managers can view invoices from teams they manage
        if ($user->hasRole('sales_manager')) {
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $managedTeamIds->contains($invoice->team_id);
        }

        // Sales coordinators can view invoices from teams they coordinate
        if ($user->hasRole('sales_coordinator')) {
            $coordinatedTeamIds = Team::where('coordinator_id', $user->id)->pluck('id');
            return $coordinatedTeamIds->contains($invoice->team_id);
        }

        // Sales executives can only view invoices assigned to them or created by them
        if ($user->hasRole('sales_executive')) {
            return $invoice->assigned_to === $user->id || $invoice->created_by === $user->id;
        }

        return $user->can('view invoices');
    }

    /**
     * Determine whether the user can create invoices.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager', 'sales_manager', 'sales_coordinator', 'sales_executive']) ||
               $user->can('invoices.create');
    }

    /**
     * Determine whether the user can update the invoice.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        // Must be same company
        if ($user->company_id !== $invoice->company_id) {
            return false;
        }

        // Superadmin and company manager can update all invoices
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Finance manager can update all invoices (especially payment-related)
        if ($user->hasRole('finance_manager')) {
            return true;
        }

        // Sales managers can update invoices from teams they manage
        if ($user->hasRole('sales_manager')) {
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $managedTeamIds->contains($invoice->team_id);
        }

        // Sales coordinators can update invoices from teams they coordinate
        if ($user->hasRole('sales_coordinator')) {
            $coordinatedTeamIds = Team::where('coordinator_id', $user->id)->pluck('id');
            return $coordinatedTeamIds->contains($invoice->team_id);
        }

        // Sales executives can only update invoices assigned to them or created by them
        if ($user->hasRole('sales_executive')) {
            return $invoice->assigned_to === $user->id || $invoice->created_by === $user->id;
        }

        return $user->can('invoices.update');
    }

    /**
     * Determine whether the user can delete the invoice.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        // Must be same company
        if ($user->company_id !== $invoice->company_id) {
            return false;
        }

        // Only superadmin, company manager, and finance manager can delete invoices
        if ($user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager'])) {
            return true;
        }

        // Sales managers can only delete draft invoices from teams they manage
        if ($user->hasRole('sales_manager') && $invoice->status === 'DRAFT') {
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $managedTeamIds->contains($invoice->team_id);
        }

        return $user->can('invoices.delete');
    }

    /**
     * Determine whether the user can restore the invoice.
     */
    public function restore(User $user, Invoice $invoice): bool
    {
        return $this->delete($user, $invoice);
    }

    /**
     * Determine whether the user can permanently delete the invoice.
     */
    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can send invoices.
     */
    public function send(User $user, Invoice $invoice): bool
    {
        // Must be same company
        if ($user->company_id !== $invoice->company_id) {
            return false;
        }

        // Superadmin, company manager, finance manager can send invoices
        if ($user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager'])) {
            return true;
        }

        // Sales managers can send invoices from their teams
        if ($user->hasRole('sales_manager')) {
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $managedTeamIds->contains($invoice->team_id);
        }

        // Sales coordinators can send invoices from teams they coordinate
        if ($user->hasRole('sales_coordinator')) {
            $coordinatedTeamIds = Team::where('coordinator_id', $user->id)->pluck('id');
            return $coordinatedTeamIds->contains($invoice->team_id);
        }

        // Sales executives can send their own invoices
        if ($user->hasRole('sales_executive')) {
            return $invoice->assigned_to === $user->id || $invoice->created_by === $user->id;
        }

        return $user->can('invoices.send');
    }

    /**
     * Determine whether the user can record payments for invoices.
     */
    public function recordPayment(User $user, Invoice $invoice): bool
    {
        // Must be same company
        if ($user->company_id !== $invoice->company_id) {
            return false;
        }

        // Superadmin, company manager, and finance manager can record payments
        if ($user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager'])) {
            return true;
        }

        // Sales managers can record payments for invoices from their teams
        if ($user->hasRole('sales_manager')) {
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $managedTeamIds->contains($invoice->team_id);
        }

        // Sales coordinators can record payments for invoices from teams they coordinate
        if ($user->hasRole('sales_coordinator')) {
            $coordinatedTeamIds = Team::where('coordinator_id', $user->id)->pluck('id');
            return $coordinatedTeamIds->contains($invoice->team_id);
        }

        return $user->can('payments.record');
    }

    /**
     * Determine whether the user can manage payment records.
     */
    public function managePayments(User $user, Invoice $invoice): bool
    {
        // Must be same company
        if ($user->company_id !== $invoice->company_id) {
            return false;
        }

        // Only superadmin, company manager, and finance manager can manage payments
        if ($user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager'])) {
            return true;
        }

        return $user->can('manage invoices');
    }

    /**
     * Determine whether the user can view invoice analytics.
     */
    public function viewAnalytics(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager', 'sales_manager']) ||
               $user->can('invoices.analytics');
    }

    /**
     * Determine whether the user can view financial reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager']) ||
               $user->can('reports.financial');
    }

    /**
     * Determine whether the user can export invoice data.
     */
    public function export(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager']) ||
               $user->can('invoices.export');
    }
}