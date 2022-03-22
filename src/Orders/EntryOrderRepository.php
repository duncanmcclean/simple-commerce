<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\OrderRepository as RepositoryContract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\OrderNotFound;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Arr;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class EntryOrderRepository implements RepositoryContract
{
    protected $collection;

    public function __construct()
    {
        $this->collection = SimpleCommerce::orderDriver()['collection'];
    }

    public function all()
    {
        return Entry::whereCollection($this->collection)->all();
    }

    public function find($id): ?Order
    {
        $entry = Entry::find($id);

        if (! $entry) {
            throw new OrderNotFound("Order [{$id}] could not be found.");
        }

        return app(Order::class)
            ->entry($entry)
            ->id($entry->id())
            ->data(array_merge(
                $entry->data()->toArray(),
                [
                    'site' => optional($entry->site())->handle(),
                    'slug' => $entry->slug(),
                    'published' => $entry->published(),
                ]
            ));
    }

    public function make(): Order
    {
        return app(Order::class);
    }

    public function save($order): void
    {
        $entry = $order->entry();

        if (! $entry) {
            $entry = Entry::make()
                ->id(Stache::generateId())
                ->collection($this->collection);
        }

        if (! $order->has('title')) {
            $entry->set('title', SimpleCommerce::freshOrderNumber());
        }

        if ($order->get('site')) {
            $entry->site($order->get('site'));
        }

        if ($order->get('slug')) {
            $entry->slug($order->get('slug'));
        }

        if ($order->get('published')) {
            $entry->published($order->get('published'));
        }

        $entry->data(
            Arr::except($order->data(), ['id', 'site', 'slug', 'published'])
        );

        $entry->save();

        $order->id = $entry->id();
        $order->entry = $entry;
    }

    public function delete($order): void
    {
        $order->entry()->delete();
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
