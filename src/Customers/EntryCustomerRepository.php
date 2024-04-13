<?php

namespace DuncanMcClean\SimpleCommerce\Customers;

use DuncanMcClean\SimpleCommerce\Contracts\Customer;
use DuncanMcClean\SimpleCommerce\Contracts\CustomerRepository as RepositoryContract;
use DuncanMcClean\SimpleCommerce\Exceptions\CustomerNotFound;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class EntryCustomerRepository implements RepositoryContract
{
    protected $collection;

    public function __construct()
    {
        $this->collection = SimpleCommerce::customerDriver()['collection'];
    }

    public function all()
    {
        return $this->query()->get();
    }

    public function query()
    {
        return app(EntryQueryBuilder::class, [
            'store' => app('stache')->store('entries'),
        ])->where('collection', $this->collection);
    }

    public function find($id): ?Customer
    {
        $entry = Entry::find($id);

        if (! $entry) {
            return null;
        }

        return $this->fromEntry($entry);
    }

    public function findOrFail($id): Customer
    {
        $customer = $this->find($id);

        if (! $customer) {
            throw new CustomerNotFound("Customer [{$id}] could not be found.");
        }

        return $customer;
    }

    public function findByEmail(string $email): ?Customer
    {
        $entry = Entry::query()
            ->where('collection', $this->collection)
            ->where('slug', Str::slug($email))
            ->first();

        if (! $entry) {
            throw new CustomerNotFound("Customer [{$email}] could not be found.");
        }

        return $this->fromEntry($entry);
    }

    public function fromEntry($entry)
    {
        return app(Customer::class)
            ->resource($entry)
            ->id($entry->id())
            ->email($entry->get('email'))
            ->data(array_merge(
                $entry->data()->toArray(),
                [
                    'site' => optional($entry->site())->handle(),
                    'slug' => $entry->slug(),
                    'published' => $entry->published(),
                ]
            ));
    }

    public function make(): Customer
    {
        return app(Customer::class);
    }

    public function save(Customer $customer): void
    {
        $entry = $customer->resource();

        if (! $entry) {
            $entry = Entry::make()
                ->id(Stache::generateId())
                ->collection($this->collection);
        }

        if ($customer->get('site')) {
            $entry->locale($customer->get('site'));
        }

        if ($customer->get('slug')) {
            $entry->slug($customer->get('slug'));
        } else {
            $entry->slug(Str::slug($customer->email()));
        }

        if ($customer->get('published')) {
            $entry->published($customer->get('published'));
        } else {
            $entry->published(true);
        }

        $entry->data(
            array_merge(Arr::except($customer->data(), ['id', 'site', 'slug', 'published'])->toArray(), [
                'email' => $customer->email(),
            ])
        );

        $entry->save();

        $customer->id = $entry->id();
        $customer->email = $entry->get('email');
        $customer->resource = $entry;

        $customer->merge([
            'site' => $entry->site()->handle(),
            'slug' => $entry->slug(),
            'published' => $entry->published(),
        ]);
    }

    public function delete(Customer $customer): void
    {
        $customer->resource()->delete();
    }

    protected function isUsingEloquentDriverWithIncrementingIds(): bool
    {
        return config('statamic.eloquent-driver.entries.model') === \Statamic\Eloquent\Entries\EntryModel::class;
    }

    public static function bindings(): array
    {
        return [];
    }
}
