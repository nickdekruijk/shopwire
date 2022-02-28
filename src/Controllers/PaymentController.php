<?php

namespace NickDeKruijk\Shopwire\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use NickDeKruijk\Shopwire\Controllers\CartController;
use NickDeKruijk\Shopwire\Models\Order;
use NickDeKruijk\Shopwire\Resources\Payment;
use NickDeKruijk\Shopwire\Resources\PaymentProvider;
use NickDeKruijk\Shopwire\Shopwire;

class PaymentController extends Controller
{
    /**
     * Return an instance of the payment provider set in config
     *
     * @return PaymentProvider
     */
    public static function provider()
    {
        $provider = config('shopwire.payment_provider');
        return new $provider;
    }

    /**
     * Get payment details from provider
     *
     * @param string $id
     * @return Payment
     */
    public static function payment($payment_id)
    {
        return self::provider()->payment($payment_id);
    }

    /**
     * Create payment with provider
     *
     * @param array $options
     * @return Payment
     */
    public static function create(array $options)
    {
        return self::provider()->create($options);
    }

    /**
     * Return available payment methods
     *
     * @return array
     */
    public static function methods()
    {
        return self::provider()->methods();
    }

    /**
     * Mark the order as paid and send confirmation email
     *
     * @param Order $order
     * @return void
     */
    private function markOrderAsPaid(Order $order)
    {
        if (!$order->paid) {
            Shopwire::log('info', 'Verified payment: ' . $order->payment_id);
            Mail::send((new (config('shopwire.mailable_customer'))($order)));
            Mail::send((new (config('shopwire.mailable_owner'))($order)));
            Shopwire::log('info', 'Mail sent: ' . $mailable . ' ' . $order->customer['email']);
            $order->paid = true;
            $order->save();
        }
    }

    /**
     * Verify if the payment is valid and handle accordingly
     * This is called by the shopwire-payment-verify route
     *
     * @return void
     */
    public function verify()
    {
        $order = Shopwire::order();
        $payment = self::payment($order->payment_id);
        if ($payment->paid) {
            $this->markOrderAsPaid($order);
            if (!config('app.debug')) {
                CartController::empty();
            }
            Shopwire::session(['order_id' => null]);
            return redirect(config('shopwire.checkout_redirect_paid'));
        } else {
            Shopwire::log('notice', 'Failed payment: ' . $order->payment_id . ' (' . $payment->status . ')');
            Shopwire::session(['payment_error' => __('shopwire::cart.payment_' . $payment->status)]);
            return redirect(Shopwire::session('checkout_url'));
        }
    }

    /**
     * Webhook for payment provider
     * This is called by the shopwire-payment-webhook route
     *
     * @param Request $request
     * @return void
     */
    public function webhook(Request $request)
    {
        Shopwire::log('info', 'webhookPayment: ' . $request->id);
        abort_if(!$request->id, 404);
        $order = Shopwire::order($request->id);
        $payment = PaymentController::payment($order->payment_id);
        if ($payment->paid) {
            $this->markOrderAsPaid($order);
        } else {
            Shopwire::log('notice', 'Failed payment: ' . $order->payment_id . ' (' . $payment->status . ')');
        }
    }
}
