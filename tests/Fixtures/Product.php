<?php

namespace NickDeKruijk\Shopwire\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use NickDeKruijk\Shopwire\Traits\ShopwireProduct;

class Product extends Model
{
    use ShopwireProduct;

    protected $table = 'products';
    protected $guarded = [];
}
