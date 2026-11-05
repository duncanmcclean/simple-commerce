<?php

namespace DuncanMcClean\SimpleCommerce\Customers\Concerns;

use DuncanMcClean\SimpleCommerce\Contracts\Customer;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use Statamic\Facades\User;

trait AuthorizesCustomerAccess
{
    protected function filterCustomerInformation(Order $cart, array $data): array
    {
        if (! $this->belongsToCurrentUser($cart->customer())) {
            unset($data['customer'], $data['customer_id']);
        }

        return $data;
    }

    private function belongsToCurrentUser(?Customer $customer): bool
    {
        $user = User::current();

        return $user && $customer && $user->email() === $customer->email();
    }
}
