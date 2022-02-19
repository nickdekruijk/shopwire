<?php

namespace NickDeKruijk\Shopwire;

use Log;

class Shopwire
{
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
        return config('shopwire.currency.symbol') . number_format($amount, config('shopwire.currency.decimals'), trans('shopwire::cart.dec_point'), trans('shopwire::cart.thousands_sep'));
    }
}
