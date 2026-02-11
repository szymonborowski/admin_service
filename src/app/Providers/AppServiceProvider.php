<?php

namespace App\Providers;

use App\Auth\ApiUserProvider;
use App\Http\Responses\SsoLogoutResponse;
use App\Services\UsersApiService;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
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

        $this->app->bind(LogoutResponseContract::class, SsoLogoutResponse::class);
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
