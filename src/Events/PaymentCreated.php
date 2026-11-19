<?php

namespace NickDeKruijk\Shopwire\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NickDeKruijk\Shopwire\Models\Order;
use NickDeKruijk\Shopwire\Resources\Payment;

/**
 * Dispatched when checkout created a payment and is about to redirect the
 * customer to the payment provider. This can happen more than once for the
 * same order, for example when the customer retries after a failed payment.
 */
class PaymentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order, public Payment $payment)
    {
    }
}
