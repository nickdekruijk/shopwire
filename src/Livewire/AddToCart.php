<?php

namespace NickDeKruijk\Shopwire\Livewire;

use Livewire\Attributes\Locked;
use Livewire\Component;
use NickDeKruijk\Shopwire\Controllers\CartController;
use NickDeKruijk\Shopwire\Shopwire;
use NickDeKruijk\Shopwire\Support\Stock;

class AddToCart extends Component
{
    #[Locked]
    public $product;

    public int $quantity = 1;

    #[Locked]
    public int $cart_quantity = 0;

    // The chosen product option, bound via wire:model in the view.
    public $option_id = null;

    // True when the option was passed in, the customer can't choose another one.
    #[Locked]
    public bool $option_fixed = false;

    // The active options of the product, empty when it has none.
    #[Locked]
    public array $options = [];

    public function mount($product, $option = null)
    {
        $this->product = $product;

        $columns = config('shopwire.product_option_columns');
        foreach (Shopwire::productOptions($product) as $product_option) {
            $this->options[] = [
                'id' => $product_option->getKey(),
                'title' => empty($columns['title']) ? null : $product_option[$columns['title']],
                'price' => empty($columns['price']) ? null : $product_option[$columns['price']],
                'sold_out' => config('shopwire.prevent_oversell') && Stock::available($product->id, $product_option->getKey()) === 0.0,
            ];
        }

        if ($option) {
            $this->option_id = is_object($option) ? $option->getKey() : $option;
            $this->option_fixed = true;
        }

        $this->loadCartQuantity();
    }

    /**
     * Set the quantity to what is in the cart, of the chosen option when the product has options.
     */
    private function loadCartQuantity()
    {
        $this->cart_quantity = 0;
        $cart = CartController::getItems();
        if ($cart) {
            $items = $cart->items->where('product_id', $this->product->id);
            if ($this->options) {
                $items = $items->where('product_option_id', $this->option_id ?: null);
            }
            $this->cart_quantity = $items->sum('quantity');
        }
        $this->quantity = $this->cart_quantity ?: 1;
    }

    public function updatedOptionId()
    {
        $this->resetErrorBag();
        $this->loadCartQuantity();
    }

    public function add()
    {
        if ($this->quantity < 0) {
            $this->quantity = 0;
        }
        if ($this->quantity == 0 && $this->cart_quantity == 0) {
            $this->quantity = 1;
        }

        $this->resetErrorBag();

        // A product with options can only be added with one of its active options
        $option_id = $this->option_id ?: null;
        if (($this->options && !$option_id) || !CartController::validOption($this->product->id, $option_id)) {
            $this->addError('option_id', __('shopwire::cart.select_option'));
            return;
        }

        if (CartController::update($this->product->id, $this->quantity, $option_id)) {
            $this->cart_quantity = $this->quantity;
        } else {
            // The quantity was limited to the stock, tell the customer and show what is in the cart now
            $this->addError('quantity', Shopwire::stockMessage(Stock::available($this->product->id, $option_id, CartController::getCart())));
            $this->loadCartQuantity();
        }
        $this->dispatch('cartUpdate');
    }

    public function render()
    {
        // The price of the chosen option when it has one, otherwise the price of the product
        $option = collect($this->options)->firstWhere('id', $this->option_id);

        return view('shopwire::add-to-cart', [
            'price' => ($option['price'] ?? null) ?: $this->product->price,
        ]);
    }
}
