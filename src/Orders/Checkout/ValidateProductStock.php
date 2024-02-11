<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Checkout;

use Closure;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Exceptions\CheckoutProductHasNoStockException;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Products\EntryProductRepository;
use DuncanMcClean\SimpleCommerce\Products\ProductType;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;

class ValidateProductStock
{
    public function handle(Order $order, Closure $next)
    {
        $order->lineItems()
            ->each(function (LineItem $lineItem) use (&$order) {
                $product = $lineItem->product();

                // Multi-site: Is the Stock field not localised? If so, we want the origin
                // version of the product for stock purposes.
                if (
                    $this->isOrExtendsClass(SimpleCommerce::productDriver()['repository'], EntryProductRepository::class)
                    && $product->resource()->hasOrigin()
                    && $product->resource()->blueprint()->hasField('stock')
                    && ! $product->resource()->blueprint()->field('stock')->isLocalizable()
                ) {
                    $product = Product::find($product->resource()->origin()->id());
                }

                if ($product->purchasableType() === ProductType::Product) {
                    if (is_int($product->stock())) {
                        $stock = $product->stock() - $lineItem->quantity();

                        if ($stock < 0) {
                            $order->removeLineItem($lineItem->id());
                            $order->save();

                            throw new CheckoutProductHasNoStockException(
                                __("Checkout failed. One or more products in your cart don't have enough stock to complete your order. The product(s) have been removed from your cart."),
                                $product
                            );
                        }
                    }
                }

                if ($product->purchasableType() === ProductType::Variant) {
                    $variant = $product->variant($lineItem->variant()['variant'] ?? $lineItem->variant());

                    if ($variant !== null && is_int($variant->stock())) {
                        $stock = $variant->stock() - $lineItem->quantity();

                        if ($stock < 0) {
                            $order->removeLineItem($lineItem->id());
                            $order->save();

                            throw new CheckoutProductHasNoStockException(
                                __("Checkout failed. One or more products in your cart don't have enough stock to complete your order. The product(s) have been removed from your cart."),
                                $product,
                                $variant
                            );
                        }
                    }
                }
            });

        return $next($order);
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
