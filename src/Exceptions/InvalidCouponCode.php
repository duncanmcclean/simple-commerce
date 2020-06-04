<?php

namespace DoubleThreeDigital\SimpleCommerce\Exceptions;

class InvalidCouponCode extends \Exception
{
    protected $message;

    public function _construct()
    {
        $this->message = 'The coupon code provided does not exist.';
    }
}