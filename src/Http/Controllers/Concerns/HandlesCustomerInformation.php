<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Support\Arr;

trait HandlesCustomerInformation
{
    protected function handleCustomerInformation(Request $request, Cart $cart): Cart
    {
        $customerData = $request->get('customer', collect([
            'name' => $request->name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
        ])->filter()->all());

        if ($user = User::current()) {
            // When a customer is missing from the order, we'll set it to the logged-in user.
            if (! $cart->customer()) {
                $cart->customer($user);
            }

            // When the request contains customer data, update the user.
            if ($customerData) {
                $user
                    ->email(Arr::get($customerData, 'email', $user->email()))
                    ->merge(Arr::except($customerData, ['email', 'super', 'roles', 'groups']))
                    ->save();
            }

            return $cart;
        }

        // When the request contains customer data, create or update a guest customer.
        if ($customerData) {
            if (! $cart->customer()) {
                $guest = (new GuestCustomer)->data($customerData);
                $cart->customer($guest);
            }

            $cart->customer()->merge($customerData);
        }

        return $cart;
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
