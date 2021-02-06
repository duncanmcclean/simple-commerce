<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Facades\Shipping;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Site;

class Calculator
{
    public function calculate($order)
    {
        if (isset($order->data['is_paid']) && $order->data['is_paid'] === true) {
            return $order;
        }

        $data = [
            'grand_total'       => 0000,
            'items_total'       => 0000,
            'shipping_total'    => 0000,
            'tax_total'         => 0000,
            'coupon_total'      => 0000,
        ];

        $data['items'] = collect($order->get('items'))
            ->map(function ($item) use (&$data) {
                $product = Product::find($item['product']);

                $siteTax = collect(Config::get('simple-commerce.sites'))
                    ->get(Site::current()->handle())['tax'];

                if ($product->purchasableType() === 'variants') {
                    $productPrice = $product->variantOption(
                        isset($item['variant']['variant']) ? $item['variant']['variant'] : $item['variant']
                    )['price'];

                    $itemTotal = ($productPrice * $item['quantity']);
                } else {
                    $itemTotal = ($product->data['price'] * $item['quantity']);
                }

                if (! $product->isExemptFromTax()) {
                    if ($siteTax['included_in_prices']) {
                        $itemTax = str_replace(
                            '.',
                            '',
                            round(
                                ((float) substr_replace($itemTotal, '.', -2, 0) / ($siteTax['rate'] + 100)) * $siteTax['rate'],
                                2
                            )
                        );
                        $itemTotal -= $itemTax;
                        $data['tax_total'] += $itemTax;
                    } else {
                        $data['tax_total'] += (int) str_replace(
                            '.',
                            '',
                            round(
                                ((float) substr_replace($itemTotal, '.', -2, 0) / 100) * $siteTax['rate'],
                                2
                            ) * 100
                        );
                    }
                }

                $data['items_total'] += $itemTotal;

                return array_merge($item, [
                    'total' => $itemTotal,
                ]);
            })
            ->toArray();

        if (isset($order->data['shipping_method'])) {
            $data['shipping_total'] = Shipping::use($order->data['shipping_method'])->calculateCost($order->entry());
        }

        $data['grand_total'] = ($data['items_total'] + $data['shipping_total'] + $data['tax_total']);

        if (isset($order->data['coupon']) && $order->data['coupon'] !== null) {
            $coupon = Coupon::find($order->data['coupon']);
            $value = (int) $coupon->data['value'];

            if ($coupon->data['type'] === 'percentage') {
                $data['coupon_total'] = (int) (($value *  $data['items_total']) / 100);
            }

            if ($coupon->data['type'] === 'fixed') {
                $data['coupon_total'] = (int) ($data['items_total'] - $value);
            }

            $data['items_total'] = str_replace('.', '', (string) ($data['items_total'] - $data['coupon_total']));
        }

        return $data;
    }
}
