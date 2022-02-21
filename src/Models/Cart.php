<?php

namespace NickDeKruijk\Shopwire\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('shopwire.table_prefix') . 'carts';
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
