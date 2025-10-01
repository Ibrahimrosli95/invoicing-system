<?php

namespace App\Providers;

use App\Models\Team;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\CustomerSegment;
use App\Models\PricingItem;
use App\Models\WebhookEndpoint;
use App\Policies\TeamPolicy;
use App\Policies\LeadPolicy;
use App\Policies\QuotationPolicy;
use App\Policies\CustomerSegmentPolicy;
use App\Policies\PricingPolicy;
use App\Policies\WebhookEndpointPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
        Lead::class => LeadPolicy::class,
        Quotation::class => QuotationPolicy::class,
        CustomerSegment::class => CustomerSegmentPolicy::class,
        PricingItem::class => PricingPolicy::class,
        WebhookEndpoint::class => WebhookEndpointPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}