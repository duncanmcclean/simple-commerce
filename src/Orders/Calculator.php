<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Coupons\CouponType;
use DoubleThreeDigital\SimpleCommerce\Facades\Product as ProductAPI;
use DoubleThreeDigital\SimpleCommerce\Facades\Shipping;
use DoubleThreeDigital\SimpleCommerce\Products\ProductType;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Site;

class Calculator
{
    /** @var \DoubleThreeDigital\SimpleCommerce\Contracts\Order */
    protected static $order;

    public static function calculate(OrderContract $order): array
    {
        if ($order->paymentStatus()->is(PaymentStatus::Paid)) {
            return $order->data()->merge([
                'items' => $order->lineItems()->toArray(),
                'grand_total' => $order->grandTotal(),
                'items_total' => $order->itemsTotal(),
                'tax_total' => $order->taxTotal(),
                'shipping_total' => $order->shippingTotal(),
                'coupon_total' => $order->couponTotal(),
            ])->toArray();
        }

        static::$order = $order;

        $data = [
            'grand_total' => 0,
            'items_total' => 0,
            'shipping_total' => 0,
            'tax_total' => 0,
            'coupon_total' => 0,
        ];

        $data['items'] = $order
            ->lineItems()
            ->map(function ($lineItem) {
                return $lineItem->toArray();
            })
            ->map(function ($lineItem) use (&$data) {
                $calculate = static::calculateLineItem($data, $lineItem);

                $data = $calculate['data'];
                $lineItem = $calculate['lineItem'];

                return $lineItem;
            })
            ->map(function ($lineItem) use (&$data) {
                $calculate = static::calculateLineItemTax($data, $lineItem);

                $data = $calculate['data'];
                $lineItem = $calculate['lineItem'];

                return $lineItem;
            })
            ->each(function ($lineItem) use (&$data) {
                $data['items_total'] += $lineItem['total'];
            })
            ->toArray();

        $data = static::calculateOrderCoupons($data)['data'];

        $data = static::calculateOrderShipping($data)['data'];

        $data['grand_total'] = (($data['items_total'] + $data['tax_total']) - $data['coupon_total']) + $data['shipping_total'];
        $data['grand_total'] = (int) round($data['grand_total']);

        return $data;
    }

    public static function calculateLineItem(array $data, array $lineItem): array
    {
        $product = ProductAPI::find($lineItem['product']);

        if ($product->purchasableType() === ProductType::Variant) {
            $variant = $product->variant(
                isset($lineItem['variant']['variant']) ? $lineItem['variant']['variant'] : $lineItem['variant']
            );

            if (SimpleCommerce::$productVariantPriceHook) {
                $productPrice = (SimpleCommerce::$productVariantPriceHook)(static::$order, $product, $variant);
            } else {
                $productPrice = $variant->price();
            }

            // Ensure we strip any decimals from price
            $productPrice = (int) str_replace('.', '', (string) $productPrice);

            $lineItem['total'] = ($productPrice * $lineItem['quantity']);

            return [
                'data' => $data,
                'lineItem' => $lineItem,
            ];
        }

        if (SimpleCommerce::$productPriceHook) {
            $productPrice = (SimpleCommerce::$productPriceHook)(static::$order, $product);
        } else {
            $productPrice = $product->price();
        }

        // Ensure we strip any decimals from price
        $productPrice = (int) str_replace('.', '', (string) $productPrice);

        $lineItem['total'] = ($productPrice * $lineItem['quantity']);

        return [
            'data' => $data,
            'lineItem' => $lineItem,
        ];
    }

    public static function calculateLineItemTax(array $data, array $lineItem): array
    {
        $taxEngine = SimpleCommerce::taxEngine();
        $taxCalculation = $taxEngine->calculate(static::$order, $lineItem);

        $lineItem['tax'] = $taxCalculation->toArray();

        if ($taxCalculation->priceIncludesTax()) {
            $lineItem['total'] -= $taxCalculation->amount();
            $data['tax_total'] += $taxCalculation->amount();
        } else {
            $data['tax_total'] += $taxCalculation->amount();
        }

        return [
            'data' => $data,
            'lineItem' => $lineItem,
        ];
    }

    public static function calculateOrderShipping(array $data): array
    {
        $shippingMethod = static::$order->get('shipping_method');
        $defaultShippingMethod = config('simple-commerce.sites.'.Site::current()->handle().'.shipping.default_method');

        if (! $shippingMethod && ! $defaultShippingMethod) {
            return [
                'data' => $data,
            ];
        }

        $data['shipping_total'] = Shipping::site(Site::current()->handle())
            ->use($shippingMethod ?? $defaultShippingMethod)
            ->calculateCost(static::$order);

        return [
            'data' => $data,
        ];
    }

    public static function calculateOrderCoupons(array $data): array
    {
        if ($coupon = static::$order->coupon()) {
            $value = (int) $coupon->value();

            // Double check coupon is still valid
            if (! $coupon->isValid(static::$order)) {
                return [
                    'data' => $data,
                ];
            }

            $baseAmount = $data['items_total'] + $data['tax_total'];

            // Otherwise do all the other stuff...
            if ($coupon->type() === CouponType::Percentage) {
                $data['coupon_total'] = (int) ($value * $baseAmount) / 100;
            }

            if ($coupon->type() === CouponType::Fixed) {
                $data['coupon_total'] = (int) $baseAmount - ($baseAmount - $value);
            }

            $data['coupon_total'] = (int) round($data['coupon_total']);
        }

        return [
            'data' => $data,
        ];
    }
}
