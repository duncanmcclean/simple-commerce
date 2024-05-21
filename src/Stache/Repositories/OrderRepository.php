<?php

namespace DuncanMcClean\SimpleCommerce\Stache\Repositories;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\OrderRepository as RepositoryContract;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\QueryBuilder;
use DuncanMcClean\SimpleCommerce\Exceptions\OrderNotFound;
use Illuminate\Support\Str;
use Statamic\Stache\Stache;

class OrderRepository implements RepositoryContract
{
    protected $stache;

    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('orders');
    }

    public function all()
    {
        return $this->query()->get();
    }

    public function query()
    {
        return app(QueryBuilder::class);
    }

    public function find($orderNumber): ?Order
    {
        return $this->query()->where('order_number', $orderNumber)->first();
    }

    public function findOrFail($orderNumber): Order
    {
        $order = $this->find($orderNumber);

        if (! $order) {
            throw new OrderNotFound("Order [{$orderNumber}] could not be found.");
        }

        return $order;
    }

    public function make(): Order
    {
        return app(Order::class);
    }

    public function save(Order $order): void
    {
        if (! $order->orderNumber()) {
            $order->orderNumber($this->generateOrderNumber());
        }

        $this->store->save($order);
    }

    public function delete(Order $order): void
    {
        $this->store->delete($order);
    }

    protected function generateOrderNumber(): int
    {
        $lastOrder = $this->query()->where('order_number', '!=', null)->orderByDesc('order_number')->first();

        // Fallback to get order number from title (otherwise: start from the start..)
        if (! $lastOrder) {
            $lastOrder = $this->query()->where('title', '!=', null)->orderByDesc('title')->first();

            // And if we don't have any orders with the old title format, start from the start.
            if (! $lastOrder) {
                return config('simple-commerce.minimum_order_number', 1000);
            }

            $lastOrderNumber = (int) Str::of($lastOrder->get('title'))
                ->replace('Order ', '')
                ->replace('#', '')
                ->__toString();
        } else {
            $lastOrderNumber = $lastOrder->get('order_number');
        }

        return (int) $lastOrderNumber + 1;
    }

    public static function bindings(): array
    {
        return [
            Order::class => \DuncanMcClean\SimpleCommerce\Orders\Order::class,
            QueryBuilder::class => \DuncanMcClean\SimpleCommerce\Stache\Query\OrderQueryBuilder::class,
        ];
    }
}
