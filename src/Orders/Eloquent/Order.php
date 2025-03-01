<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Eloquent;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Orders\Order as StacheOrder;
use Illuminate\Support\Str;

class Order extends StacheOrder
{
    protected $model;

    public static function fromModel(OrderModel $model): self
    {
        return (new static)
            ->model($model)
            ->id($model->uuid)
            ->site($model->site)
            ->orderNumber($model->order_number)
            ->date($model->date)
            ->cart($model->cart)
            ->status($model->status)
            ->customer(
                // Guest customers are stored as JSON strings, so we need to decode them.
                Str::contains($model->customer, '{"')
                    ? json_decode($model->customer, true)
                    : $model->customer
            )
            ->coupon($model->coupon)
            ->lineItems($model->line_items)
            ->grandTotal($model->grand_total)
            ->subTotal($model->sub_total)
            ->discountTotal($model->discount_total)
            ->taxTotal($model->tax_total)
            ->shippingTotal($model->shipping_total)
            ->data($model->data);
    }

    public static function makeModelFromContract(OrderContract $source): OrderModel
    {
        $class = app('simple-commerce.orders.eloquent.model');

        $attributes = [
            'site' => $source->site()->handle(),
            'date' => $source->date(),
            'cart' => $source->cart(),
            'status' => $source->status()->value,
            'customer' => is_array($source->customer)
                ? json_encode($source->customer)
                : $source->customer()?->getKey(),
            'coupon' => $source->coupon,
            'line_items' => $source->lineItems()->map->fileData()->all(),
            'grand_total' => $source->grandTotal(),
            'sub_total' => $source->subTotal(),
            'discount_total' => $source->discountTotal(),
            'tax_total' => $source->taxTotal(),
            'shipping_total' => $source->shippingTotal(),
            'data' => $source->data()->all(),
        ];

        if ($uuid = $source->id()) {
            $attributes['uuid'] = $uuid;
        }

        if ($orderNumber = $source->orderNumber()) {
            $attributes['order_number'] = $orderNumber;
        }

        return $class::findOrNew($source->id())->fill($attributes);
    }

    public function toModel()
    {
        return self::makeModelFromContract($this);
    }

    public function model($model = null)
    {
        if (func_num_args() === 0) {
            return $this->model;
        }

        $this->model = $model;

        if (! is_null($model)) {
            $this->id($model->uuid);
        }

        return $this;
    }
}
