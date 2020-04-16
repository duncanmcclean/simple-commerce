<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
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

    public function find(int $id): ?Collection
    {
        $order = Order::notCompleted()->findOrFail($id);

        $attributes = $order->toArray();
        $attributes['lineItems'] = $order->lineItems;

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

    public function update(int $id, array $attributes = [])
    {
        return Order::notCompleted()
            ->updateOrCreate([
                'id' => $id,
            ], $attributes);
    }

    public function addLineItem(int $id, string $variantUuid, int $quantity, string $note = '')
    {
        $variant = Variant::select('id', 'name', 'sku', 'price', 'max_quantity', 'product_id')
            ->where('uuid', $variantUuid)
            ->first();

        if ($quantity > $variant->max_quantity) {
            throw new \Exception("You are not allowed to add more than {$variant->max_quantity} of this item.");
        }

        // TODO: need to get shipping zone so we can calculate the rate for the weight of the product
        // TODO: variants need weight field in the db
        // TODO: remove the other dimension fields from the database + model

        return Order::notCompleted()
            ->findOrFail($id)
            ->lineItems()
            ->create([
                'uuid'                  => (new Stache())->generateId(),
                'variant_id'            => $variant->id,
                'tax_rate_id'           => $variant->product->tax_rate_id,
                'shipping_rate_id'      => null,
                'description'           => $variant->name,
                'sku'                   => $variant->sku,
                'price'                 => $variant->price,
                'weight'                => null, // TODO: this field needs added to the variants model
                'height'                => null, // TODO: this field needs added to the variants model
                'length'                => null, // TODO: this field needs added to the variants model
                'width'                 => null, // TODO: this field needs added to the variants model
                'total'                 => $variant->price, // price + shipping for item dimensions + tax rate
                'quantity'              => $quantity,
                'note'                  => $note,
            ]);
    }

    public function calculateTotals(Order $order)
    {
        dd($order);
    }
}
