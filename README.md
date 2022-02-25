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

## Some seeds with data to start with
Dutch VAT
`php artisan db:seed --class=NickDeKruijk\\Shopwire\\Seeds\\VatDutch`

Dutch Shipment methods
`php artisan db:seed --class=NickDeKruijk\\Shopwire\\Seeds\\ShippingDutch`

## License
Shopwire is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

More later...
