<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Orders;

trait ExtractsFromOrderFields
{
    protected function extractFromFields($order, $blueprint)
    {
        $values = $order->data()
            ->merge([
                'order_number' => $order->orderNumber(),
                'date' => $order->date(),
                'line_items' => $order->lineItems()->map->fileData()->all(),
                'grand_total' => $order->grandTotal(),
                'sub_total' => $order->subTotal(),
                'coupon_total' => $order->couponTotal(),
                'tax_total' => $order->taxTotal(),
                'shipping_total' => $order->shippingTotal(),
                'customer' => $order->customer(),
            ]);

        $fields = $blueprint
            ->fields()
            ->setParent($order)
            ->addValues($values->all())
            ->preProcess();

        $values = $fields->values()->merge([
            'id' => $order->id(),
        ]);

        return [$values->all(), $fields->meta()->all()];
    }
}
