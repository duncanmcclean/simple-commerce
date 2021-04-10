<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Contracts\Calculator as Contract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Product as ProductAPI;
use DoubleThreeDigital\SimpleCommerce\Facades\Shipping;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Site;

class Calculator implements Contract
{
    /** @var \DoubleThreeDigital\SimpleCommerce\Contracts\Order $model */
    protected $order;

    public function calculate(OrderContract $order): array
    {
        if ($order->has('is_paid') && $order->get('is_paid') === true) {
            return $order->data()->toArray();
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

    public function calculateLineItem(array $data, array $lineItem): array
    {
        $product = ProductAPI::find($lineItem['product']);

        if ($product->purchasableType() === 'variants') {
            $productPrice = $product->variant(
                isset($lineItem['variant']['variant']) ? $lineItem['variant']['variant'] : $lineItem['variant']
            )->price();

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

    public function calculateLineItemTax(array $data, array $lineItem): array
    {
        $product = ProductAPI::find($lineItem['product']);

        $taxConfiguration = collect(Config::get('simple-commerce.sites'))
            ->get(Site::current()->handle())['tax'];

        $data['tax_rate'] = $taxConfiguration['rate'];

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

    public function calculateOrderShipping(array $data): array
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

    public function calculateOrderCoupons(array $data): array
    {
        if (isset($this->order->data['coupon']) && $this->order->data['coupon'] !== null) {
            $coupon = Coupon::find($this->order->data['coupon']);
            $value = (int) $coupon->data['value'];

            // Double check coupon is still valid
            if (! $coupon->isValid($this->order)) {
                return [
                    'data' => $data,
                ];
            }

            // Otherwise do all the other stuff...
            if ($coupon->data['type'] === 'percentage') {
                $data['coupon_total'] = (int) (($value * $data['grand_total']) / 100);
            }

            if ($coupon->data['type'] === 'fixed') {
                $data['coupon_total'] = (int) $data['grand_total'] - ($data['grand_total'] - $value);
            }

            $data['grand_total'] = (int) str_replace('.', '', (string) ($data['grand_total'] - $data['coupon_total']));
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
