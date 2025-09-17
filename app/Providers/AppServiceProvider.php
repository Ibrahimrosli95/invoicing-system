<?php

namespace App\Providers;

use App\Models\Testimonial;
use App\Models\Certification;
use App\Models\CaseStudy;
use App\Observers\TestimonialObserver;
use App\Observers\CertificationObserver;
use App\Observers\CaseStudyObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for automatic proof compilation
        Testimonial::observe(TestimonialObserver::class);
        Certification::observe(CertificationObserver::class);
        CaseStudy::observe(CaseStudyObserver::class);
    }
}
