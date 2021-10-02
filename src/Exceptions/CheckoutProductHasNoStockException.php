<?php

namespace DoubleThreeDigital\SimpleCommerce\Exceptions;

use DoubleThreeDigital\SimpleCommerce\Contracts\Product;

class CheckoutProductHasNoStockException extends \Exception
{
    public $product;
    public $variant;

    public function __construct(Product $product, $variant = null)
    {
        $this->product = $product;
        $this->variant = $variant;

        if ($product->purchasableType() === 'product') {
            $message = "Product [{$product->id()}] does not have any available stock.";
        }

        if ($product->purchasableType() === 'variants') {
            $message = "Variant [{$variant->key()}] on [{$product->id()}] does not have any available stock.";
        }

        parent::__construct($message);
    }
}
