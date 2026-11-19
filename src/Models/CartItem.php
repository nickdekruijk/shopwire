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

    public function option()
    {
        return $this->belongsTo(config('shopwire.product_option_model'), 'product_option_id');
    }

    /**
     * Return the product option of this item, or null when the item has none
     * or when product options aren't configured.
     */
    public function selectedOption()
    {
        return config('shopwire.product_option_model') && $this->product_option_id ? $this->option : null;
    }

    /**
     * Return a value of the product option, or null when the item has no
     * option or the column isn't configured.
     */
    private function optionValue(string $column)
    {
        $column = config('shopwire.product_option_columns.' . $column);
        $option = $this->selectedOption();

        return $option && $column ? $option[$column] : null;
    }

    /**
     * The product title, followed by the option title: "Product (Option)".
     */
    public function getTitleAttribute($value)
    {
        $option_title = $this->optionValue('title');

        return $this->product->title . ($option_title ? ' (' . $option_title . ')' : '');
    }

    /**
     * The weight of the option, or of the product when the option has none.
     */
    public function getWeightAttribute($value)
    {
        return $this->optionValue('weight') ?: $this->product->weight;
    }

    /**
     * The price details of this item, with the price of the option when it has one.
     *
     * @param  boolean $taxfree Return the price without VAT, for taxfree countries
     * @return object
     */
    public function price(bool $taxfree = false): object
    {
        $option = $this->selectedOption();
        if ($option) {
            return $this->product->shopwirePrice($option, $taxfree);
        }

        // Without an option use the accessors, a product model may have its own
        return $taxfree ? $this->product->shopwire_price_taxfree : $this->product->shopwire_price;
    }
}
