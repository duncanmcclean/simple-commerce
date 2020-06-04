<?php

namespace DoubleThreeDigital\SimpleCommerce\Exceptions;

class ParamMissing extends \Exception
{
    protected $message;

    public function __construct(string $param)
    {
        $this->message = "You need to pass in the {$param} parameter.";
    }
}