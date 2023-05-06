<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags\Helpers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use Illuminate\Http\Request;

class TestOffsiteGateway extends BaseGateway implements Gateway
{
    public function name(): string
    {
        return 'Test Off-site Gateway';
    }

    public function isOffsiteGateway(): bool
    {
        return true;
    }

    public function prepare(Request $request, OrderContract $order): array
    {
        return [
            'bagpipes' => 'music',
            'checkout_url' => 'http://backpipes.com',
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

    public function webhook(Request $request)
    {
        return 'Success.';
    }
}
