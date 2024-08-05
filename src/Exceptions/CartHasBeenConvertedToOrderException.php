<?php

namespace DuncanMcClean\SimpleCommerce\Exceptions;

class CartHasBeenConvertedToOrderException extends \Exception
{
    public function __construct()
    {
        parent::__construct("This cart has been converted to an order and can no longer be saved.");
    }
}