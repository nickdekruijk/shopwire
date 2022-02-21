<?php

namespace NickDeKruijk\Shopwire\Livewire;

use Livewire\Component;
use NickDeKruijk\Shopwire\Controllers\CartController;

class AddToCart extends Component
{
    public $product;
    public $quantity = 1;

    public function mount($product)
    {
        $this->product = $product;
    }

    public function add()
    {
        CartController::add($this->product, $this->quantity);
        $this->emit('cartUpdate');
    }

    public function render()
    {
        return view('shopwire::add-to-cart');
    }
}
