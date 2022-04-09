<?php

namespace DoubleThreeDigital\SimpleCommerce\Customers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer;
use DoubleThreeDigital\SimpleCommerce\Contracts\CustomerRepository as RepositoryContract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Arr;

class EloquentCustomerRepository implements RepositoryContract
{
    protected $model;

    public function __construct()
    {
        $this->model = SimpleCommerce::customerDriver()['model'];
    }

    public function all()
    {
        return (new $this->model)->all();
    }

    public function find($id): ?Customer
    {
        $model = (new $this->model)->find($id);

        if (! $model) {
            throw new CustomerNotFound("Customer [{$id}] could not be found.");
        }

        return app(Customer::class)
            ->resource($model)
            ->id($model->id)
            ->email($model->email)
            ->data(array_merge($model->data, [
                'name' => $model->name,
            ]));
    }

    public function findByEmail(string $email): ?Customer
    {
        $model = (new $this->model)->query()->firstWhere('email', $email);

        if (! $model) {
            throw new CustomerNotFound("Customer [{$email}] could not be found.");
        }

        return $this->find($model->id);
    }

    public function make(): Customer
    {
        return app(Customer::class);
    }

    public function save($customer): void
    {
        $model = $customer->resource();

        if (! $model) {
            $model = new $this->model();
        }

        $model->email = $customer->email();
        $model->data = Arr::except($customer->data()->toArray(), ['name']);

        if ($name = $customer->get('name')) {
            $model->name = $name;
        }

        $model->save();

        $customer->id = $model->id;
        $customer->email = $model->email;
        $customer->resource = $model;
    }

    public function delete($customer): void
    {
        $customer->resource()->delete();
    }

    public static function bindings(): array
    {
        return [];
    }
}
