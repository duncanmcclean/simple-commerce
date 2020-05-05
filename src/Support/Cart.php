<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

use DoubleThreeDigital\SimpleCommerce\Models\Attribute;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Coupon;
use DoubleThreeDigital\SimpleCommerce\Models\LineItem;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingRate;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use ErrorException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Statamic\Stache\Stache;

class Cart
{
    public function all(): Collection
    {
        return Order::notCompleted()->get();
    }

    public function find(string $orderUuid): ?Collection
    {
        $order = Order::notCompleted()->where('uuid', $orderUuid)->first();

        $attributes = $order->toArray();
        $attributes['line_items'] = $order->lineItems->map(function ($lineItem) {
            $attributes = $lineItem->toArray();

            $attributes['variant'] = $lineItem->variant->toArray();
            $attributes['product'] = Arr::except($lineItem->variant->product->toArray(), 'variants');

            collect($lineItem->variant->attributes)
                ->each(function (Attribute $attribute) use (&$attributes) {
                    $attributes['variant']["$attribute->key"] = $attribute->value;
                });

            collect($lineItem->variant->product->attributes)
                ->each(function (Attribute $attribute) use (&$attributes) {
                    $attributes['product']["$attribute->key"] = $attribute->value;
                });

            return $attributes;
        })->toArray();

        $attributes['items_count'] = 0;
        collect($attributes['line_items'])
            ->each(function ($lineItem) use (&$attributes) {
                $attributes['items_count'] += $lineItem['quantity'];
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
            'coupon_total' => 00.00,
            'total' => 00.00,
            'currency_id' => \DoubleThreeDigital\SimpleCommerce\Facades\Currency::primary()->id,
            'is_completed'  => false,
            'is_paid'       => false,
        ]);
    }

    public function update(string $orderUuid, array $attributes = [])
    {
        return Order::notCompleted()
            ->updateOrCreate([
                'uuid' => $orderUuid,
            ], $attributes);
    }

    public function clear(string $orderUuid)
    {
        return Order::notCompleted()
            ->where('uuid', $orderUuid)
            ->first()
            ->delete();
    }

    public function addLineItem(string $orderUuid, string $variantUuid, int $quantity, string $note = '')
    {
        $variant = Variant::select('id', 'name', 'sku', 'price', 'max_quantity', 'product_id', 'weight')
            ->where('uuid', $variantUuid)
            ->first();

        if ($quantity > $variant->max_quantity) {
            throw new \Exception("You are not allowed to add more than {$variant->max_quantity} of this item.");
        }

        if ($lineItem = Order::notCompleted()->where('uuid', $orderUuid)->first()->lineItems()->where('variant_id', $variant->id)->first()) {
            $lineItem->update([
                'quantity' => $lineItem->quantity + $quantity,
            ]);

            return $lineItem->recalculate();
        }

        return Order::notCompleted()
            ->where('uuid', $orderUuid)
            ->first()
            ->lineItems()
            ->create([
                'uuid'                  => (new Stache())->generateId(),
                'variant_id'            => $variant->id,
                'tax_rate_id'           => $variant->product->tax_rate_id,
                'shipping_rate_id'      => null,
                'coupon_id'             => null,
                'description'           => $variant->name,
                'sku'                   => $variant->sku,
                'price'                 => $variant->price,
                'weight'                => $variant->weight,
                'total'                 => $variant->price, // price + shipping for item dimensions + tax rate
                'quantity'              => $quantity,
                'note'                  => $note,
            ])
            ->recalculate();
    }

    public function updateLineItem(string $orderUuid, string $itemUuid, array $updateOptions)
    {
        $lineItem = LineItem::where('uuid', $itemUuid)->first();

        $lineItem->update($updateOptions);
        $lineItem->recalculate();
    }

    public function removeLineItem(string $orderUuid, string $itemUuid)
    {
        LineItem::where('uuid', $itemUuid)->get()->each(function ($item) {
            $item->delete();
        });

        return Order::where('uuid', $orderUuid)->first()->recalculate();
    }

