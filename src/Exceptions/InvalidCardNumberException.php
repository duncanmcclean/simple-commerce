<?php

namespace DoubleThreeDigital\SimpleCommerce\Exceptions;

class InvalidCardNumberException extends \Exception
{
    protected $message;

    public function __construct()
    {
        $this->message = 'The card provided is invalid.';
    }
}
