<?php

namespace App\Providers;

use App\Services\FrontendContentService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Custom Blade directive for frontend content resolution
        // Usage: @content('navigation.technicians', 'Engineers / Technicians')
        Blade::directive('content', function ($expression) {
            return "<?php echo e(\\App\\Services\\FrontendContentService::get({$expression})); ?>";
        });
    }
}
