<?php

namespace DuncanMcClean\SimpleCommerce\Facades;

use DuncanMcClean\SimpleCommerce\Contracts\GatewayManager as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \DuncanMcClean\SimpleCommerce\Contracts\GatewayManager use($className)
 * @method static \DuncanMcClean\SimpleCommerce\Contracts\GatewayManager withRedirectUrl(string $redirectUrl)
 * @method static \DuncanMcClean\SimpleCommerce\Contracts\GatewayManager withErrorRedirectUrl(string $errorRedirectUrl)
 */
class Gateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
