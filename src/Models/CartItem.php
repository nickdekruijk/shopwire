<?php

namespace NickDeKruijk\Shopwire\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $casts = [
        'quantity' => 'decimal:5',
        // 'price' => 'decimal:2',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('shopwire.table_prefix') . 'cart_items';
    }

    public function product()
    {
        return $this->belongsTo(config('shopwire.product_model'));
    }

    public function getPriceAttribute($value)
    {
        if (config('shopwire.product_option_model') && $this->option && config('shopwire.product_option_columns.price')) {
            $price = ['price' => $this->option[config('shopwire.product_option_columns.price')] ?: $this->product[config('shopwire.product_columns.price')]];
        } else {
            $price = ['price' => $this->product[config('shopwire.product_columns.price')]];
        }

        $price['vat_included'] = $this->product->vat->included;
        $price['vat_rate'] = $this->product->vat->rate;

        if ($price['vat_included']) {
            $price['price_including_vat'] = $price['price'];
            $price['price_excluding_vat'] = round($price['price'] / ($price['vat_rate'] / 100 + 1), 2);
            $price['price_vat'] = $price['price_including_vat'] - $price['price_excluding_vat'];
        } else {
            $price['price_including_vat'] = round($price['price'] * ($price['vat_rate'] / 100 + 1), 2);
            $price['price_excluding_vat'] = $price['price'];
            $price['price_vat'] = $price['price_including_vat'] - $price['price_excluding_vat'];
        }

        return (object) $price;
    }
}
