<?php

namespace DuncanMcClean\SimpleCommerce\Exceptions;

class PreventCheckout extends \Exception
{
    public function errors(): array
    {
        return [
            'checkout' => $this->getMessage(),
        ];
    }
}
