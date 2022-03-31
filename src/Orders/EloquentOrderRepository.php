<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Contracts\OrderRepository as RepositoryContract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\OrderNotFound;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

class EloquentOrderRepository implements RepositoryContract
{
    protected $model;

    public function __construct()
    {
        $this->model = SimpleCommerce::orderDriver()['model'];
    }

    public function all()
    {
        return (new $this->model)->all();
    }

    public function find($id): ?Order
    {
        $model = (new $this->model)->find($id);

        if (! $model) {
            throw new OrderNotFound("Order [{$id}] could not be found.");
        }

        return app(Order::class)
            ->resource($model)
            ->id($model->id)
            ->isPaid($model->is_paid)
            ->isShipped($model->is_shipped)
            ->isRefunded($model->is_refunded)
            ->lineItems($model->line_items)
            ->grandTotal($model->grand_total)
            ->itemsTotal($model->items_total)
            ->taxTotal($model->tax_total)
            ->shippingTotal($model->shipping_total)
            ->couponTotal($model->coupon_total)
            ->customer($model->customer_id)
            ->coupon($model->coupon)
            ->gateway($model->gateway)
            ->data($model->data);
    }

    public function make(): Order
    {
        return app(Order::class);
    }

    public function save($order): void
    {
        $model = $order->resource();

        if (! $model) {
            $model = new $this->model();
        }

        $model->is_paid = $order->isPaid();
        $model->is_shipped = $order->isShipped();
        $model->is_refunded = $order->isRefunded();
        $model->line_items = $order->lineItems();
        $model->grand_total = $order->grandTotal();
        $model->items_total = $order->itemsTotal();
        $model->tax_total = $order->taxTotal();
        $model->shipping_total = $order->shippingTotal();
        $model->coupon_total = $order->couponTotal();
        $model->customer_id = optional($order->customer())->id();
        $model->coupon = $order->coupon();
        $model->gateway = $order->gateway();
        $model->data = $order->data();

        $model->save();

        $order->id = $model->id;
        $order->isPaid = $model->is_paid;
        $order->isShipped = $model->is_shipped;
        $order->isRefunded = $model->is_refunded;
        $order->lineItems = collect($model->line_items);
        $order->grandTotal = $model->grand_total;
        $order->itemsTotal = $model->items_total;
        $order->taxTotal = $model->tax_total;
        $order->shippingTotal = $model->shipping_total;
        $order->couponTotal = $model->coupon_total;
        $order->customer = $model->customer_id;
        $order->coupon = $model->coupon;
        $order->gateway = $model->gateway;
        $order->data = collect($model->data);
    }

    public function delete($order): void
    {
        $order->resource()->delete();
    }

    public static function bindings(): array
    {
        return [];
    }
}
