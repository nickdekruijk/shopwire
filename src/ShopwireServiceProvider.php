<?php

namespace NickDeKruijk\Shopwire;

use Illuminate\Support\ServiceProvider;

class ShopwireServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path('shopwire.php'),
        ], 'config');

        $this->loadTranslationsFrom(__DIR__ . '/lang', 'shopwire');

        if (config('shopwire.migration')) {
            $this->loadMigrationsFrom(__DIR__ . '/migrations/');
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'shopwire');

        // Register the main class to use with the facade
        $this->app->singleton('nickdekruijk-shopwire', function () {
            return new Shopwire;
        });
    }
}
