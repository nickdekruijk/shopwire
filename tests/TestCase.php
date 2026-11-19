<?php

namespace NickDeKruijk\Shopwire\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Monarobase\CountryList\CountryList;
use Monarobase\CountryList\CountryListFacade;
use Monolog\Handler\NullHandler;
use NickDeKruijk\Shopwire\ShopwireFacade;
use NickDeKruijk\Shopwire\ShopwireServiceProvider;
use NickDeKruijk\Shopwire\Tests\Fixtures\FakePaymentProvider;
use NickDeKruijk\Shopwire\Tests\Fixtures\Product;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            ShopwireServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Countries' => CountryListFacade::class,
            'Shopwire' => ShopwireFacade::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.debug', true);
        $app['config']->set('app.key', 'base64:' . base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('logging.channels.shopwire', [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ]);
        $app['config']->set('shopwire.product_model', Product::class);
        $app['config']->set('shopwire.payment_provider', FakePaymentProvider::class);

        // The country list looks for its data in the application's vendor directory,
        // which is the Testbench skeleton here. Point it to this package's instead.
        $app->singleton(CountryList::class, fn() => new CountryList(__DIR__ . '/../vendor/umpirsky/country-list/data'));
    }

    protected function defineDatabaseMigrations()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('price', 8, 2)->default(0);
            $table->unsignedBigInteger('vat_id')->nullable();
            $table->decimal('stock', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(1);
            $table->unsignedBigInteger('product_id');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('stock', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->unsignedBigInteger('sort')->nullable();
            $table->timestamps();
        });
    }
}
