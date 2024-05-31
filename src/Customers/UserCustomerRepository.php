<?php

namespace DuncanMcClean\SimpleCommerce\Customers;

use DuncanMcClean\SimpleCommerce\Contracts\Customer;
use DuncanMcClean\SimpleCommerce\Contracts\CustomerRepository as RepositoryContract;
use DuncanMcClean\SimpleCommerce\Exceptions\CustomerNotFound;
use Illuminate\Support\Arr;
use Statamic\Facades\User;

class UserCustomerRepository implements RepositoryContract
{
    public function all()
    {
        return $this->query()->get();
    }

    public function query()
    {
        // Statamic users can live in the database OR in the stache, so we have
        // to extend both query builders then use the relevant one.
        if ($this->isUsingEloquentUsers()) {
            $usersModel = config('auth.providers.users.model');

            return app(EloquentUserQueryBuilder::class, [
                'builder' => (new $usersModel)->query(),
            ]);
        }

        return app(StacheUserQueryBuilder::class, [
            'store' => app('stache')->store('users'),
        ]);
    }

    public function find($id): ?Customer
    {
        $user = User::find($id);

        if (! $user) {
            return null;
        }

        return $this->fromUser($user);
    }

    public function findOrFail($id): Customer
    {
        $user = User::find($id);

        if (! $user) {
            throw new CustomerNotFound("Customer [{$id}] could not be found.");
        }

        return $this->fromUser($user);
    }

    public function findByEmail(string $email): ?Customer
    {
        $user = User::findByEmail($email);

        if (! $user) {
            throw new CustomerNotFound("Customer [{$email}] could not be found.");
        }

        return $this->fromUser($user);
    }

    public function fromUser($user)
    {
        return app(Customer::class)
            ->resource($user)
            ->id($user->id())
            ->email($user->email())
            ->data($user->data()->toArray());
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

        $ignoredKeys = ['id', 'email', 'roles', 'groups', 'super'];

        if ($user instanceof \Statamic\Auth\Eloquent\User) {
            $ignoredKeys = array_merge($ignoredKeys, $user->model()->getAppends());
        }

        $user->data(Arr::except($customer->data()->all(), $ignoredKeys));

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
