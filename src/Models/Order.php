<?php

namespace NickDeKruijk\Shopwire\Models;

use Countries;
use Illuminate\Database\Eloquent\Model;
use NickDeKruijk\Shopwire\Shopwire;
use NickDeKruijk\Shopwire\Support\Money;
use NickDeKruijk\Shopwire\Support\Stock;

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

    /**
     * Decrease the stock of the ordered products, or of the product option
     * when the option has its own stock column. A null stock value is treated
     * as unlimited and left untouched. Called after payment when
     * shopwire.decrease_stock is enabled.
     *
     * @return void
     */
    public function decreaseStock(): void
    {
        foreach ($this->lines as $line) {
            // Skip shipping, discount and other non-product lines
            if (!($line->product_id > 0) || $line->quantity <= 0) {
                continue;
            }

            // The stock of the product option when it has its own stock column, otherwise of the product
            $target = Stock::target($line->product_id, $line->product_option_id);
            if ($target) {
                [$model, $column, $key] = $target;
                (new $model)->newQueryWithoutScopes()
                    ->whereKey($key)
                    ->whereNotNull($column)
                    ->decrement($column, $line->quantity);
            }
        }
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
            $vat += Money::multiply(Money::toCents($product['price']['price_including_vat']), $product['quantity'])
                - Money::multiply(Money::toCents($product['price']['price_excluding_vat']), $product['quantity']);
        }
        return $value ?: Money::fromCents($vat);
    }

    public function getAmountExclVatAttribute($value)
    {
        $amount = 0;
        foreach ($this->products as $product) {
            $amount += Money::multiply(Money::toCents($product['price']['price_excluding_vat']), $product['quantity']);
        }
        return $value ?: Money::fromCents($amount);
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
