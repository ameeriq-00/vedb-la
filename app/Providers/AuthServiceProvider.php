<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Vehicle;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        'App\Models\Vehicle' => 'App\Policies\VehiclePolicy',
        'App\Models\EditRequest' => 'App\Policies\EditRequestPolicy',
        'App\Models\VehicleTransfer' => 'App\Policies\VehicleTransferPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // إعطاء صلاحية لمستخدمي الآليات لإضافة عجلات حكومية
        Gate::define('create-government-vehicle', function (User $user) {
            return $user->hasRole(['admin', 'verifier', 'vehicles_dept']);
        });
        Gate::define('create-government-vehicle', function (User $user) {
            return $user->hasRole(['admin', 'verifier', 'vehicles_dept']);
        });
    }
}