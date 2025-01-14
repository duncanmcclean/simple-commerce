<?php

namespace DuncanMcClean\SimpleCommerce\Exceptions;

class PaymentGatewayDoesNotExist extends \Exception
{
    public function __construct(string $paymentGateway)
    {
        parent::__construct("Payment gateway [{$paymentGateway}] does not exist.");
    }
}
