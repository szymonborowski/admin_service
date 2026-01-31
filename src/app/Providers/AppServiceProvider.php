<?php

namespace App\Providers;

use App\Auth\ApiUserProvider;
use App\Services\UsersApiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(UsersApiService::class, function ($app) {
            return new UsersApiService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('api_users', function ($app, array $config) {
            return new ApiUserProvider($app->make(UsersApiService::class));
        });
    }
}
