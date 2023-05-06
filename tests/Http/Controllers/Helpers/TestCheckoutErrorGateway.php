<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Helpers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayCheckoutFailed;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use Illuminate\Http\Request;

class TestCheckoutErrorGateway extends BaseGateway implements Gateway
{
    public function name(): string
    {
        return 'Test Checkout Error Gateway';
    }

    public function isOffsiteGateway(): bool
    {
        return false;
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
        throw new GatewayCheckoutFailed('Something went wrong with your payment. Sorry!');
    }

    public function checkoutRules(): array
    {
        return [];
    }

    public function checkoutMessages(): array
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
