<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Eloquent;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\OrderRepository as RepositoryContract;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\QueryBuilder;
use DuncanMcClean\SimpleCommerce\Stache;
use Illuminate\Support\Carbon;

class OrderRepository extends Stache\Repositories\OrderRepository implements RepositoryContract
{
    public function query()
    {
        return app(QueryBuilder::class, ['builder' => app('simple-commerce.orders.eloquent.model')::query()]);
    }

    public function save(Order $order): void
    {
        $model = $order->toModel();

        if (! $order->date()) {
            $model->date = Carbon::now('UTC');
        }

        $model->save();

        $model = $model->fresh();

        $order->model($model);
        $order->date($model->date);
        $order->orderNumber($model->order_number);
    }

    public function delete(Order $order): void
    {
        $order->model()->delete();
    }

    public static function bindings(): array
    {
        return [
            Order::class => \DuncanMcClean\SimpleCommerce\Orders\Eloquent\Order::class,
            QueryBuilder::class => OrderQueryBuilder::class,
        ];
    }
}
