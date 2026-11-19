<?php

namespace NickDeKruijk\Shopwire\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    protected $table = 'product_options';
    protected $guarded = [];
    protected $casts = [
        'active' => 'boolean',
    ];
}
