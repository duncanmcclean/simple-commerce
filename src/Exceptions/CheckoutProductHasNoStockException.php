<?php

namespace DoubleThreeDigital\SimpleCommerce\Exceptions;

use DoubleThreeDigital\SimpleCommerce\Contracts\Product;

class CheckoutProductHasNoStockException extends PreventCheckout
{
    public function __construct(string $message, public Product $product, public $variant = null)
    {
        $this->product = $product;
        $this->variant = $variant;

        parent::__construct($message);
    }
}
