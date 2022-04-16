<?php

namespace DoubleThreeDigital\SimpleCommerce\Customers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer;
use DoubleThreeDigital\SimpleCommerce\Contracts\CustomerRepository as RepositoryContract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use Illuminate\Support\Arr;
use Statamic\Facades\User;

class UserCustomerRepository implements RepositoryContract
{
    public function all()
    {
        return User::all();
    }

    public function find($id): ?Customer
    {
        $user = User::find($id);

        if (! $user) {
            throw new CustomerNotFound("Customer [{$id}] could not be found.");
        }

        return app(Customer::class)
            ->resource($user)
            ->id($user->id())
            ->email($user->email())
            ->data($user->data()->toArray());
    }

    public function findByEmail(string $email): ?Customer
    {
        $user = User::findByEmail($email);

        if (! $user) {
            throw new CustomerNotFound("Customer [{$email}] could not be found.");
        }

        return $this->find($user->id());
    }

    public function make(): Customer
    {
        return app(Customer::class);
    }

    public function save(Customer $customer): void
    {
        // We're going to use this in the meantime...
        $user = $customer->resource();

        if (! $user) {
            $user = User::make()
                ->id($customer->id());
        }

        if ($customer->email()) {
            $user->email($customer->email());
        }

        $user->data(
            Arr::except($customer->data(), ['id', 'email'])
        );

        $user->save();

        $customer->id = $user->id();
        $customer->email = $user->email();
    }

    public function delete(Customer $customer): void
    {
        $customer->resource()->delete();
    }

    protected function isUsingEloquentUsers(): bool
    {
        return config('statamic.users.repository') === 'eloquent';
    }

    public static function bindings(): array
    {
        return [];
    }
}
