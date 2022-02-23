<?php

namespace NickDeKruijk\Shopwire\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $casts = [
        'active' => 'boolean',
        'vat_included' => 'boolean',
        'rate' => 'decimal:2',
        'amount_from' => 'decimal:2',
        'amount_to' => 'decimal:2',
        'weight_from' => 'decimal:2',
        'weight_to' => 'decimal:2',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = config('shopwire.table_prefix') . 'shipping_rates';
    }

    public function scopeValid($query, $amount, $weight, $country = null)
    {
        $query->where(function ($query) use ($amount) {
            $query->whereNull('amount_from')->orWhere('amount_from', '<=', $amount);
        });
        $query->where(function ($query) use ($amount) {
            $query->whereNull('amount_to')->orWhere('amount_to', '>', $amount);
        });
        $query->where(function ($query) use ($weight) {
            $query->whereNull('weight_from')->orWhere('weight_from', '<=', $weight);
        });
        $query->where(function ($query) use ($weight) {
            $query->whereNull('weight_to')->orWhere('weight_to', '>=', $weight);
        });
        if ($country) {
            $query->where(function ($query) use ($country) {
                $query->whereNull('countries')->orWhere('countries', 'LIKE', '%' . $country . '%');
            });
            $query->where(function ($query) use ($country) {
                $query->whereNull('countries_except')->orWhere('countries_except', 'NOT LIKE', '%' . $country . '%');
            });
        }
        return $query->where('active', 1)->orderBy('rate');
    }
}
