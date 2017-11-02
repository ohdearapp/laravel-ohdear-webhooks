<?php

namespace OhDear\LaravelWebhooks;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OhDearWebhooksServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ohdear-webhooks.php' => config_path('ohdear-webhooks.php'),
            ], 'config');
        }

        Route::macro('ohDearWebhooks', function ($url) {
            return Route::post($url, \OhDear\LaravelWebhooks\OhDearWebhooksController::class);
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ohdear-webhooks.php', 'skeleton');
    }
}
