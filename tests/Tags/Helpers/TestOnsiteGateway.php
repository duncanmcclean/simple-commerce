<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags\Helpers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use Illuminate\Http\Request;

class TestOnsiteGateway extends BaseGateway implements Gateway
{
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
