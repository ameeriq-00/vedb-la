<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;

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
        // Fix for MySQL < 5.7.7 and MariaDB < 10.2.2
        Schema::defaultStringLength(191);
        
        // Use Bootstrap pagination
        Paginator::useBootstrap();
        
        // Custom blade directives
        Blade::directive('money', function ($amount) {
            return "<?php echo number_format($amount, 2) . ' د.ع'; ?>";
        });
        
        // Define an admin directive to check if user has admin role
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->hasRole('admin');
        });
        
        // Define a role directive to check if user has a specific role
        Blade::if('role', function ($role) {
            return auth()->check() && auth()->user()->hasRole($role);
        });
        
        // Define a permission directive to check if user has a specific permission
        Blade::if('permission', function ($permission) {
            return auth()->check() && auth()->user()->hasPermissionTo($permission);
        });
    }
}