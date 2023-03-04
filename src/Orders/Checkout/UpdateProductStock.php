<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Checkout;

use Closure;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Events\StockRunningLow;
use DoubleThreeDigital\SimpleCommerce\Events\StockRunOut;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\LineItem;
use DoubleThreeDigital\SimpleCommerce\Products\EntryProductRepository;
use DoubleThreeDigital\SimpleCommerce\Products\ProductType;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

class UpdateProductStock
{
    public function handle(Order $order, Closure $next)
    {
        $order->lineItems()
            ->each(function (LineItem $item) {
                $product = $item->product();

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
                        $stock = $product->stock() - $item->quantity();

                        $product->stock(
                            $stock = $product->stock() - $item->quantity()
                        );

                        $product->save();

                        if ($stock <= 0) {
                            event(new StockRunOut(
                                product: $product,
                                variant: null,
                                stock: $stock,
                            ));
                        }

                        if ($stock <= config('simple-commerce.low_stock_threshold', 10)) {
                            event(new StockRunningLow(
                                product: $product,
                                variant: null,
                                stock: $stock,
                            ));
                        }
                    }
                }

                if ($product->purchasableType() === ProductType::Variant) {
                    $variant = $product->variant($item->variant()['variant'] ?? $item->variant());

                    if ($variant !== null && is_int($variant->stock())) {
                        $stock = $variant->stock() - $item->quantity();

                        $variant->stock(
                            $stock = $variant->stock() - $item->quantity()
                        );

                        $variant->save();

                        if ($stock <= 0) {
                            event(new StockRunOut(
                                product: $product,
                                variant: $variant,
                                stock: $stock,
                            ));
                        }

                        if ($stock <= config('simple-commerce.low_stock_threshold', 10)) {
                            event(new StockRunningLow(
                                product: $product,
                                variant: $variant,
                                stock: $stock,
                            ));
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
