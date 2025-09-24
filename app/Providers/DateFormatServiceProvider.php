<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Carbon\Carbon;
use App\Helpers\DateHelper;

class DateFormatServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register DateHelper as singleton
        $this->app->singleton('date.helper', function () {
            return new DateHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set global Carbon format for display
        Carbon::setToStringFormat('d/m/Y H:i:s');

        // Register Blade directives for date formatting
        Blade::directive('displayDate', function ($expression) {
            return "<?php echo \App\Helpers\DateHelper::format($expression); ?>";
        });

        Blade::directive('displayDateTime', function ($expression) {
            return "<?php echo \App\Helpers\DateHelper::format($expression, true); ?>";
        });

        Blade::directive('inputDate', function ($expression) {
            return "<?php echo \App\Helpers\DateHelper::formatForInput($expression); ?>";
        });

        Blade::directive('html5Date', function ($expression) {
            return "<?php echo \App\Helpers\DateHelper::formatForHtml5Input($expression); ?>";
        });

        Blade::directive('relativeDate', function ($expression) {
            return "<?php echo \App\Helpers\DateHelper::formatRelative($expression); ?>";
        });

        // Create global view helper
        view()->share('dateHelper', app('date.helper'));
    }
}