    public function redeemCoupon(string $orderUuid, string $couponCode): bool
    {   
        $order = Order::notCompleted()->where('uuid', $orderUuid)->first();
        $coupon = Coupon::where('code', $couponCode)->first();

        if (! $coupon) {
            throw new ErrorException('The coupon code provided does not exist.');
        }

        if (! $coupon->isActive()) {
            return 'The coupon provided is not active.';
        }

        if ($coupon->minimum_total) {
            if ($order->total < $coupon->minimum_total) {
                $amount = Currency::parse($coupon->minimum_total);
                return "The coupon provided can only be used when the minimum cart total is {$amount}";
            }
        }

        $order
            ->lineItems
            ->each(function ($lineItem) use ($coupon) {
                $lineItem->update(['coupon_id' => $coupon->id]);
            });

        $order->recalculate();

        return false;
    }

    public function decideShipping(Order $order)
    {
        $zone = Country::find($order->shippingAddress->country_id)->shippingZone;

        if (! $zone) {
            return ;
        }

        $order
            ->lineItems
            ->reject(function (LineItem $lineItem) {
                if ($lineItem->variant->product->needs_shipping) {
                    return false;
                }

                return true;
            })
            ->each(function (LineItem $lineItem) use (&$zone) {
                $complete = false;

                collect($zone->rates)
                    ->where('type', 'weight-based')
                    ->each(function (ShippingRate $rate) use (&$lineItem, &$complete) {
                        $weight = $lineItem->variant->weight;

                        if ($weight >= $rate->minimum && $weight <= $rate->maximum) {
                            $lineItem->update([
                                'shipping_rate_id' => $rate->id,
                            ]);

                            $complete = true;
                        }
                    });

                if (! $complete) {
                    collect($zone->rates)
                        ->where('type', 'price-based')
                        ->each(function (ShippingRate $rate) use (&$lineItem, &$complete) {
                            $price = $lineItem->variant->price;

                            if ($price >= $rate->minimum && $price <= $rate->maximum) {
                                $lineItem->update([
                                    'shipping_rate_id' => $rate->id,
                                ]);
    
                                $complete = true;
                            }
                        });
                }     
            });
    }

    public function calculateTotals(Order $order)
    {
        $totals = [
            'total'             => 00.00,
            'item_total'        => 00.00,
            'tax_total'         => 00.00,
            'shipping_total'    => 00.00,
            'coupon_total'      => 00.00,
        ];

        $order
            ->lineItems
            ->each(function (LineItem $lineItem) use (&$totals) {
                $itemTotal = ($lineItem->price * $lineItem->quantity);

                if ($lineItem->variant->product->needs_shipping && $lineItem->shipping_rate_id) {
                    $shippingTotal = $lineItem->shippingRate->rate;
                } else {
                    $shippingTotal = 00.00;
                }

                if ($lineItem->coupon) {
                    switch ($lineItem->coupon->type) {
                        case 'percent_discount';
                            $couponTotal = ($lineItem->coupon->value / 100) * ($itemTotal);
                            $itemTotal -= $couponTotal;
                        case 'fixed_discount':
                            $couponTotal = $lineItem->coupon->value;
                            $itemTotal -= $lineItem->coupon->value;
                        case 'free_shipping':
                            $couponTotal = $shippingTotal;
                            $shippingTotal = 00.00;
                    }
                }

                if (! config('simple-commerce.entered_with_tax')) {
                    $taxTotal = ($lineItem->taxRate->rate / 100) * ($itemTotal + $shippingTotal);
                } else {
                    $taxTotal = 00.00;
                }

                $overallTotal = $itemTotal + $taxTotal + $shippingTotal;

                $lineItem->update([
                    'total' => $itemTotal,
                ]);

                $totals['total'] += $overallTotal;
                $totals['item_total'] += $itemTotal;
                $totals['tax_total'] += $taxTotal;
                $totals['shipping_total'] += $shippingTotal;
                $totals['coupon_total'] += $couponTotal ?? 00.00;
            });

        $order->update($totals);
    }
}
