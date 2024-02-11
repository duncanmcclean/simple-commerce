<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers;

class PostCheckoutDriver extends SessionDriver
{
    protected $orderId;

    public function __construct(array $checkoutSuccess)
    {
        $this->orderId = $checkoutSuccess['order_id'];
    }

    public function getCartKey(): string
    {
        return $this->orderId;
    }

    public function hasCart(): bool
    {
        return ! empty($this->orderId);
    }
}
