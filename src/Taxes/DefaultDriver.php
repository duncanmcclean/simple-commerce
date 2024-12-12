<?php

namespace DuncanMcClean\SimpleCommerce\Taxes;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\Driver as DriverContract;

class DefaultDriver implements DriverContract
{
    // todo: consider making this work for line items AND shipping methods...
    // todo: add this method to the contract
    public function getBreakdown(Cart $cart, LineItem $lineItem)
    {
        //
    }
}