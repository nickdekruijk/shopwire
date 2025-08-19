<?php

namespace NickDeKruijk\Shopwire;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use NickDeKruijk\Shopwire\Controllers\CartController;
use NickDeKruijk\Shopwire\Models\Discount;
use NickDeKruijk\Shopwire\Models\Order;

class Shopwire
{
    /**
     * Return total count of items in the cart.
     *
     * @param  boolean $unique When true return total amount of unique items instead of adding all quantities together.
     * @return integer
     */
    public static function count($unique = false)
    {
        return CartController::count($unique);
    }

    /**
     * Return a formatted representation of an amount with currency symbol and decimals.
     *
     * @param  float   $amount
     * @param  string  $currency
     * @param  integer $decimals
     * @return string
     */
    public static function money($amount)
    {
        return config('shopwire.currency.symbol') . number_format($amount, config('shopwire.currency.decimals'), trans('shopwire::cart.decimal_point'), trans('shopwire::cart.thousands_seperator'));
    }

    /**
     * Write a log entry to the shopwire log channel.
     *
     * @param string $type
     * @param string $message
     * @return void
     */
    public static function log($type, $message)
    {
        $message = "\t" . request()->ip() . "\t" . $message;
        Log::channel('shopwire')->$type($message);
    }

    /**
     * Return auth object with the correct guard.
     *
     * @return mixed
     */
    public static function auth(): Guard|StatefulGuard
    {
        return Auth::guard(config('shopwire.auth_guard'));
    }

    /**
     * Get or put a value in the session inside the shopwire.session_array.
     *
     * @param array|string $key
     * @param mixed $default
     * @return mixed
     */
    public static function session(array|string $key, mixed $default = null): mixed
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $key[config('shopwire.session_array') . '.' . $k] = $v;
                unset($key[$k]);
            }
            return session($key);
        } else {
            return session(config('shopwire.session_array') . '.' . $key, $default);
        }
    }

    /**
     * Return the current order or create new one.
     *
     * @param string|null $payment_id
     * @return Order
     */
    public static function order(string|null $payment_id = null): Order
    {
        $model = config('shopwire.order_model');
        if ($payment_id) {
            return $model::where('payment_id', $payment_id)->firstOrFail();
        } elseif (self::session('order_id')) {
            $order = $model::find(self::session('order_id'));
            if ($order) {
                return $order;
            }
        }
        $order = new $model;
        $order->save();
        Shopwire::log('notice', 'New order: ' . $order->id);
        self::session(['order_id' => $order->id]);
        return $order;
    }

    /**
     * Check if the shop has active discounts
     *
     * @return boolean
     */
    public static function hasDiscounts(): bool
    {
        return Discount::active()->count() > 0;
    }
}
