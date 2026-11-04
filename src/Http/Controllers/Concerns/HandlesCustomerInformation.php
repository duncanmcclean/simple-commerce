<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns;

use DuncanMcClean\SimpleCommerce\Contracts\Customer as CustomerContract;
use DuncanMcClean\SimpleCommerce\Contracts\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Customers\UserCustomerRepository;
use DuncanMcClean\SimpleCommerce\Exceptions\CustomerNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\User;

trait HandlesCustomerInformation
{
    protected function handleCustomerInformation(Request $request, OrderContract $cart): OrderContract
    {
        // When the customer driver is set to users, a user is logged in, and the cart doesn't have a customer,
        // we'll set the customer to the logged in user.
        if (
            $this->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], UserCustomerRepository::class)
            && Auth::check()
            && ! $cart->customer()
        ) {
            $cart->customer(Auth::id());
        } elseif (! $request->has('customer') && ! $request->has('email')) {
            return $cart;
        }

        // When the customer is a string, we'll assume it's a customer ID.
        if (is_string($request->customer)) {
            $customer = Customer::find($request->customer);
            $cart->customer($customer->id());
        }

        // When an email is passed, we'll assume it's a new customer.
        if (is_string($request->email)) {
            $cart = $this->findOrCreateCustomer($cart, $request->email);

            $cart = $this->updateCustomer($cart, [
                'name' => $request->name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
            ]);
        }

        // When the customer is an array, we'll first check if a customer exists with
        // the provided email. Then we'll update the customer with any other provided data.
        if (is_array($request->customer)) {
            // Do we have an email key, if so, create/update the customer.
            if ($email = Arr::get($request->customer, 'email')) {
                $cart = $this->findOrCreateCustomer($cart, $email);
            }

            $cart = $this->updateCustomer($cart, $request->customer);
        }

        return $cart;
    }

    private function findOrCreateCustomer(OrderContract $cart, string $email): OrderContract
    {
        if ($customer = $cart->customer()) {
            if ($customer->email() === $email) {
                return $cart;
            }

            if ($this->belongsToCurrentUser($customer) && ! $this->emailBelongsToAnotherCustomer($email, $customer)) {
                $customer->email($email)->save();

                return $cart;
            }
        }

        // Otherwise, find or create a customer with the provided email.
        try {
            $customer = Customer::findByEmail($email);
            $cart->customer($customer->id());
        } catch (CustomerNotFound $e) {
            $customer = Customer::make()->email($email)->data(['published' => true])->save();
            $cart->customer($customer->id());
        }

        return $cart;
    }

    private function belongsToCurrentUser(CustomerContract $customer): bool
    {
        $user = User::current();

        return $user && $user->email() === $customer->email();
    }

    private function emailBelongsToAnotherCustomer(string $email, CustomerContract $customer): bool
    {
        try {
            return Customer::findByEmail($email)->id() !== $customer->id();
        } catch (CustomerNotFound $e) {
            return false;
        }
    }

    private function updateCustomer(OrderContract $cart, array $data): OrderContract
    {
        $whitelist = array_merge(
            ['name', 'first_name', 'last_name'],
            config('simple-commerce.field_whitelist.customers')
        );

        $cart->customer()
            ->merge(collect($data)->only($whitelist)->filter()->toArray())
            ->save();

        return $cart;
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
