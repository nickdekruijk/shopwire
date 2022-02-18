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
    }
}
