<?php

namespace NickDeKruijk\Shopwire\Models;

use Illuminate\Database\Eloquent\Model;

class Vat extends Model
{
    protected $casts = [
        'active' => 'boolean',
        'rate' => 'decimal:2',
        'included' => 'boolean',
        'high_rate' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('shopwire.table_prefix') . 'vats';
    }

    public function getDescriptionAttribute($value)
    {
        return $value ?: ($this->included ? 'Including' : 'Excluding') . ' ' . $this->rate + 0 . '%';
    }
}
