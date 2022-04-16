<?php

namespace DoubleThreeDigital\SimpleCommerce\Checkout;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Events\StockRunningLow;
use DoubleThreeDigital\SimpleCommerce\Events\StockRunOut;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CheckoutProductHasNoStockException;
use DoubleThreeDigital\SimpleCommerce\Orders\LineItem;
use DoubleThreeDigital\SimpleCommerce\Products\ProductType;

trait HandleStock
{
    public function handleStock(Order $order): self
    {
        $order->lineItems()
            ->each(function (LineItem $item) {
                $product = $item->product();

                if ($product->purchasableType() === ProductType::PRODUCT()) {
                    if (is_int($product->stock())) {
                        $stock = $product->stock() - $item->quantity();

                        // Need to do this check before actually setting the stock
                        if ($stock < 0) {
                            event(new StockRunOut($product, $stock));

                            throw new CheckoutProductHasNoStockException($product);
                        }

                        $product->stock(
                            $stock = $product->stock() - $item->quantity()
                        );

                        $product->save();

                        if ($stock <= config('simple-commerce.low_stock_threshold', 10)) {
                            event(new StockRunningLow($product, $stock));
                        }
                    }
                }

                if ($product->purchasableType() === ProductType::VARIANT()) {
                    $variant = $product->variant($item->variant()['variant'] ?? $item->variant());

                    if ($variant !== null && $variant->stock() !== null) {
                        $stock = $variant->stock() - $item->quantity();

                        // Need to do this check before actually setting the stock
                        if ($stock < 0) {
                            event(new StockRunOut($product, $stock, $variant));

                            throw new CheckoutProductHasNoStockException($product, $variant);
                        }

                        $variant->stock(
                            $stock = $variant->stock() - $item->quantity()
                        );

                        $variant->save();

                        if ($stock <= config('simple-commerce.low_stock_threshold', 10)) {
                            event(new StockRunningLow($product, $stock));
                        }
                    }
                }
            });

        return $this;
    }
}
