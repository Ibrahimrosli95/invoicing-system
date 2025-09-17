<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebhookEndpoint;

class WebhookEndpointPolicy
{
    /**
     * Determine whether the user can view any webhook endpoints.
     */
    public function viewAny(User $user): bool
    {
        // Only company managers and above can manage webhooks
        return $user->hasAnyRole(['superadmin', 'company_manager']);
    }

    /**
     * Determine whether the user can view the webhook endpoint.
     */
    public function view(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        // Must be same company and have appropriate role
        return $user->company_id === $webhookEndpoint->company_id
            && $user->hasAnyRole(['superadmin', 'company_manager']);
    }

    /**
     * Determine whether the user can create webhook endpoints.
     */
    public function create(User $user): bool
    {
        // Only company managers and above can create webhooks
        return $user->hasAnyRole(['superadmin', 'company_manager']);
    }

    /**
     * Determine whether the user can update the webhook endpoint.
     */
    public function update(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        // Must be same company and have appropriate role
        return $user->company_id === $webhookEndpoint->company_id
            && $user->hasAnyRole(['superadmin', 'company_manager']);
    }

    /**
     * Determine whether the user can delete the webhook endpoint.
     */
    public function delete(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        // Must be same company and have appropriate role
        return $user->company_id === $webhookEndpoint->company_id
            && $user->hasAnyRole(['superadmin', 'company_manager']);
    }

    /**
     * Determine whether the user can restore the webhook endpoint.
     */
    public function restore(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        return $this->update($user, $webhookEndpoint);
    }

    /**
     * Determine whether the user can permanently delete the webhook endpoint.
     */
    public function forceDelete(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        // Only superadmin can force delete
        return $user->hasRole('superadmin');
    }
}