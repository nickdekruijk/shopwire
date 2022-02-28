<?php

namespace NickDeKruijk\Shopwire\Models;

use Illuminate\Database\Eloquent\Model;
use NickDeKruijk\Shopwire\Shopwire;

class Order extends Model
{
    protected $casts = [
        'paid' => 'boolean',
        'customer' => 'array',
        'products' => 'array',
        'amount' => 'decimal:2',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('shopwire.table_prefix') . 'orders';
    }

    public function user()
    {
        return $this->belongsTo(Shopwire::auth()->getProvider()->getModel());
    }

    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    public function getQuarterAttribute($value)
    {
        $quarter = $this->created_at->format('Y') . '-' . (ceil($this->created_at->format('m') / 3));
        return $value ?: $quarter;
    }

    public function getAmountVatAttribute($value)
    {
        $vat = 0;
        foreach ($this->products as $product) {
            $vat += ($product['price']['price_including_vat'] - $product['price']['price_excluding_vat']) * $product['quantity'];
        }
        return $value ?: $vat;
    }
    public function getAmountExclVatAttribute($value)
    {
        $vat = 0;
        foreach ($this->products as $product) {
            $vat += $product['price']['price_excluding_vat'] * $product['quantity'];
        }
        return $value ?: $vat;
    }
}
