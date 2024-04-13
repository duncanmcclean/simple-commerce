<?php

namespace DuncanMcClean\SimpleCommerce\Customers;

use Doctrine\DBAL\Schema\Column;
use DuncanMcClean\SimpleCommerce\Contracts\Customer;
use DuncanMcClean\SimpleCommerce\Contracts\CustomerRepository as RepositoryContract;
use DuncanMcClean\SimpleCommerce\Exceptions\CustomerNotFound;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Schema;

class EloquentCustomerRepository implements RepositoryContract
{
    protected $model;

    protected $knownColumns = ['name'];

    public function __construct()
    {
        $this->model = SimpleCommerce::customerDriver()['model'];
    }

    public function all()
    {
        return $this->query()->get();
    }

    public function query()
    {
        return app(EloquentQueryBuilder::class, [
            'builder' => (new $this->model)->query(),
        ]);
    }

    public function find($id): ?Customer
    {
        $model = (new $this->model)->find($id);

        if (! $model) {
            return null;
        }

        return $this->fromModel($model);
    }

    public function findOrFail($id): Customer
    {
        $model = (new $this->model)->find($id);

        if (! $model) {
            throw new CustomerNotFound("Customer [{$id}] could not be found.");
        }

        return $this->fromModel($model);
    }

    public function findByEmail(string $email): ?Customer
    {
        $customer = $this->query()->where('email', $email)->first();

        if (! $customer) {
            throw new CustomerNotFound("Customer [{$email}] could not be found.");
        }

        return $customer;
    }

    public function fromModel($model)
    {
        return app(Customer::class)
            ->resource($model)
            ->id($model->id)
            ->email($model->email)
            ->data(
                collect($model->data)
                    ->merge([
                        'name' => $model->name,
                    ])
                    ->merge(
                        collect($this->getCustomColumns())
                            ->mapWithKeys(function ($columnName) use ($model) {
                                return [$columnName => $model->{$columnName}];
                            })
                            ->toArray()
                    )
            );
    }

    public function make(): Customer
    {
        return app(Customer::class);
    }

    public function save(Customer $customer): void
    {
        $model = $customer->resource();

        if (! $model) {
            $model = new $this->model();
        }

        $model->email = $customer->email();

        if ($name = $customer->get('name')) {
            $model->name = $name;
        }

        // If anything in the order data has it's own column, save it
        // there, rather than in the data column.
        collect($this->getCustomColumns())
            ->filter(function ($columnName) use ($customer) {
                return $customer->has($columnName);
            })
            ->each(function ($columnName) use (&$model, $customer) {
                $model->{$columnName} = $customer->get($columnName);
            });

        $model->data = $customer->data()
            ->except($this->knownColumns)
            ->except($this->getCustomColumns());

        $model->save();

        $customer->id = $model->id;
        $customer->email = $model->email;

        $customer->data = collect($model->data)
            ->merge([
                'name' => $model->name,
            ])
            ->merge(
                collect($this->getCustomColumns())
                    ->mapWithKeys(function ($columnName) use ($model) {
                        return [$columnName => $model->{$columnName}];
                    })
                    ->toArray()
            );

        $customer->resource = $model;
    }

    public function delete(Customer $customer): void
    {
        $customer->resource()->delete();
    }

    /**
     * Returns an array of custom columns the developer
     * has added to the 'customers' table.
     */
    protected function getCustomColumns(): array
    {
        return collect(Schema::getColumns((new $this->model)->getTable()))
            ->reject(fn (array $column) => in_array($column['name'], $this->knownColumns))
            ->map(fn (array $column) => $column['name'])
            ->all();
    }

    public static function bindings(): array
    {
        return [];
    }
}
