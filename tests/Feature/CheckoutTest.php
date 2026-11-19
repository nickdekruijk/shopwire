<?php

use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use NickDeKruijk\Shopwire\Events\PaymentCreated;
use NickDeKruijk\Shopwire\Livewire\Checkout;
use NickDeKruijk\Shopwire\Models\Cart;
use NickDeKruijk\Shopwire\Models\CartItem;
use NickDeKruijk\Shopwire\Models\Discount;
use NickDeKruijk\Shopwire\Models\Order;
use NickDeKruijk\Shopwire\Models\ShippingRate;
use NickDeKruijk\Shopwire\Models\Vat;
use NickDeKruijk\Shopwire\Shopwire;
use NickDeKruijk\Shopwire\Tests\Fixtures\Product;

beforeEach(function () {
    $high = new Vat;
    $high->forceFill(['rate' => 21, 'included' => true])->save();
    $low = new Vat;
    $low->forceFill(['rate' => 9, 'included' => true])->save();

    $shirt = Product::create(['title' => 'Shirt', 'price' => '121.00', 'vat_id' => $high->id]);
    $book = Product::create(['title' => 'Boek', 'price' => '109.00', 'vat_id' => $low->id]);

    (new ShippingRate)->forceFill(['title' => 'Pakket', 'rate' => '6.05', 'vat_included' => true])->save();
    (new Discount)->forceFill(['title' => 'Korting', 'active' => true, 'discount_abs' => 23])->save();

    $cart = new Cart;
    $cart->forceFill(['session_id' => 'test', 'country_code' => 'NL'])->save();
    foreach ([$shirt, $book] as $product) {
        (new CartItem)->forceFill(['cart_id' => $cart->id, 'product_id' => $product->id, 'quantity' => 1])->save();
    }

    Shopwire::session([
        'cart_id' => $cart->id,
        'checkout_account' => 'guest',
        'checkout_form' => [
            'email' => 'klant@example.com',
            'firstname' => 'Jan',
            'lastname' => 'Jansen',
            'address' => 'Dorpsstraat 1',
            'postcode' => '1234 AB',
            'city' => 'Utrecht',
            'agree_with_terms' => true,
        ],
    ]);
});

it('dispatches PaymentCreated and redirects to the payment provider', function () {
    Event::fake([PaymentCreated::class]);

    Livewire::test(Checkout::class)
        ->call('gotoPayment')
        ->assertHasNoErrors()
        ->assertRedirect('https://example.com/pay');

    $order = Order::firstOrFail();

    expect($order->payment_id)->toBe('tr_test');
    Event::assertDispatchedTimes(PaymentCreated::class, 1);
    Event::assertDispatched(PaymentCreated::class, fn(PaymentCreated $event) => $event->order->is($order) && $event->payment->id === 'tr_test');
});

it('stores the discount per VAT rate on the order', function () {
    Livewire::test(Checkout::class)->call('gotoPayment')->assertHasNoErrors();

    $discount = collect(Order::firstOrFail()->products)->firstWhere('type', 'discount');

    expect($discount['price']['vat_shares'])->toBe([
        '21.00' => ['including' => -12.1, 'excluding' => -10, 'vat' => -2.1],
        '9.00' => ['including' => -10.9, 'excluding' => -10, 'vat' => -0.9],
    ]);
});
