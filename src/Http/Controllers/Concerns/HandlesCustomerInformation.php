<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns;

use DuncanMcClean\SimpleCommerce\Contracts\Customer as CustomerContract;
use DuncanMcClean\SimpleCommerce\Contracts\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Customers\Concerns\AuthorizesCustomerAccess;
use DuncanMcClean\SimpleCommerce\Customers\UserCustomerRepository;
use DuncanMcClean\SimpleCommerce\Exceptions\CustomerNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait HandlesCustomerInformation
{
    use AuthorizesCustomerAccess;

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

        $data = is_string($request->email)
            ? $request->only(['name', 'first_name', 'last_name', 'email'])
            : [];

        if (is_array($request->customer)) {
            $data = array_merge($data, array_filter($request->customer));
        }

        if ($email = $data['email'] ?? null) {
            return $this->findOrCreateCustomer($cart, $email, $data);
        }

        if (is_array($request->customer)) {
            $this->updateCustomer($cart->customer(), $data);
        }

        return $cart;
    }

    private function findOrCreateCustomer(OrderContract $cart, string $email, array $data): OrderContract
    {
        if ($customer = $cart->customer()) {
            if ($customer->email() === $email) {
                $this->updateCustomer($customer, $data);

                return $cart;
            }

            if ($this->belongsToCurrentUser($customer) && ! $this->emailBelongsToAnotherCustomer($email, $customer)) {
                $this->updateCustomer($customer, $data);
                $customer->email($email)->save();

                return $cart;
            }
        }

        // Otherwise, find or create a customer with the provided email.
        try {
            $customer = Customer::findByEmail($email);
        } catch (CustomerNotFound $e) {
            $customer = Customer::make()->email($email)->data(['published' => true]);
        }

        $this->updateCustomer($customer, $data);
        $cart->customer($customer->id());

        return $cart;
    }

    private function emailBelongsToAnotherCustomer(string $email, CustomerContract $customer): bool
    {
        try {
            return Customer::findByEmail($email)->id() !== $customer->id();
        } catch (CustomerNotFound $e) {
            return false;
        }
    }

    private function updateCustomer(CustomerContract $customer, array $data): void
    {
        if ($customer->id() && ! $this->belongsToCurrentUser($customer)) {
            return;
        }

        $whitelist = array_merge(
            ['name', 'first_name', 'last_name'],
            config('simple-commerce.field_whitelist.customers')
        );

        $customer
            ->merge(collect($data)->only($whitelist)->filter()->toArray())
            ->save();
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
