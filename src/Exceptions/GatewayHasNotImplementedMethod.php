<?php

namespace DuncanMcClean\SimpleCommerce\Exceptions;

class GatewayHasNotImplementedMethod extends \Exception
{
    public function __construct(string $methodName)
    {
        parent::__construct("This gateway does not support the [{$methodName}] method.");
    }
}
