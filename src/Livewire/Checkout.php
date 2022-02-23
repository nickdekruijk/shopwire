<?php

namespace NickDeKruijk\Shopwire\Livewire;

use Livewire\Component;
use NickDeKruijk\Shopwire\Controllers\CartController;
use Countries;

class Checkout extends Component
{
    public $statistics;
    public $items;
    public $quantity = [];
    public $includingVat = true;
    public $show_zero = false;
    public $shipping;
    public $shipping_options;
    public $countries;
    public $form;
    public $form_columns = [];
    public $form_groups = [];

    protected $listeners = [
        'cartUpdate' => 'cartUpdate',
    ];

    public function mount()
    {
        // Get the countries and codes from the countries package
        $this->countries = Countries::getList(app()->getLocale());

        // Get stored form values from session
        $this->form = session(config('shopwire.cache_prefix') . 'checkout_form');

        // Track group toggle status
        $group_status = [];

        // Set form_columns and form_groups
        foreach (config('shopwire.checkout_form') as $column => $attributes) {

            // $attributes should be an array
            if (!is_array($attributes)) {
                $column = $attributes;
                $attributes = [];
            }

            // Set the label based on either the localized label, default label or the column name
            $attributes['label'] =
                $attributes['label_' . app()->getLocale()]
                ?? $attributes['label']
                ?? ucfirst(str_replace('_', ' ', $column));

            // Set the column type or use text as default
            $attributes['type'] = $attributes['type'] ?? 'text';

            if (isset($attributes['toggle_group'])) {
                $group_status[$attributes['toggle_group']] = $this->form[$column] ?? false;
            }

            // Check for form groups
            if (isset($attributes['group'])) {
                $this->form_groups[$attributes['group']] = $group_status[$attributes['group']] ?? false;
            } else {
                $attributes['group'] = null;
            }

            // Add the column to the form
            $this->form_columns[$column] = $attributes;
        }

        // Get the items from the cart
        $this->cartUpdate();
    }

    public function cartUpdate()
    {
        $cart = CartController::getItems();
        $this->form['country'] = $cart->cart->country_code;
        $this->shipping = $cart->cart->shipping_rate_id;
        $this->items = [];
        foreach ($cart->items as $item) {
            if ($item == 'select_shipping') {
                $this->items[] = [
                    'id' => null,
                    'title' => $item,
                    'price' => 0,
                ];
            } elseif ($this->show_zero || $item->quantity != 0) {
                $this->quantity[$item->product_id] = $item->quantity;
                $this->items[] = [
                    'id' => $item->product_id,
                    'title' => $item->title,
                    'url' => $item->product_id ? $item->product->url : null,
                    'price' => $this->includingVat ? $item->price->price_including_vat : $item->price->price_excluding_vat,
                ];
            }
        }
        $this->show_zero = true;
        $this->statistics = $cart->statistics;
        $this->shipping_options = $cart->shipping_options;
    }

    public function updatedIncludingVat()
    {
        $this->cartUpdate();
    }

    public function updatedShipping()
    {
        $cart = CartController::getCart();
        $cart->shipping_rate_id = $this->shipping ?: null;
        $cart->save();
        $this->cartUpdate();
    }

    public function updatedFormCountry($code)
    {
        $cart = CartController::getCart();
        $cart->country_code = $code ?: null;
        $cart->save();
        $this->cartUpdate();
    }

    public function updatedForm($value, $attribute)
    {
        session()->put(config('shopwire.cache_prefix') . 'checkout_form.' . $attribute, $value);
        if (isset($this->form_columns[$attribute]['toggle_group'])) {
            $this->form_groups[$this->form_columns[$attribute]['toggle_group']] = $value;
        }
    }

    public function updatedQuantity($quantity, $product_id)
    {
        if ($quantity < 0) {
            $quantity = 0;
        }
        CartController::update($product_id, $quantity);
        $this->emit('cartUpdate');
    }

    public function render()
    {
        return view('shopwire::checkout');
    }
}
