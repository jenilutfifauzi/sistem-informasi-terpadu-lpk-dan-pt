<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Asset;
use App\Models\CTK;
use App\Models\User;
use App\Policies\AssetPolicy;
use App\Policies\CTKPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        CTK::class => CTKPolicy::class,
        Asset::class => AssetPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
