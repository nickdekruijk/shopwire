[![Latest Stable Version](https://poser.pugx.org/nickdekruijk/shopwire/v/stable)](https://packagist.org/packages/nickdekruijk/shopwire)
[![Latest Unstable Version](https://poser.pugx.org/nickdekruijk/shopwire/v/unstable)](https://packagist.org/packages/nickdekruijk/shopwire)
[![Monthly Downloads](https://poser.pugx.org/nickdekruijk/shopwire/d/monthly)](https://packagist.org/packages/nickdekruijk/shopwire)
[![Total Downloads](https://poser.pugx.org/nickdekruijk/shopwire/downloads)](https://packagist.org/packages/nickdekruijk/shopwire)
[![License](https://poser.pugx.org/nickdekruijk/shopwire/license)](https://packagist.org/packages/nickdekruijk/shopwire)

# Shopwire
A simple, easy to implement shopping cart and checkout package for Laravel 12/13 using Livewire 3/4.

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
The payment provider webhook must be excluded from CSRF verification. In Laravel 11 and newer add this to `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'shopwire/payment/webhook',
    ]);
})
```
In older applications add `'shopwire/payment/webhook'` to the `$except` array in `app/Http/Middleware/VerifyCsrfToken.php`.

## Logging
You need to make a logging channel called shopwire, add something like this to `config/logging.php`:
```php
        'shopwire' => [
            'driver' => 'single',
            'path' => storage_path('logs/shopwire.log'),
            'level' => 'debug',
        ],
```

## Stock
Set `decrease_stock` to `true` in the config to decrease the stock column of each ordered product once the order is paid. A `null` stock value is treated as unlimited. For a [product variant](#product-variants) with its own stock column the stock of the variant is decreased instead.

Set `prevent_oversell` to `true` to stop customers from putting more in the cart than is in stock. The quantity is limited to the stock and the customer gets a message, in the add to cart component and in the checkout. The stock is checked again when the customer goes to the payment. Stock is not reserved: two customers paying at the same time can still buy the last one, so the stock can become negative.

## Product variants
Products can have options, also known as variants: for example the sizes of a shirt, each with its own stock and optionally its own price and weight. Without `product_option_model` in the config nothing changes.

Create a model and a table for the options. There is an example model and migration in `src/examples`:
```php
Schema::create('product_options', function (Blueprint $table) {
    $table->id();
    $table->boolean('active')->default(1)->index();
    $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
    $table->string('title')->nullable();
    $table->string('description')->nullable();
    $table->decimal('price', 10, 2)->nullable()->index();
    $table->decimal('stock', 8, 2)->nullable();
    $table->decimal('weight', 8, 2)->nullable();
    $table->bigInteger('sort')->nullable()->unsigned()->index();
    $table->timestamps();
});
```

Then set the model in `config/shopwire.php`. Set a column to `null` when your table does not have it:
```php
'product_option_model' => App\Models\ProductOption::class,
'product_option_columns' => [
    'product_id' => 'product_id',
    'title' => 'title',
    'description' => 'description',
    'price' => 'price',
    'stock' => 'stock',
    'weight' => 'weight',
    'active' => 'active',
    'sort' => 'sort',
],
```

The add to cart component shows a select with the active variants of the product, and a variant must be chosen before the product can be added. To show the component for one variant, pass it in:
```blade
{{-- The customer chooses a variant --}}
<livewire:shopwire-add :product="$product" />

{{-- One fixed variant, a model or an id --}}
<livewire:shopwire-add :product="$product" :option="$option" />
```

How variants work:
- The title in the cart, the order and the mails is the product title followed by the variant title: `Shirt (L)`.
- The price and weight of the variant are used. When they are empty the price and weight of the product are used.
- Two variants of the same product are separate items in the cart. The order line stores the variant in `product_option_id`.
- A variant can only be added when it belongs to the product and is active. `CartController::add()` and `update()` return `false` otherwise.
- With `product_option_columns.stock` set, each variant has its own stock for `decrease_stock` and `prevent_oversell`. Set it to `null` to keep one stock on the product, shared by its variants.
- In the checkout component an item without a variant keeps the product id as key in `$items` and `$quantity`. An item with a variant gets the key `{product id}-{variant id}`, for example `12-5`. Each item also has a `cart_item_id`, `product_id` and `product_option_id`.

If you published the views, compare `add-to-cart.blade.php` and `checkout.blade.php` with the package versions: the first has the variant select, both show the stock messages.

## Events
Shopwire dispatches these events you can listen to, for example to create invoices or start fulfilment:

| Event | When |
| --- | --- |
| `NickDeKruijk\Shopwire\Events\PaymentCreated` | Checkout created a payment and redirects the customer to the payment provider. Can happen more than once per order (e.g. retry after a failed payment). Has `$order` and `$payment`. |
| `NickDeKruijk\Shopwire\Events\OrderPaid` | The order was marked as paid. Dispatched exactly once per order, also when the verify redirect and the webhook arrive at the same time. Has `$order`. |

## Testing
```
composer install
composer test
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
