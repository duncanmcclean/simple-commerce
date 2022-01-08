<?php

namespace DoubleThreeDigital\SimpleCommerce\Checkout;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Events\StockRunningLow;
use DoubleThreeDigital\SimpleCommerce\Events\StockRunOut;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CheckoutProductHasNoStockException;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Products\ProductType;

trait HandleStock
{
    public function handleStock(Order $order): self
    {
        $order->lineItems()
            ->each(function ($item) {
                $product = Product::find($item['product']);

                if ($product->purchasableType() === ProductType::PRODUCT()) {
                    if ($product->has('stock') && $product->get('stock') !== null) {
                        $stockCount = $product->get('stock') - $item['quantity'];

                        // Need to do this check before actually setting the stock
                        if ($stockCount < 0) {
                            event(new StockRunOut($product, $stockCount));

                            throw new CheckoutProductHasNoStockException($product);
                        }

                        $product->set(
                            'stock',
                            $stockCount = $product->get('stock') - $item['quantity']
                        )->save();

                        if ($stockCount <= config('simple-commerce.low_stock_threshold')) {
                            event(new StockRunningLow($product, $stockCount));
                        }
                    }
                }

                if ($product->purchasableType() === ProductType::VARIANT()) {
                    $variant = $product->variant($item['variant']['variant'] ?? $item['variant']);

                    if ($variant !== null && $variant->stockCount() !== null) {
                        $stockCount = $variant->stockCount() - $item['quantity'];

                        // Need to do this check before actually setting the stock
                        if ($stockCount < 0) {
                            event(new StockRunOut($product, $stockCount, $variant));

                            throw new CheckoutProductHasNoStockException($product, $variant);
                        }

                        $variant->set(
                            'stock',
                            $stockCount = $variant->stockCount() - $item['quantity']
                        );

                        if ($stockCount <= config('simple-commerce.low_stock_threshold')) {
                            event(new StockRunningLow($product, $stockCount));
                        }
                    }
                }
            });

        return $this;
    }
}
