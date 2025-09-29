<?php

namespace App\Providers;

use App\View\Composers\GlobalStatsComposer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
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
        // Register global stats composer for all views
        View::composer('*', GlobalStatsComposer::class);

        // Register markdown blade directive
        Blade::directive('markdown', function ($expression) {
            return "<?php echo App\Helpers\MarkdownHelper::parseContent($expression); ?>";
        });
    }
}
