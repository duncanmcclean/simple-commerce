<?php

namespace Damcclean\Commerce\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class ReturnCustomer
{
    use Dispatchable, InteractsWithSockets;

    public $customer;

    public function __construct($customer)
    {
        $this->customer = $customer;
    }
}
