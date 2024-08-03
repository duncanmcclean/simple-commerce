<?php

namespace DuncanMcClean\SimpleCommerce\Listeners;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use Illuminate\Auth\Events\Login;
use Statamic\Facades\User;

class AssignUserAsCustomer
{
    public function handle(Login $event)
    {
        if (Cart::hasCurrentCart()) {
            Cart::current()->customer(User::fromUser($event->user))->save();
        }
    }
}