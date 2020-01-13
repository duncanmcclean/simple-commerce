<?php

namespace Damcclean\Commerce\Events;

use Damcclean\Commerce\Models\Customer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class ReturnCustomer
{
    use Dispatchable, InteractsWithSockets;

    public $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }
}
