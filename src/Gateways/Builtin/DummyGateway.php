<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use Illuminate\Http\Request;

class DummyGateway extends BaseGateway implements Gateway
{
    public function name(): string
    {
        return __('Dummy');
    }

    public function prepare(Request $request, Order $order): array
    {
        return [];
    }

    public function checkout(Request $request, Order $order): array
    {
        $this->markOrderAsPaid($order);

        return [
            'id' => '123456789abcdefg',
            'last_four' => '4242',
            'date' => (string) now()->subDays(14),
            'refunded' => false,
        ];
    }

    public function checkoutRules(): array
    {
        return [
            'card_number' => ['required', 'string'],
            'expiry_month' => ['required'],
            'expiry_year' => ['required'],
            'cvc' => ['required'],
        ];
    }

    public function refund(Order $order): array
    {
        return [];
    }

    public function fieldtypeDisplay($value): array
    {
        return [
            'text' => $value['data']['id'],
            'url' => null,
        ];
    }
}
