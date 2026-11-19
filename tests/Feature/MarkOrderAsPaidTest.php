<?php

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use NickDeKruijk\Shopwire\Controllers\PaymentController;
use NickDeKruijk\Shopwire\Events\OrderPaid;
use NickDeKruijk\Shopwire\Mail\OrderConfirmation;
use NickDeKruijk\Shopwire\Mail\OrderCreated;
use NickDeKruijk\Shopwire\Models\Order;
use NickDeKruijk\Shopwire\Tests\Fixtures\FakePaymentProvider;
use NickDeKruijk\Shopwire\Tests\Fixtures\ThrowingMailable;

function makeOrder(array $attributes = []): Order
{
    $order = new Order;
    $order->forceFill(array_merge([
        'paid' => false,
        'payment_id' => 'tr_' . uniqid(),
        'amount' => 10,
        'customer' => ['email' => 'klant@example.com', 'firstname' => 'Jan', 'lastname' => 'Jansen'],
        'products' => [],
    ], $attributes))->save();

    return $order;
}

beforeEach(function () {
    FakePaymentProvider::$paid = true;
    Mail::fake();
    Event::fake([OrderPaid::class]);
});

it('marks the order as paid and sends both mails via the webhook', function () {
    $order = makeOrder();

    $this->post('/shopwire/payment/webhook', ['id' => $order->payment_id])->assertOk();

    expect($order->fresh()->paid)->toBeTrue();
    Mail::assertSent(OrderConfirmation::class, 1);
    Mail::assertSent(OrderCreated::class, 1);
    Event::assertDispatchedTimes(OrderPaid::class, 1);
});

it('handles a paid order only once when the webhook is called repeatedly', function () {
    $order = makeOrder();

    $this->post('/shopwire/payment/webhook', ['id' => $order->payment_id]);
    $this->post('/shopwire/payment/webhook', ['id' => $order->payment_id]);

    Mail::assertSentCount(2);
    Event::assertDispatchedTimes(OrderPaid::class, 1);
});

it('does nothing when another request already marked a stale order instance as paid', function () {
    $order = makeOrder();

    // Simulate the race: the verify request loaded the order, then the webhook marked it paid
    Order::whereKey($order->id)->update(['paid' => true]);

    $method = new ReflectionMethod(PaymentController::class, 'markOrderAsPaid');
    $method->invoke(new PaymentController, $order);

    Mail::assertNothingSent();
    Event::assertNotDispatched(OrderPaid::class);
});

it('does not mark the order as paid when the payment failed', function () {
    FakePaymentProvider::$paid = false;
    $order = makeOrder();

    $this->post('/shopwire/payment/webhook', ['id' => $order->payment_id]);

    expect($order->fresh()->paid)->toBeFalse();
    Mail::assertNothingSent();
    Event::assertNotDispatched(OrderPaid::class);
});

it('keeps the order paid and dispatches the event when sending mail fails', function () {
    config(['shopwire.mailable_customer' => ThrowingMailable::class]);
    $order = makeOrder();

    $this->post('/shopwire/payment/webhook', ['id' => $order->payment_id])->assertOk();

    expect($order->fresh()->paid)->toBeTrue();
    Event::assertDispatchedTimes(OrderPaid::class, 1);
});

it('marks the order as paid on the verify redirect', function () {
    $order = makeOrder();

    $this->withSession(['shopwire_session' => ['order_id' => $order->id]])
        ->get('/shopwire/payment/verify')
        ->assertRedirect('/checkout/thankyou');

    expect($order->fresh()->paid)->toBeTrue();
    Event::assertDispatchedTimes(OrderPaid::class, 1);
});
