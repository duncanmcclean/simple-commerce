<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

use DoubleThreeDigital\SimpleCommerce\Exceptions\InvalidCouponCode;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Coupon;
use DoubleThreeDigital\SimpleCommerce\Models\LineItem;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingRate;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Collection;
use Statamic\Stache\Stache;

class Cart
{
    /**
     * @return Collection
     */
    public function all(): Collection
    {
        return Order::notCompleted()->get();
    }

    /**
     * @param string $orderUuid
     * @return Collection|null
     */
    public function find(string $orderUuid): ?Collection
    {
        return collect(
            Order::notCompleted()
                ->where('uuid', $orderUuid)
                ->first()
                ->templatePrep()
        );
    }

    /**
     * @return mixed
     */
    public function make()
    {
        return Order::create([
            'uuid'  => (new Stache())->generateId(),
            'billing_address_id' => null,
            'shipping_address_id' => null,
            'gateway' => SimpleCommerce::gateways()[0]['class'],
            'customer_id' => null,
            'order_status_id' => OrderStatus::where('primary', true)->first()->id,
            'item_total' => 00.00,
            'tax_total' => 00.00,
            'shipping_total' => 00.00,
            'coupon_total' => 00.00,
            'total' => 00.00,
            'currency_id' => \DoubleThreeDigital\SimpleCommerce\Facades\Currency::get()['id'],
            'is_completed'  => false,
            'is_paid'       => false,
            'email' => null,
        ]);
    }

    /**
     * @param string $orderUuid
     * @param array $attributes
     * @return mixed
     */
    public function update(string $orderUuid, array $attributes = [])
    {
        return Order::notCompleted()
            ->updateOrCreate([
                'uuid' => $orderUuid,
            ], $attributes);
    }

    /**
     * @param string $orderUuid
     * @return mixed
     */
    public function clear(string $orderUuid)
    {
        return Order::notCompleted()
            ->where('uuid', $orderUuid)
            ->first()
            ->delete();
    }

    /**
     * @param string $orderUuid
     * @param string $variantUuid
     * @param int $quantity
     * @param string $note
     * @return mixed
     */
    public function addLineItem(string $orderUuid, string $variantUuid, int $quantity, string $note = '')
    {
        $variant = Variant::select('id', 'name', 'sku', 'price', 'max_quantity', 'product_id', 'weight')
            ->where('uuid', $variantUuid)
            ->first();

        if ($lineItem = Order::notCompleted()->where('uuid', $orderUuid)->first()->lineItems()->where('variant_id', $variant->id)->first()) {
            if ($quantity >= $variant->max_quantity) {
                $lineItem->update([
                    'quantity' => $variant->max_quantity,
                ]);

                return $lineItem->recalculate();
            }

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

    /**
     * @param string $orderUuid
     * @param string $itemUuid
     * @param array $updateOptions
     */
    public function updateLineItem(string $orderUuid, string $itemUuid, array $updateOptions)
    {
        $lineItem = LineItem::where('uuid', $itemUuid)->first();

        $lineItem->update($updateOptions);
        $lineItem->recalculate();
    }

    /**
     * @param string $orderUuid
     * @param string $itemUuid
     * @return mixed
     */
    public function removeLineItem(string $orderUuid, string $itemUuid)
    {
        LineItem::where('uuid', $itemUuid)->get()->each(function ($item) {
            $item->delete();
        });

        return Order::where('uuid', $orderUuid)->first()->recalculate();
    }

    /**
     * @param string $orderUuid
     * @param string $couponCode
     * @return bool
     * @throws InvalidCouponCode
     */
    public function redeemCoupon(string $orderUuid, string $couponCode): bool
    {
        $order = Order::notCompleted()->where('uuid', $orderUuid)->first();
        $coupon = Coupon::where('code', $couponCode)->first();

        if (! $coupon) {
            throw new InvalidCouponCode();
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

    /**
     * @param Order $order
     */
    public function decideShipping(Order $order)
    {
        $zone = Country::find($order->shippingAddress->country_id)->shippingZone;

        if (! $zone) {
            $zone = null;

            foreach (ShippingZone::all() as $thisZone) {
                if ($thisZone->countries->count() === 0) {
                    $zone = $thisZone;
                }
            }

            if (! $zone) {
                return;
            }
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

    /**
     * @param Order $order
     */
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
                        case 'percent_discount':
                            $couponTotal = ($lineItem->coupon->value / 100) * ($itemTotal);
                            $itemTotal -= $couponTotal;
                            break;
                        case 'fixed_discount':
                            $couponTotal = $lineItem->coupon->value;
                            $itemTotal -= $lineItem->coupon->value;
                            break;
                        case 'free_shipping':
                            $couponTotal = $shippingTotal;
                            $shippingTotal = 00.00;
                            break;
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
