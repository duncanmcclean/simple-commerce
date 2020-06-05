<?php

namespace DoubleThreeDigital\SimpleCommerce\Exceptions;

class CurrencyNotConfigured extends \Exception
{
    protected $message;

    public function __construct()
    {
        $this->message = "Please configure your store's currency.";
    }
}
