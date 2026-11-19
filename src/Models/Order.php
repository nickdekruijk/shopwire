<?php

namespace NickDeKruijk\Shopwire\Models;

use Countries;
use Illuminate\Database\Eloquent\Model;
use NickDeKruijk\Shopwire\Shopwire;
use NickDeKruijk\Shopwire\Support\Money;

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
     * Decrease the stock of the ordered products (and product options when
     * configured). Products with a null stock value are treated as unlimited
     * and left untouched. Called after payment when shopwire.decrease_stock
     * is enabled.
     *
     * @return void
     */
    public function decreaseStock(): void
    {
        $productModel = config('shopwire.product_model');
        $productColumn = config('shopwire.product_columns.stock');
        $optionModel = config('shopwire.product_option_model');
        $optionColumn = config('shopwire.product_option_columns.stock');

        foreach ($this->lines as $line) {
            // Skip shipping, discount and other non-product lines
            if (!($line->product_id > 0) || $line->quantity <= 0) {
                continue;
            }

            if ($line->product_option_id && $optionModel && $optionColumn) {
                (new $optionModel)->newQueryWithoutScopes()
                    ->whereKey($line->product_option_id)
                    ->whereNotNull($optionColumn)
                    ->decrement($optionColumn, $line->quantity);
            } elseif ($productModel && $productColumn) {
                (new $productModel)->newQueryWithoutScopes()
                    ->whereKey($line->product_id)
                    ->whereNotNull($productColumn)
                    ->decrement($productColumn, $line->quantity);
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
