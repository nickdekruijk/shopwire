<?php

namespace NickDeKruijk\Shopwire\Livewire;

use Livewire\Component;
use NickDeKruijk\Shopwire\Controllers\CartController;

class Cart extends Component
{
    public $count = 0;
    public $unique = false;

    protected $listeners = [
        'cartUpdate',
    ];

    public function mount()
    {
        $this->cartUpdate();
    }

    public function cartUpdate()
    {
        $this->count = CartController::count($this->unique);
    }

    public function render()
    {
        return view('shopwire::cart');
    }
}
