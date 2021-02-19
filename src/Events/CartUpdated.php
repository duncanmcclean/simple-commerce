<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Statamic\Entries\Entry;

class CartUpdated
{
    use Dispatchable, InteractsWithSockets;

     // TODO: this event should be renamed `OrderUpdated`

    public $cart;

    public function __construct(Entry $cart)
    {
        $this->cart = $cart;
    }
}
