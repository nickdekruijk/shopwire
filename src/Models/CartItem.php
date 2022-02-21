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

    // public function option()
    // {
    //     return $this->belongsTo(config('shopwire.product_option_model'), 'product_option_id', config('shopwire.product_option_columns.product_id'));
    // }

    // public function getWeightAttribute($value)
    // {
    //     if (config('shopwire.product_option_model') && $this->option && config('shopwire.product_option_columns.weight')) {
    //         return $this->option[config('shopwire.product_option_columns.weight')] ?: $this->product[config('shopwire.product_columns.weight')];
    //     } else {
    //         return $this->product[config('shopwire.product_columns.weight')];
    //     }
    // }

    // public function getTitleAttribute($value)
    // {
    //     return $this->product[config('shopwire.product_columns.title')] . (config('shopwire.product_option_model') && $this->option && $this->option[config('shopwire.product_option_columns.title')] ? ' (' . $this->option[config('shopwire.product_option_columns.title')] . ')' : '');
    // }

    // public function getDescriptionAttribute($value)
    // {
    //     return $this->product[config('shopwire.product_columns.description')] . (config('shopwire.product_option_model') && $this->option && $this->option[config('shopwire.product_option_columns.description')] ? ' (' . $this->option[config('shopwire.product_option_columns.description')] . ')' : '');
    // }
}
