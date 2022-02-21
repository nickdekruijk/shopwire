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
Add Vat relationship:
```php
use NickDeKruijk\Shopwire\Models\Vat;

public function vat()
{
    return $this->belongsTo(Vat::class);
}
```

## Some seeds with data to start with
Dutch VAT
`php artisan db:seed --class=NickDeKruijk\\Shopwire\\Seeds\\VatDutch`

## License
Shopwire is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

More later...
