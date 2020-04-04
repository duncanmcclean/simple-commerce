<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CustomerCreated
{
    use Dispatchable, InteractsWithSockets;

    public $customer;

    public function __construct($customer)
    {
        $this->customer = $customer;
    }
}
