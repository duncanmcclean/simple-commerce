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
                'line_items' => $order->lineItems()->map->toArray()->all(),
                'grand_total' => $order->grandTotal(),
                'sub_total' => $order->subTotal(),
                'discount_total' => $order->discountTotal(),
                'tax_total' => $order->taxTotal(),
                'shipping_total' => $order->shippingTotal(),
                // todo: other stuff probably needs to be merged in here too
            ]);

        $fields = $blueprint
            ->fields()
            ->setParent($order)
            ->addValues($values->all())
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }
}
