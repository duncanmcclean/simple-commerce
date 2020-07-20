<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Statamic\Entries\Entry;

class CustomerAddedToCart
{
    use Dispatchable, InteractsWithSockets;
    
    public $cart;

    public function __construct(Entry $cart)
    {
        $this->cart = $cart;
    }
}
