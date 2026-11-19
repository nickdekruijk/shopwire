<?php

namespace NickDeKruijk\Shopwire\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use NickDeKruijk\Shopwire\Models\Order;

/**
 * Dispatched exactly once per order, right after the order is marked as paid.
 */
class OrderPaid
{
    use Dispatchable, SerializesModels;

    public function __construct(public Order $order)
    {
    }
}
