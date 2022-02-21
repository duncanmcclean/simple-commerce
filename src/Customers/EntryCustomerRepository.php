<?php

namespace DoubleThreeDigital\SimpleCommerce\Customers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer;
use DoubleThreeDigital\SimpleCommerce\Contracts\CustomerRepository as RepositoryContract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Arr;
use Statamic\Facades\Entry;

class EntryCustomerRepository implements RepositoryContract
{
    protected $collection;

    public function __construct()
    {
        $this->collection = SimpleCommerce::customerDriver()['collection'];
    }

    public function all()
    {
        return Entry::whereCollection($this->collection)->all();
    }

    public function find($id): ?Customer
    {
        $entry = Entry::find($id);

        if (! $entry) {
            throw new CustomerNotFound("Customer [{$id}] could not be found.");
        }

        return app(Customer::class)
            ->entry($entry)
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

    public function findByEmail(string $email): ?Customer
    {
        $entry = Entry::query()
            ->where('collection', $this->collection)
            ->where('slug', str_slug($email)) // TODO: index the email?
            ->first();

        if (! $entry) {
            throw new CustomerNotFound("Customer [{$email}] could not be found.");
        }

        return $this->find($entry->id());
    }

    public function make(): Customer
    {
        return app(Customer::class);
    }

    public function save($customer): void
    {
        $entry = $customer->entry();

        if (! $entry) {
            $entry = Entry::make()
                ->id($customer->id())
                ->collection($this->collection);
        }

        if ($customer->get('site')) {
            $entry->site($customer->get('site'));
        }

        if ($customer->get('slug')) {
            $entry->slug($customer->get('slug'));
        } else {
            $entry->slug(str_slug($customer->email()));
        }

        if ($customer->get('published')) {
            $entry->published($customer->get('published'));
        } else {
            $entry->published(true);
        }

        $entry->data(
            Arr::except($customer->data(), ['id', 'site', 'slug', 'published'])
        );

        $entry->save();
    }

    public function delete($customer): void
    {
        $customer->entry()->delete();
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
