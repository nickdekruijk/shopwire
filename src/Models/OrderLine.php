<?php

namespace NickDeKruijk\Shopwire\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLine extends Model
{
    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('shopwire.table_prefix') . 'order_lines';
    }

    public function product()
    {
        return $this->belongsTo(config('shopwire.product_model'));
    }

    public function product_option()
    {
        return $this->belongsTo(config('shopwire.product_option_model'));
    }
}
