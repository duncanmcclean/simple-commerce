<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Products\ProductType;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;

class LineItemCalculator
{
    public function handle(Order $order, Closure $next)
    {
        $order->lineItems()
            ->transform(function (LineItem $lineItem) use ($order) {
                $product = $lineItem->product();

                if ($product->purchasableType() === ProductType::Variant) {
                    $variant = $product->variant(
                        isset($lineItem->variant()['variant']) ? $lineItem->variant()['variant'] : $lineItem->variant()
                    );

                    if (SimpleCommerce::$productVariantPriceHook) {
                        $productPrice = (SimpleCommerce::$productVariantPriceHook)($order, $product, $variant);
                    } else {
                        $productPrice = $variant->price();
                    }

                    // If $productPrice contains a decimal, we need to strip it & ensure we have two decimal places.
                    if (str_contains($productPrice, '.')) {
                        $productPrice = number_format($productPrice, 2, '.', '');
                        $productPrice = (int) str_replace('.', '', (string) $productPrice);
                    }

                    $lineItem->total(
                        $productPrice * $lineItem->quantity()
                    );
                }

                if ($product->purchasableType() === ProductType::Product) {
                    if (SimpleCommerce::$productPriceHook) {
                        $productPrice = (SimpleCommerce::$productPriceHook)($order, $product);
                    } else {
                        $productPrice = $product->price();
                    }

                    // If $productPrice contains a decimal, we need to strip it & ensure we have two decimal places.
                    if (str_contains($productPrice, '.')) {
                        $productPrice = number_format($productPrice, 2, '.', '');
                        $productPrice = (int) str_replace('.', '', (string) $productPrice);
                    }

                    $lineItem->total(
                        $productPrice * $lineItem->quantity()
                    );
                }

                return $lineItem;
            });

        return $next($order);
    }
}
