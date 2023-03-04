<?php

namespace DoubleThreeDigital\SimpleCommerce\Exceptions;

use DoubleThreeDigital\SimpleCommerce\Contracts\Product;
use DoubleThreeDigital\SimpleCommerce\Products\ProductType;

class CheckoutProductHasNoStockException extends \Exception
{
    public $product;

    public $variant;

    public function __construct(Product $product, $variant = null)
    {
        $this->product = $product;
        $this->variant = $variant;

        if ($product->purchasableType() === ProductType::Product) {
            $message = __('Product :product does not have any available stock.', ['product' => $product->id()]);
        }

        if ($product->purchasableType() === ProductType::Variant) {
            $message = __('Variant :variant on :product does not have any available stock.', [
                'variant' => $variant->key(),
                'product' => $product->id(),
            ]);
        }

        parent::__construct($message);
    }
}
