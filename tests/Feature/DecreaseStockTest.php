<?php

use Illuminate\Support\Facades\Mail;
use NickDeKruijk\Shopwire\Models\Order;
use NickDeKruijk\Shopwire\Models\OrderLine;
use NickDeKruijk\Shopwire\Tests\Fixtures\FakePaymentProvider;
use NickDeKruijk\Shopwire\Tests\Fixtures\Product;

function orderWithLines(array $lines): Order
{
    $order = new Order;
    $order->forceFill([
        'paid' => false,
        'payment_id' => 'tr_' . uniqid(),
        'amount' => 10,
        'customer' => ['email' => 'klant@example.com', 'firstname' => 'Jan', 'lastname' => 'Jansen'],
        'products' => [],
    ])->save();

    foreach ($lines as $line) {
        $orderLine = new OrderLine;
        $orderLine->forceFill(array_merge(['order_id' => $order->id, 'title' => 'Line', 'price' => 5], $line))->save();
    }

    return $order;
}

beforeEach(function () {
    FakePaymentProvider::$paid = true;
    Mail::fake();
});

it('leaves stock untouched by default', function () {
    $product = Product::create(['title' => 'Shirt', 'stock' => 10]);
    $order = orderWithLines([['product_id' => $product->id, 'quantity' => 3]]);

    $this->post('/shopwire/payment/webhook', ['id' => $order->payment_id]);

    expect((float) $product->fresh()->stock)->toBe(10.0);
});

it('decreases stock once after payment when enabled', function () {
    config(['shopwire.decrease_stock' => true]);
    $product = Product::create(['title' => 'Shirt', 'stock' => 10]);
    $order = orderWithLines([['product_id' => $product->id, 'quantity' => 3]]);

    $this->post('/shopwire/payment/webhook', ['id' => $order->payment_id]);
    $this->post('/shopwire/payment/webhook', ['id' => $order->payment_id]);

    expect((float) $product->fresh()->stock)->toBe(7.0);
});

it('treats null stock as unlimited and skips non-product lines', function () {
    config(['shopwire.decrease_stock' => true]);
    $unlimited = Product::create(['title' => 'Download', 'stock' => null]);
    $order = orderWithLines([
        ['product_id' => $unlimited->id, 'quantity' => 2],
        ['product_id' => null, 'quantity' => 1, 'title' => 'Verzendkosten'],
    ]);

    $this->post('/shopwire/payment/webhook', ['id' => $order->payment_id]);

    expect($unlimited->fresh()->stock)->toBeNull();
    expect($order->fresh()->paid)->toBeTrue();
});

it('does not decrease stock when payment failed', function () {
    config(['shopwire.decrease_stock' => true]);
    FakePaymentProvider::$paid = false;
    $product = Product::create(['title' => 'Shirt', 'stock' => 10]);
    $order = orderWithLines([['product_id' => $product->id, 'quantity' => 3]]);

    $this->post('/shopwire/payment/webhook', ['id' => $order->payment_id]);

    expect((float) $product->fresh()->stock)->toBe(10.0);
});
