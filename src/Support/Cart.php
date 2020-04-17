<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

use DoubleThreeDigital\SimpleCommerce\Models\LineItem;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingRate;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Collection;
use Statamic\Stache\Stache;

class Cart
{
    public function all(): Collection
    {
        return Order::notCompleted()->get();
    }

    public function find(string $uuid): ?Collection
    {
        $order = Order::notCompleted()->where('uuid', $uuid)->first();

        $attributes = $order->toArray();
        $attributes['line_items'] = $order->lineItems->toArray();

        $attributes['items_count'] = 0;
        collect($attributes['line_items'])
            ->each(function ($lineItem) use (&$attributes) {
                $attributes['items_count'] += $lineItem->quantity;
            });

        return collect($attributes);
    }

    public function make()
    {
        return Order::create([
            'uuid'  => (new Stache())->generateId(),
            'billing_address_id' => null,
            'shipping_address_id' => null,
            'gateway' => SimpleCommerce::gateways()[0]['class'],
            'customer_id' => null,
            'order_status_id' => OrderStatus::where('primary', true)->first()->id, // TODO: make this customisable later
            'item_total' => 00.00,
            'tax_total' => 00.00,
            'shipping_total' => 00.00,
            'total' => 00.00,
            'currency_id' => \DoubleThreeDigital\SimpleCommerce\Facades\Currency::primary()->id,
            'is_completed'  => false,
            'is_paid'       => false,
        ]);
    }

    public function update(string $uuid, array $attributes = [])
    {
        return Order::notCompleted()
            ->updateOrCreate([
                'uuid' => $uuid,
            ], $attributes);
    }

    public function addLineItem(string $uuid, string $variantUuid, int $quantity, string $note = '')
    {
        $variant = Variant::select('id', 'name', 'sku', 'price', 'max_quantity', 'product_id', 'weight')
            ->where('uuid', $variantUuid)
            ->first();

        if ($quantity > $variant->max_quantity) {
            throw new \Exception("You are not allowed to add more than {$variant->max_quantity} of this item.");
        }

        // TODO: need to get shipping zone so we can calculate the rate for the weight of the product

        return Order::notCompleted()
            ->where('uuid', $uuid)
            ->first()
            ->lineItems()
            ->create([
                'uuid'                  => (new Stache())->generateId(),
                'variant_id'            => $variant->id,
                'tax_rate_id'           => $variant->product->tax_rate_id,
                'shipping_rate_id'      => ShippingRate::first()->id,
                'description'           => $variant->name,
                'sku'                   => $variant->sku,
                'price'                 => $variant->price,
                'weight'                => $variant->weight,
                'total'                 => $variant->price, // price + shipping for item dimensions + tax rate
                'quantity'              => $quantity,
                'note'                  => $note,
            ]);
    }

    public function calculateTotals(Order $order)
    {
        $totals = [
            'overall'   => 00.00,
            'items'     => 00.00,
            'tax'       => 00.00,
            'shipping'  => 00.00,
        ];

        $order
            ->lineItems
            ->each(function (LineItem $lineItem) use (&$totals) {
                $itemTotal = ($lineItem->price * $lineItem->quantity);

                if (! config('simple-commerce.entered_with_tax')) {
                    $taxTotal = ($lineItem->taxRate->rate / 100) * $itemTotal;
                } else {
                    $taxTotal = 00.00;
                }

                $shippingTotal = $lineItem->shippingRate->rate;
                $overallTotal = $itemTotal + $taxTotal + $shippingTotal;

                $lineItem->update([
                    'total' => $overallTotal,
                ]);

                $totals['overall'] += $overallTotal;
                $totals['items'] += $itemTotal;
                $totals['tax'] += $taxTotal;
                $totals['shipping'] += $shippingTotal;
            });

        $order->update([
            'total' => $totals['total'],
            'item_total' => $totals['item_total'],
            'tax_total' => $totals['tax_total'],
            'shipping_total' => $totals['shipping'],
        ]);
    }
}
