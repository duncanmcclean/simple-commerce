<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Calculator as Contract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Coupons\CouponType;
use DoubleThreeDigital\SimpleCommerce\Facades\Product as ProductAPI;
use DoubleThreeDigital\SimpleCommerce\Facades\Shipping;
use DoubleThreeDigital\SimpleCommerce\Products\ProductType;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Site;

class Calculator implements Contract
{
    /** @var \DoubleThreeDigital\SimpleCommerce\Contracts\Order */
    protected $order;

    public function calculate(OrderContract $order): array
    {
        if ($order->isPaid()) {
            return $order->data()->merge([
                'items' => $order->lineItems()->toArray(),
                'grand_total' => $order->grandTotal(),
                'items_total' => $order->itemsTotal(),
                'tax_total' => $order->taxTotal(),
                'shipping_total' => $order->shippingTotal(),
                'coupon_total' => $order->couponTotal(),
            ])->toArray();
        }

        $this->order = $order;

        $data = [
            'grand_total'    => 0,
            'items_total'    => 0,
            'shipping_total' => 0,
            'tax_total'      => 0,
            'coupon_total'   => 0,
        ];

        $data['items'] = $order
            ->lineItems()
            ->map(function ($lineItem) {
                return $lineItem->toArray();
            })
            ->map(function ($lineItem) use (&$data) {
                $calculate = $this->calculateLineItem($data, $lineItem);

                $data = $calculate['data'];
                $lineItem = $calculate['lineItem'];

                return $lineItem;
            })
            ->map(function ($lineItem) use (&$data) {
                $calculate = $this->calculateLineItemTax($data, $lineItem);

                $data = $calculate['data'];
                $lineItem = $calculate['lineItem'];

                return $lineItem;
            })
            ->each(function ($lineItem) use (&$data) {
                $data['items_total'] += $lineItem['total'];
            })
            ->toArray();

        $data = $this->calculateOrderCoupons($data)['data'];

        $data = $this->calculateOrderShipping($data)['data'];

        $data['grand_total'] = (($data['items_total'] + $data['tax_total']) - $data['coupon_total']) + $data['shipping_total'];
        $data['grand_total'] = (int) round($data['grand_total']);

        return $data;
    }

    public function calculateLineItem(array $data, array $lineItem): array
    {
        $product = ProductAPI::find($lineItem['product']);

        if ($product->purchasableType() === ProductType::VARIANT()) {
            $variant = $product->variant(
                isset($lineItem['variant']['variant']) ? $lineItem['variant']['variant'] : $lineItem['variant']
            );

            if (SimpleCommerce::$productVariantPriceHook) {
                $productPrice = (SimpleCommerce::$productVariantPriceHook)($this->order, $product, $variant);
            } else {
                $productPrice = $variant->price();
            }

            // Ensure we strip any decimals from price
            $productPrice = (int) str_replace('.', '', (string) $productPrice);

            $lineItem['total'] = ($productPrice * $lineItem['quantity']);

            return [
                'data' => $data,
                'lineItem'  => $lineItem,
            ];
        }

        if (SimpleCommerce::$productPriceHook) {
            $productPrice = (SimpleCommerce::$productPriceHook)($this->order, $product);
        } else {
            $productPrice = $product->price();
        }

        // Ensure we strip any decimals from price
        $productPrice = (int) str_replace('.', '', (string) $productPrice);

        $lineItem['total'] = ($productPrice * $lineItem['quantity']);

        return [
            'data' => $data,
            'lineItem'  => $lineItem,
        ];
    }

    public function calculateLineItemTax(array $data, array $lineItem): array
    {
        $taxEngine = SimpleCommerce::taxEngine();
        $taxCalculation = $taxEngine->calculate($this->order, $lineItem);

        $lineItem['tax'] = $taxCalculation->toArray();

        if ($taxCalculation->priceIncludesTax()) {
            $lineItem['total'] -= $taxCalculation->amount();
            $data['tax_total'] += $taxCalculation->amount();
        } else {
            $data['tax_total'] += $taxCalculation->amount();
        }

        return [
            'data' => $data,
            'lineItem'  => $lineItem,
        ];
    }

    public function calculateOrderShipping(array $data): array
    {
        $shippingMethod = $this->order->get('shipping_method');
        $defaultShippingMethod = config('simple-commerce.sites.' . Site::current()->handle() . '.shipping.default_method');

        if (! $shippingMethod && ! $defaultShippingMethod) {
            return [
                'data' => $data,
            ];
        }

        $data['shipping_total'] = Shipping::site(Site::current()->handle())
            ->use($shippingMethod ?? $defaultShippingMethod)
            ->calculateCost($this->order);

        return [
            'data' => $data,
        ];
    }

    public function calculateOrderCoupons(array $data): array
    {
        if ($coupon = $this->order->coupon()) {
            $value = (int) $coupon->value();

            // Double check coupon is still valid
            if (! $coupon->isValid($this->order)) {
                return [
                    'data' => $data,
                ];
            }

            $baseAmount = $data['items_total'] + $data['tax_total'];

            // Otherwise do all the other stuff...
            if ($coupon->type() === CouponType::PERCENTAGE()) {
                $data['coupon_total'] = (int) ($value * $baseAmount) / 100;
            }

            if ($coupon->type() === CouponType::FIXED()) {
                $data['coupon_total'] = (int) $baseAmount - ($baseAmount - $value);
            }

            $data['coupon_total'] = (int) round($data['coupon_total']);
        }

        return [
            'data' => $data,
        ];
    }

    public static function bindings(): array
    {
        return [];
    }
}
