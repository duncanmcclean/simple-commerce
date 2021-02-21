<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Statamic\Entries\Entry;

class CartSaved
{
    use Dispatchable;
    use InteractsWithSockets;

    // TODO: this event should be renamed `OrderSaved`

    public $cart;

    public function __construct(Entry $cart)
    {
        $this->cart = $cart;
    }
}
