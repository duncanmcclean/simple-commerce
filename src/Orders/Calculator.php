<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderAPI;
use DoubleThreeDigital\SimpleCommerce\Facades\Product as ProductAPI;
use DoubleThreeDigital\SimpleCommerce\Facades\Shipping;
use DoubleThreeDigital\SimpleCommerce\Products\Product;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Site;

class Calculator
{
    protected $order;

    public function calculate(Order $order): array
    {
        if ($order->has('is_paid') && $order->get('is_paid') === true) {
            return $order->data();
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
            ->map(function ($lineItem) use (&$data) {
                $calculate = $this->calculateLineItem($data, $lineItem);

                $data      = $calculate['orderData'];
                $lineItem  = $calculate['lineItem'];

                return $lineItem;
            })
            ->map(function ($lineItem) use (&$data) {
                $calculate = $this->calculateLineItemTax($data, $lineItem);

                $data      = $calculate['orderData'];
                $lineItem  = $calculate['lineItem'];

                return $lineItem;
            })
            ->each(function ($lineItem) use (&$data) {
                $data['items_total'] += $lineItem['total'];
            })
            ->toArray();

        $data = $this->calculateOrderShipping($data)['orderData'];

        $data['grand_total'] = ($data['items_total'] + $data['shipping_total'] + $data['tax_total']);

        $data = $this->calculateOrderCoupons($data)['orderData'];

        return $data;
    }

    protected function calculateLineItem(array $orderData, array $lineItem): array
    {
        $product = ProductAPI::find($lineItem['product']);

        if ($product->purchasableType() === 'variants') {
            $productPrice = $product->variantOption(
                isset($item['variant']['variant']) ? $item['variant']['variant'] : $lineItem['variant']
            )['price'];

            // Ensure we strip any decimals from price
            $productPrice = (int) str_replace('.', '', (string) $productPrice);

            $lineItem['total'] = ($productPrice * $lineItem['quantity']);

            return [
                'orderData' => $orderData,
                'lineItem'  => $lineItem,
            ];
        }

        $productPrice = $product->get('price');

        // Ensure we strip any decimals from price
        $productPrice = (int) str_replace('.', '', (string) $productPrice);

        $lineItem['total'] = ($productPrice * $lineItem['quantity']);

        return [
            'orderData' => $orderData,
            'lineItem'  => $lineItem,
        ];
    }

    protected function calculateLineItemTax(array $orderData, array $lineItem): array
    {
        $product = ProductAPI::find($lineItem['product']);

        $taxConfiguration = collect(Config::get('simple-commerce.sites'))
            ->get(Site::current()->handle())['tax'];

        if ($product->isExemptFromTax()) {
            return [
                'orderData' => $orderData,
                'lineItem'  => $lineItem,
            ];
        }

        $itemTotal = $lineItem['total'];
        $taxAmount = ($itemTotal / 100) * ($taxConfiguration['rate'] / (100 + $taxConfiguration['rate']));

        if ($taxConfiguration['included_in_prices']) {
            $itemTax = str_replace(
                '.',
                '',
                round(
                    $taxAmount,
                    2
                )
            );

            $lineItem['total'] -= $itemTax;
            $orderData['tax_total'] += $itemTax;
        } else {
            $orderData['tax_total'] += (int) str_replace(
                '.',
                '',
                round(
                    $taxAmount,
                    2
                )
            );
        }

        return [
            'orderData' => $orderData,
            'lineItem'  => $lineItem,
        ];
    }

    protected function calculateOrderShipping(array $orderData): array
    {
        if (! $this->order->has('shipping_method')) {
            return [
                'orderData' => $orderData,
            ];
        }

        $orderData['shipping_total'] = Shipping::use($this->order->data['shipping_method'])->calculateCost($this->order->entry());

        return [
            'orderData' => $orderData,
        ];
    }

    protected function calculateOrderCoupons(array $orderData): array
    {
        if (isset($this->order->data['coupon']) && $this->order->data['coupon'] !== null) {
            $coupon = Coupon::find($this->order->data['coupon']);
            $value = (int) $coupon->data['value'];

            if ($coupon->data['type'] === 'percentage') {
                $orderData['coupon_total'] = (int) (($value * $orderData['items_total']) / 100);
            }

            if ($coupon->data['type'] === 'fixed') {
                $orderData['coupon_total'] = (int) ($orderData['items_total'] - $value);
            }

            $orderData['items_total'] = (int) str_replace('.', '', (string) ($orderData['items_total'] - $orderData['coupon_total']));
        }

        return [
            'orderData' => $orderData,
        ];
    }
}
