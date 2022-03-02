# Shopwire
A simple, easy to implement shopping cart and checkout package for Laravel 9 using Livewire.

## Installation

To install run the following command:

```bash
composer require nickdekruijk/shopwire
```

Afterwards run the migration command:
```bash
php artisan migrate
```

## Prepare your Product model
Add ShopwireProduct trait:
```php
use NickDeKruijk\Shopwire\Traits\ShopwireProduct;
class Product extends Model
{
    use ShopwireProduct;
```
If your model is different from the default (App\Models\Product), you can change the model name in the config file.

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

### Admin package integration
To manage products/vat/orders etc with the [nickdekruijk/admin](https://github.com/nickdekruijk/admin) package add the modules as described in [this example file](https://github.com/nickdekruijk/webshop/blob/master/src/examples/admin.md) to your `config/admin.php` file.

### Some seeds with data to start with
Dutch VAT
`php artisan db:seed --class=NickDeKruijk\\Shopwire\\Seeds\\VatDutch`

Dutch Shipment methods
`php artisan db:seed --class=NickDeKruijk\\Shopwire\\Seeds\\ShippingDutch`

## License
Shopwire is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

More later...
