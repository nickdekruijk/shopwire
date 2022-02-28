<?php

namespace NickDeKruijk\Shopwire;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use NickDeKruijk\Shopwire\Livewire\AddToCart;
use NickDeKruijk\Shopwire\Livewire\Cart;
use NickDeKruijk\Shopwire\Livewire\Checkout;

class ShopwireServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'shopwire');

        $this->publishes([
            __DIR__ . '/config.php' => config_path('shopwire.php'),
        ], 'config');

        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        $this->loadTranslationsFrom(__DIR__ . '/lang', 'shopwire');

        if (config('shopwire.migration')) {
            $this->loadMigrationsFrom(__DIR__ . '/migrations/');
        }

        Livewire::component('shopwire-add', AddToCart::class);
        Livewire::component('shopwire-cart', Cart::class);
        Livewire::component('shopwire-checkout', Checkout::class);
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
