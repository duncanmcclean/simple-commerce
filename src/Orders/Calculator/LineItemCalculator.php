<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Calculator;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Orders\LineItem;
use DoubleThreeDigital\SimpleCommerce\Products\ProductType;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

class LineItemCalculator
{
    public function handle(OrderCalculation $orderCalculation, Closure $next)
    {
        $orderCalculation->order->lineItems()
            ->transform(function (LineItem $lineItem) use ($orderCalculation) {
                $product = $lineItem->product();

                if ($product->purchasableType() === ProductType::Variant) {
                    $variant = $product->variant($lineItem->variant());

                    if (SimpleCommerce::$productVariantPriceHook) {
                        $productPrice = (SimpleCommerce::$productVariantPriceHook)($orderCalculation->order, $product, $variant);
                    } else {
                        $productPrice = $variant->price();
                    }

                    // Ensure we strip any decimals from price
                    $productPrice = (int) str_replace('.', '', (string) $productPrice);

                    $lineItem->total(
                        $productPrice * $lineItem->quantity()
                    );
                }

                if ($product->purchasableType() === ProductType::Product) {
                    if (SimpleCommerce::$productPriceHook) {
                        $productPrice = (SimpleCommerce::$productPriceHook)($orderCalculation->order, $product);
                    } else {
                        $productPrice = $product->price();
                    }

                    // Ensure we strip any decimals from price
                    $productPrice = (int) str_replace('.', '', (string) $productPrice);

                    $lineItem->total(
                        $productPrice * $lineItem->quantity()
                    );
                }

                return $lineItem;
            });

        return $next($orderCalculation);
    }
}
