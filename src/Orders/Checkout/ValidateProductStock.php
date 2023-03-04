<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Checkout;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CheckoutProductHasNoStockException;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\LineItem;
use DoubleThreeDigital\SimpleCommerce\Products\EntryProductRepository;
use DoubleThreeDigital\SimpleCommerce\Products\ProductType;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

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
