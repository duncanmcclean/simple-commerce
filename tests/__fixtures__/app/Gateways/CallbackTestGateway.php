<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\Gateways;

use DuncanMcClean\SimpleCommerce\Contracts\Order as ContractsOrder;
use DuncanMcClean\SimpleCommerce\Exceptions\GatewayCallbackMethodDoesNotExist;
use DuncanMcClean\SimpleCommerce\Gateways\BaseGateway;
use Illuminate\Http\Request;

class CallbackTestGateway extends BaseGateway
{
    public static $expectedCallbackResult = null;

    public static function handle()
    {
        return 'callback_test';
    }

    public function isOffsiteGateway(): bool
    {
        return true;
    }

    public function prepare(Request $request, ContractsOrder $order): array
    {
        return [];
    }

    public function refund(ContractsOrder $order): ?array
    {
        return [];
    }

    public function callback(Request $request): bool
    {
        if (static::$expectedCallbackResult === 'throw') {
            throw new GatewayCallbackMethodDoesNotExist('callback');
        }

        return static::$expectedCallbackResult;
    }
}
