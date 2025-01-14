<?php

namespace DuncanMcClean\SimpleCommerce\Http\Resources\CP\Orders;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Facades\User;

class ListedOrder extends JsonResource
{
    protected $blueprint;

    protected $columns;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function columns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function toArray($request)
    {
        $order = $this->resource;

        return [
            'id' => $order->orderNumber(),
            'order_number' => $order->orderNumber(),

            $this->merge($this->values([
                'date' => $order->date(),
                'status' => $order->status(),
                'customer' => $order->customer(),
                'coupon' => $order->coupon()?->id(),
                'shipping_method' => $order->shippingMethod()?->handle(),
                'line_items' => $order->lineItems(),
                'grand_total' => $order->grandTotal(),
                'sub_total' => $order->subTotal(),
                'discount_total' => $order->discountTotal(),
                'tax_total' => $order->taxTotal(),
                'shipping_total' => $order->shippingTotal(),
            ])),

            'edit_url' => $order->editUrl(),
            'viewable' => User::current()->can('view', $order),
            'editable' => User::current()->can('edit', $order),
            'actions' => Action::for($order),
        ];
    }

    protected function values($extra = [])
    {
        return $this->columns->mapWithKeys(function ($column) use ($extra) {
            $key = $column->field;
            $field = $this->blueprint->field($key);

            $value = $extra[$key] ?? $this->resource->get($key) ?? $field?->defaultValue();

            if (! $field) {
                return [$key => $value];
            }

            $value = $field->setValue($value)
                ->setParent($this->resource)
                ->preProcessIndex()
                ->value();

            return [$key => $value];
        });
    }
}
