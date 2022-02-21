<?php

use App\Models\Product;

return [

    /*
    |--------------------------------------------------------------------------
    | migration
    |--------------------------------------------------------------------------
    | Include the migration if required
    */
    'migration' => true,

    /*
    |--------------------------------------------------------------------------
    | table_prefix
    |--------------------------------------------------------------------------
    | The tables created by the package will be prefixed with this string
    */
    'table_prefix' => 'shopwire_',

    /*
    |--------------------------------------------------------------------------
    | cache_prefix
    |--------------------------------------------------------------------------
    | Prefix to add to cache keys
    */
    'cache_prefix' => 'shopwire_',

    /*
    |--------------------------------------------------------------------------
    | product_model
    |--------------------------------------------------------------------------
    | The model to use for products
    */
    'product_model' => Product::class,

    /*
    |--------------------------------------------------------------------------
    | currency
    |--------------------------------------------------------------------------
    | The currency to use during checkout and Shopwire::money() helper
    */
    'currency' => [
        'code' => 'EUR',
        'symbol' => '€ ',
        'decimals' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | product_columns
    |--------------------------------------------------------------------------
    | Product columns used for checkout
    */
    'product_columns' => [
        'id' => 'id',
        'price' => 'price',
        'vat' => 'vat_id',    // Must match id from shopwire_vats table
        'stock' => 'stock',   // Will decrease after payment is successful 
        'weight' => 'weight', // Weight in grams, used to calculate shipping
    ],

];
