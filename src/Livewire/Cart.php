<?php

namespace NickDeKruijk\Shopwire\Livewire;

use Livewire\Component;
use NickDeKruijk\Shopwire\Controllers\CartController;

class Cart extends Component
{
    public $count = 0;
    public $unique = false;

    protected $listeners = [
        'cartUpdate' => 'updateCount',
    ];

    public function mount()
    {
        $this->updateCount();
    }

    public function updateCount()
    {
        $items = CartController::getItems();
        $this->count = $this->unique ? $items->count_unique : $items->count;
    }

    public function render()
    {
        return view('shopwire::cart');
    }
}
