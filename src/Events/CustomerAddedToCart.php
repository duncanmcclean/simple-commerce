<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Statamic\Entries\Entry;

class CustomerAddedToCart
{
    use Dispatchable;
    use InteractsWithSockets;

    // TODO: this event should be renamed `CustomerAddedToOrder`

    public $cart;

    public function __construct(Entry $cart)
    {
        $this->cart = $cart;
    }
}
