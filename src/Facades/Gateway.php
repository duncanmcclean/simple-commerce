<?php

namespace DoubleThreeDigital\SimpleCommerce\Facades;

use DoubleThreeDigital\SimpleCommerce\Contracts\GatewayManager as Contract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\GatewayManager use($className)
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\GatewayManager withRedirectUrl(string $redirectUrl)
 * @method static \DoubleThreeDigital\SimpleCommerce\Contracts\GatewayManager withErrorRedirectUrl(string $errorRedirectUrl)
 */
class Gateway extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Contract::class;
    }
}
