<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CustomerCreated
{
    use Dispatchable, InteractsWithSockets;

    public $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }
}
