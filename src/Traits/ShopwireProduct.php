<?php

namespace NickDeKruijk\Shopwire\Traits;

use NickDeKruijk\Shopwire\Models\Vat;
use NickDeKruijk\Shopwire\Support\Money;

trait ShopwireProduct
{
    public function shopwire_vat()
    {
        return $this->belongsTo(Vat::class, 'vat_id');
    }

    public function getShopwirePriceTaxFreeAttribute()
    {
        $price = $this->shopwire_price;
        $price->vat_included = false;
        $price->vat_rate = 0;
        $price->price_including_vat = $price->price_excluding_vat;
        $price->price_vat = 0;
        return $price;
    }

    public function getShopwirePriceAttribute()
    {
        if (config('shopwire.product_option_model') && $this->option && config('shopwire.product_option_columns.price')) {
            $price = ['price' => $this->option[config('shopwire.product_option_columns.price')] ?: $this[config('shopwire.product_columns.price')]];
        } else {
            $price = ['price' => $this[config('shopwire.product_columns.price')]];
        }

        $price['vat_included'] = $this->shopwire_vat->included;
        $price['vat_rate'] = $this->shopwire_vat->rate;

        // Calculate in whole cents to avoid floating point rounding errors
        $cents = Money::toCents($price['price']);

        if ($price['vat_included']) {
            $including = $cents;
            $excluding = Money::excludingVat($cents, $price['vat_rate']);
            $price['price_including_vat'] = $price['price'];
            $price['price_excluding_vat'] = Money::fromCents($excluding);
        } else {
            $including = Money::includingVat($cents, $price['vat_rate']);
            $excluding = $cents;
            $price['price_including_vat'] = Money::fromCents($including);
            $price['price_excluding_vat'] = $price['price'];
        }
        $price['price_vat'] = Money::fromCents($including - $excluding);

        return (object) $price;
    }
}
