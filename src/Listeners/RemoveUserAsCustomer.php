<?php

namespace DuncanMcClean\SimpleCommerce\Listeners;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use Illuminate\Auth\Events\Logout;

class RemoveUserAsCustomer
{
    public function handle(Logout $event)
    {
        if (Cart::hasCurrentCart()) {
            Cart::current()->customer(null)->save();
        }
    }
}