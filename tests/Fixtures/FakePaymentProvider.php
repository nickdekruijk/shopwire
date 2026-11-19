<?php

namespace NickDeKruijk\Shopwire\Tests\Fixtures;

use NickDeKruijk\Shopwire\Resources\Payment;
use NickDeKruijk\Shopwire\Resources\PaymentProvider;

class FakePaymentProvider extends PaymentProvider
{
    public static bool $paid = true;

    public function payment($payment_id)
    {
        $payment = new Payment;
        $payment->id = $payment_id;
        $payment->paid = static::$paid;
        $payment->status = static::$paid ? 'paid' : 'failed';
        return $payment;
    }

    public function create(array $options)
    {
        $payment = new Payment;
        $payment->id = 'tr_test';
        $payment->checkoutUrl = 'https://example.com/pay';
        return $payment;
    }

    public function methods()
    {
        // Same shape as the Mollie provider returns
        return ['ideal' => ['id' => 'ideal', 'description' => 'iDEAL']];
    }
}
