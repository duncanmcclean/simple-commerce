<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Products\ProductType;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;

class CalculateLineItems
{
    public function handle(Cart $cart, Closure $next)
    {
        $cart->lineItems()
            ->transform(function (LineItem $lineItem) use ($cart) {
                $product = $lineItem->product();

                if ($product->type() === ProductType::Product) {
                    $productPrice = $product->price();

                    // If $productPrice contains a decimal, we need to strip it & ensure we have two decimal places.
                    if (str_contains($productPrice, '.')) {
                        $productPrice = number_format($productPrice, 2, '.', '');
                        $productPrice = (int) str_replace('.', '', (string) $productPrice);
                    }

                    $lineItem->total(
                        $productPrice * $lineItem->quantity()
                    );
                }

                if ($product->type() === ProductType::Variant) {
                    $variant = $product->variant($lineItem->variant()->key());

                    $productPrice = $variant->price();

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

        $cart->subTotal($cart->lineItems()->map->total()->sum());

        return $next($cart);
    }
}
