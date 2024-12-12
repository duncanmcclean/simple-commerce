<?php

namespace DuncanMcClean\SimpleCommerce\Cart\Calculator;

use Closure;
use DuncanMcClean\SimpleCommerce\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Products\ProductType;
use Statamic\Support\Traits\Hookable;

class CalculateLineItems
{
    use Hookable;

    public function handle(Cart $cart, Closure $next)
    {
        $cart->lineItems()->map(function (LineItem $lineItem) use ($cart) {
            $product = $lineItem->product();

            $price = match ($product->type()) {
                ProductType::Product => $product->price(),
                ProductType::Variant => $product->variant($lineItem->variant()->key())->price(),
            };

            $lineItem->unitPrice($price);
            $lineItem->total($price * $lineItem->quantity());

            return $lineItem;
        });

        $cart->subTotal($cart->lineItems()->map->total()->sum());

        return $next($cart);
    }
}
