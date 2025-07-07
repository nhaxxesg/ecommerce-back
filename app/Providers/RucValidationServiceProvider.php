<?php

namespace App\Providers;

use App\Services\RucValidationService;
use Illuminate\Support\ServiceProvider;

class RucValidationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RucValidationService::class, function ($app) {
            return new RucValidationService();
        });
    }

    public function boot(): void
    {
        //
    }
} 