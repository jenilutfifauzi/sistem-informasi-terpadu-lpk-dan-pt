<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\CTK;
use App\Observers\AssetObserver;
use App\Observers\CTKObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        CTK::observe(CTKObserver::class);
        Asset::observe(AssetObserver::class);
    }
}
