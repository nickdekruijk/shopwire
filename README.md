[![Latest Stable Version](https://poser.pugx.org/nickdekruijk/shopwire/v/stable)](https://packagist.org/packages/nickdekruijk/shopwire)
[![Latest Unstable Version](https://poser.pugx.org/nickdekruijk/shopwire/v/unstable)](https://packagist.org/packages/nickdekruijk/shopwire)
[![Monthly Downloads](https://poser.pugx.org/nickdekruijk/shopwire/d/monthly)](https://packagist.org/packages/nickdekruijk/shopwire)
[![Total Downloads](https://poser.pugx.org/nickdekruijk/shopwire/downloads)](https://packagist.org/packages/nickdekruijk/shopwire)
[![License](https://poser.pugx.org/nickdekruijk/shopwire/license)](https://packagist.org/packages/nickdekruijk/shopwire)

# Shopwire
A simple, easy to implement shopping cart and checkout package for Laravel 9 using Livewire.

## Installation
To install run the following command:

`composer require nickdekruijk/shopwire`

***Before your run php artisan migrate make sure your Product model is properly setup.***

Publish the config file with:

`php artisan vendor:publish --tag=config --provider="NickDeKruijk\Shopwire\ShopwireServiceProvider"`

## Prepare your Product model
Add ShopwireProduct trait:
```php
use NickDeKruijk\Shopwire\Traits\ShopwireProduct;
class Product extends Model
{
    use ShopwireProduct;
```
If your model is different from the default (App\Models\Product), you can change the model name in the config file.

Afterwards run the migration command:
`php artisan migrate`

## Environment
To enable automatic country detection, add the following to your .env file:
```
LOCATION_TESTING=false
```
By defaults Shopwire uses Mollie as payment provider, set your Mollie API key in the .env file:
```
MOLLIE_KEY=test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

## Webhooks and Csrf
To make the payment provider webhooks work you may need to update the `$except` array in `app\Http\Middleware\VerifyCsrfToken.php`
```php
    protected $except = [
        'shopwire/payment/webhook',
    ];
```

## Logging
You need to make a logging channel called shopwire, add something like this to `config/logging.php`:
```php
        'shopwire' => [
            'driver' => 'single',
            'path' => storage_path('logs/shopwire.log'),
            'level' => 'debug',
        ],
```

### Admin package integration
To manage products/vat/orders etc with the [nickdekruijk/admin](https://github.com/nickdekruijk/admin) package add the modules as described in [this example file](https://github.com/nickdekruijk/webshop/blob/master/src/examples/admin.md) to your `config/admin.php` file.

### Some seeds with data to start with
Dutch VAT
`php artisan db:seed --class=NickDeKruijk\\Shopwire\\Seeds\\VatDutch`

Dutch Shipment methods
`php artisan db:seed --class=NickDeKruijk\\Shopwire\\Seeds\\ShippingDutch`

<!-- Dutch Discounts
`php artisan db:seed --class=NickDeKruijk\\Shopwire\\Seeds\\DiscountsDutch` -->

## License
Shopwire is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
