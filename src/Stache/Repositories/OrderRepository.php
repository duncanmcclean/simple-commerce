<?php

namespace DuncanMcClean\SimpleCommerce\Stache\Repositories;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\OrderRepository as RepositoryContract;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\QueryBuilder;
use DuncanMcClean\SimpleCommerce\Exceptions\OrderNotFound;
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

    public function find($id): ?Order
    {
        return $this->query()->where('id', $id)->first();
    }

    public function findOrFail($id): Order
    {
        $order = $this->find($id);

        if (! $order) {
            throw new OrderNotFound("Order [{$id}] could not be found.");
        }

        return $order;
    }

    public function make(): Order
    {
        return app(Order::class);
    }

    public function makeFromCart(Cart $cart): Order
    {
        return self::make()
            ->cart($cart->id())
//            ->customer()
            ->lineItems($cart->lineItems())
            ->grandTotal($cart->grandTotal())
            ->subTotal($cart->subTotal())
            ->discountTotal($cart->discountTotal())
            ->taxTotal($cart->taxTotal())
            ->data($cart->data()->toArray());
    }

    public function save(Order $order): void
    {
        if (! $order->id()) {
            $order->id($this->stache->generateId());
        }

        if (! $order->orderNumber()) {
            $order->orderNumber($this->generateOrderNumber());
        }

        $this->store->save($order);
    }

    public function delete(Order $order): void
    {
        $this->store->delete($order);
    }

    public function generateOrderNumber(): int
    {
        $lastOrder = $this->query()->orderByDesc('order_number')->first();

        if (! $lastOrder) {
            return config('simple-commerce.minimum_order_number', 1000);
        }

        return (int) $lastOrder->orderNumber() + 1;
    }

    public static function bindings(): array
    {
        return [
            Order::class => \DuncanMcClean\SimpleCommerce\Orders\Order::class,
            QueryBuilder::class => \DuncanMcClean\SimpleCommerce\Stache\Query\OrderQueryBuilder::class,
        ];
    }
}
