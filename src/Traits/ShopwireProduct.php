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
        return $this->shopwirePrice($this->shopwireOwnOption(), true);
    }

    public function getShopwirePriceAttribute()
    {
        return $this->shopwirePrice($this->shopwireOwnOption());
    }

    /**
     * Return the price details of this product, or of one of its options.
     *
     * The option is passed in instead of read from the product, because cart
     * items of the same product share one product instance: an option stored
     * on the product would leak from one cart item to the next.
     *
     * @param  mixed $option The product option, its price is used when it has one
     * @param  boolean $taxfree Return the price without VAT, for taxfree countries
     * @return object
     */
    public function shopwirePrice($option = null, bool $taxfree = false): object
    {
        $option_price = config('shopwire.product_option_columns.price');
        $price = ['price' => ($option && $option_price ? $option[$option_price] : null) ?: $this[config('shopwire.product_columns.price')]];

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

        if ($taxfree) {
            $price['vat_included'] = false;
            $price['vat_rate'] = 0;
            $price['price_including_vat'] = $price['price_excluding_vat'];
            $price['price_vat'] = 0;
        }

        return (object) $price;
    }

    /**
     * Older integrations put the chosen option on the product itself, as an
     * option attribute or relation. Keep using it for shopwire_price.
     */
    private function shopwireOwnOption()
    {
        if (!config('shopwire.product_option_model')) {
            return null;
        }

        return $this->isRelation('option') || $this->hasAttribute('option') ? $this->option : null;
    }
}
