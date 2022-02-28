<?php

namespace NickDeKruijk\Shopwire\Models;

use Countries;
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

    public function getCustomerSortedAttribute($value)
    {
        $values = [
            'email' => $this->customer['email'],
        ];
        $group_status = [];

        // First check if there are any groups
        foreach (config('shopwire.checkout_form') as $column => $attributes) {
            if (isset($attributes['toggle_group'])) {
                $group_status[$attributes['toggle_group']] = $this->customer[$column] ?? false;
            }
        }

        // Then loop over the customer data and add it to the array if the group is active
        foreach (config('shopwire.checkout_form') as $column => $attributes) {
            if (empty($attributes['group']) || $group_status[$attributes['group']]) {
                if (str_contains($column, 'country') && isset($this->customer[$column]) && Countries::has($this->customer[$column], app()->getLocale())) {
                    // Format country to current locale if it's a country
                    $values[$column] = Countries::getOne($this->customer[$column], app()->getLocale());
                } else {
                    $values[$column] = $this->customer[$column] ?? '';
                }
            }
        }

        // Done, return it
        return $values;
    }
}
