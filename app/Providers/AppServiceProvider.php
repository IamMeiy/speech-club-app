<?php

namespace App\Providers;

use App\Http\ViewComposers\AppLayoutComposer;
use App\Services\ClubAccessService;
use App\Services\ClubContextService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register ClubContextService as a singleton so it holds state across a request
        $this->app->singleton(ClubContextService::class);

        // ClubAccessService depends on ClubContextService
        $this->app->singleton(ClubAccessService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super Admin bypasses all Spatie permission gates
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Super Admin')) {
                return true;
            }
        });

        // Share club context with the app layout
        View::composer('components.layouts.app', AppLayoutComposer::class);
    }
}

