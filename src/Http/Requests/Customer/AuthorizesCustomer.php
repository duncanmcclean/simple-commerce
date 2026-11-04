<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests\Customer;

use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use Statamic\Facades\User;

trait AuthorizesCustomer
{
    use CartDriver;

    public function authorize(): bool
    {
        $customer = Customer::find($this->route('customer'));

        if (! $customer) {
            return false;
        }

        if ($this->hasCart() && $this->getCart()->customer()?->id() === $customer->id()) {
            return true;
        }

        $user = User::current();

        return $user && $user->email() === $customer->email();
    }
}
