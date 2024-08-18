<?php

namespace DuncanMcClean\SimpleCommerce\Exceptions;

use Exception;

class ShipmentTrackingProviderNotFound extends Exception
{
    public function __construct(
        string $providerSlug,
        int $code = 0,
        Exception $previous = null
    )
    {
        $message = "No ShipmentTrackingProvider could be found with the given slug '{$providerSlug}'";

        parent::__construct($message, $code, $previous);
    }
}
