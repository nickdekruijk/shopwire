<?php

use NickDeKruijk\Shopwire\Controllers\CartController;
use NickDeKruijk\Shopwire\Models\Cart;
use NickDeKruijk\Shopwire\Models\CartItem;
use NickDeKruijk\Shopwire\Models\Order;
use NickDeKruijk\Shopwire\Models\Vat;
use NickDeKruijk\Shopwire\Shopwire;
use NickDeKruijk\Shopwire\Tests\Fixtures\Product;

function cartWith(array $products): void
{
    $cart = new Cart;
    $cart->session_id = 'test';
    $cart->save();
    Shopwire::session(['cart_id' => $cart->id]);

    foreach ($products as [$product, $quantity]) {
        $item = new CartItem;
        $item->cart_id = $cart->id;
        $item->product_id = $product->id;
        $item->quantity = $quantity;
        $item->save();
    }
}

function vat(float $rate, bool $included): Vat
{
    $vat = new Vat;
    $vat->rate = $rate;
    $vat->included = $included;
    $vat->save();

    return $vat;
}

it('sums cart totals exactly', function () {
    $vat = vat(21, true);
    $sticker = Product::create(['title' => 'Sticker', 'price' => '0.10', 'vat_id' => $vat->id]);
    cartWith([[$sticker, 3]]);

    $statistics = CartController::getItems()->statistics;

    // With float arithmetic this used to be 0.30000000000000004
    expect($statistics['amount_including_vat'])->toBe(0.3);
    expect($statistics['amount_excluding_vat'])->toBe(0.24);
});

it('adds up VAT of all products with the same rate', function () {
    $vat = vat(21, true);
    $shirt = Product::create(['title' => 'Shirt', 'price' => '24.20', 'vat_id' => $vat->id]);
    $poster = Product::create(['title' => 'Poster', 'price' => '12.10', 'vat_id' => $vat->id]);
    cartWith([[$shirt, 1], [$poster, 2]]);

    $statistics = CartController::getItems()->statistics;

    expect($statistics['amount_including_vat'])->toBe(48.4)
        ->and($statistics['amount_excluding_vat'])->toBe(40.0)
        ->and(array_values($statistics['amount_vat']))->toBe([8.4]);
});

it('calculates product prices excluding VAT in cents', function () {
    $vat = vat(21, false);
    $product = Product::create(['title' => 'Workshop', 'price' => '100.00', 'vat_id' => $vat->id]);

    $price = $product->shopwire_price;

    expect($price->price_including_vat)->toBe(121.0)
        ->and($price->price_vat)->toBe(21.0)
        ->and((float) $price->price_excluding_vat)->toBe(100.0);
});

it('calculates order VAT totals exactly', function () {
    $order = new Order;
    $order->products = [
        ['quantity' => 3, 'price' => ['price_including_vat' => 0.1, 'price_excluding_vat' => 0.08]],
        ['quantity' => 1, 'price' => ['price_including_vat' => 0.2, 'price_excluding_vat' => 0.17]],
    ];

    expect($order->amount_vat)->toBe(0.09)
        ->and($order->amount_excl_vat)->toBe(0.41);
});
