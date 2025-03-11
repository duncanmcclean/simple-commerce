<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Products\ProductType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait ValidatesStock
{
    protected function validateStock(Request $request, Cart $cart, ?LineItem $lineItem = null): void
    {
        $product = Product::find($request->product ?? $lineItem->product);
        $quantity = (int) $request->quantity ?? $lineItem->quantity();

        if (
            $product->isStandardProduct()
            && is_int($product->stock())
            && $quantity > $product->stock()
        ) {
            throw ValidationException::withMessages([
                'product' => __('This product is currently out of stock. Please try again later.'),
            ]);
        }

        if ($product->isVariantProduct()) {
            $variant = $product->variant($request->variant ?? $lineItem->variant);

            if (is_int($variant->stock()) && $quantity > $variant->stock()) {
                throw ValidationException::withMessages([
                    'variant' => __('This variant is currently out of stock. Please try again later.'),
                ]);
            }
        }
    }
}
