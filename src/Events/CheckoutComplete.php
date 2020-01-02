<?php

namespace Damcclean\Commerce\Events;

use Damcclean\Commerce\Models\Customer;
use Damcclean\Commerce\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class CheckoutComplete
{
    use Dispatchable, InteractsWithSockets;

    public $order;
    public $customer;

    public function __construct(Order $order, Customer $customer)
    {
        $this->order = $order;
        $this->customer = $customer;
    }
}
