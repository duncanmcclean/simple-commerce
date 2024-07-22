<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\Payments\GatewayManager as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \DuncanMcClean\SimpleCommerce\Contracts\Payments\GatewayManager use($className)
 * @method static \DuncanMcClean\SimpleCommerce\Contracts\Payments\GatewayManager withRedirectUrl(string $redirectUrl)
 * @method static \DuncanMcClean\SimpleCommerce\Contracts\Payments\GatewayManager withErrorRedirectUrl(string $errorRedirectUrl)
 */
class Gateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
