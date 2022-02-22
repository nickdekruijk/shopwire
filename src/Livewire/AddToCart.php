<?php

namespace NickDeKruijk\Shopwire\Livewire;

use Livewire\Component;
use NickDeKruijk\Shopwire\Controllers\CartController;

class AddToCart extends Component
{
    public $product;
    public $quantity = 1;
    public $cart_quantity = 0;

    public function mount($product)
    {
        $this->product = $product;
        $cart = CartController::getItems();
        if ($cart) {
            $this->quantity = $this->cart_quantity = $cart->items->where('product_id', $this->product->id)->sum('quantity');
            if ($this->quantity == 0) {
                $this->quantity = 1;
            }
        }
    }

    public function add()
    {
        if ($this->quantity == 0 && $this->cart_quantity == 0) {
            $this->quantity = 1;
        }
        CartController::update($this->product, $this->quantity ?: 1);
        $this->cart_quantity = $this->quantity;
        $this->emit('cartUpdate');
    }

    public function render()
    {
        return view('shopwire::add-to-cart');
    }
}
