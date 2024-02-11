<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fixtures\Gateways;

use DuncanMcClean\SimpleCommerce\Contracts\Gateway;
use DuncanMcClean\SimpleCommerce\Contracts\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Gateways\BaseGateway;
use Illuminate\Http\Request;

class TestOnsiteGateway extends BaseGateway implements Gateway
{
    protected static $handle = 'testonsitegateway';

    public function name(): string
    {
        return 'Test On-site Gateway';
    }

    public function isOffsiteGateway(): bool
    {
        return false;
    }

    public function prepare(Request $request, OrderContract $order): array
    {
        return [
            'haggis' => true,
            'tatties' => true,
        ];
    }

    public function checkout(Request $request, OrderContract $order): array
    {
        return [];
    }

    public function refund(OrderContract $order): array
    {
        return [];
    }
}
