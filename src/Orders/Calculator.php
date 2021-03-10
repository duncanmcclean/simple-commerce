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

                $data      = $calculate['data'];
                $lineItem  = $calculate['lineItem'];

                return $lineItem;
            })
            ->map(function ($lineItem) use (&$data) {
                $calculate = $this->calculateLineItemTax($data, $lineItem);

                $data      = $calculate['data'];
                $lineItem  = $calculate['lineItem'];

                return $lineItem;
            })
            ->each(function ($lineItem) use (&$data) {
                $data['items_total'] += $lineItem['total'];
            })
            ->toArray();

        $data = $this->calculateOrderShipping($data)['data'];

        $data['grand_total'] = ($data['items_total'] + $data['shipping_total'] + $data['tax_total']);

        $data = $this->calculateOrderCoupons($data)['data'];

        return $data;
    }

    protected function calculateLineItem(array $data, array $lineItem): array
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
                'data' => $data,
                'lineItem'  => $lineItem,
            ];
        }

        $productPrice = $product->get('price');

        // Ensure we strip any decimals from price
        $productPrice = (int) str_replace('.', '', (string) $productPrice);

        $lineItem['total'] = ($productPrice * $lineItem['quantity']);

        return [
            'data' => $data,
            'lineItem'  => $lineItem,
        ];
    }

    protected function calculateLineItemTax(array $data, array $lineItem): array
    {
        $product = ProductAPI::find($lineItem['product']);

        $taxConfiguration = collect(Config::get('simple-commerce.sites'))
            ->get(Site::current()->handle())['tax'];

        if ($product->isExemptFromTax()) {
            return [
                'data' => $data,
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
            $data['tax_total'] += $itemTax;
        } else {
            $data['tax_total'] += (int) str_replace(
                '.',
                '',
                round(
                    $taxAmount,
                    2
                )
            );
        }

        return [
            'data' => $data,
            'lineItem'  => $lineItem,
        ];
    }

    protected function calculateOrderShipping(array $data): array
    {
        if (! $this->order->has('shipping_method')) {
            return [
                'data' => $data,
            ];
        }

        $data['shipping_total'] = Shipping::use($this->order->data['shipping_method'])->calculateCost($this->order->entry());

        return [
            'data' => $data,
        ];
    }

    protected function calculateOrderCoupons(array $data): array
    {
        if (isset($this->order->data['coupon']) && $this->order->data['coupon'] !== null) {
            $coupon = Coupon::find($this->order->data['coupon']);
            $value = (int) $coupon->data['value'];

            if ($coupon->data['type'] === 'percentage') {
                $data['coupon_total'] = (int) (($value * $data['items_total']) / 100);
            }

            if ($coupon->data['type'] === 'fixed') {
                $data['coupon_total'] = (int) ($data['items_total'] - $value);
            }

            $data['items_total'] = (int) str_replace('.', '', (string) ($data['items_total'] - $data['coupon_total']));
        }

        return [
            'data' => $data,
        ];
    }
}
