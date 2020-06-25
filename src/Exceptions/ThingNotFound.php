<?php

namespace DoubleThreeDigital\SimpleCommerce\Exceptions;

class ThingNotFound extends \Exception
{
    protected $message = '';

    public function __construct(string $thing)
    {
        $this->message = "{$this->thing} not found.";
    }
}
