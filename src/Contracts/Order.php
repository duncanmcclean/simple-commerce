<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface Order
{
    public function billingAddress();

    public function shippingAddress();

    public function customer(string $customer = '');

    public function coupon(string $coupon = '');

    public function markAsCompleted(): self;
}
