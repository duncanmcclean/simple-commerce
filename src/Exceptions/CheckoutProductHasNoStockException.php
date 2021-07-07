<?php

namespace DoubleThreeDigital\SimpleCommerce\Exceptions;

use DoubleThreeDigital\SimpleCommerce\Contracts\Product;

class CheckoutProductHasNoStockException extends \Exception
{
    public $product;

    public function __construct(Product $product)
    {
        $this->product = $product;

        parent::__construct("Product [{$product->id()}] does not have any available stock.");
    }
}
