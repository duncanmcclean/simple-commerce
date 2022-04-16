<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Gateways\Purchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\Response;
use Illuminate\Http\Request;

class DummyGateway extends BaseGateway implements Gateway
{
    public function name(): string
    {
        return 'Dummy';
    }

    public function prepare(Prepare $data): Response
    {
        return new Response(true, []);
    }

    public function purchase(Purchase $data): Response
    {
        $this->markOrderAsPaid($data->order());

        return new Response(true, [
            'id'        => '123456789abcdefg',
            'last_four' => '4242',
            'date'      => (string) now()->subDays(14),
            'refunded'  => false,
        ]);
    }

    public function purchaseRules(): array
    {
        return [
            'card_number'   => 'required|string',
            'expiry_month'  => 'required',
            'expiry_year'   => 'required',
            'cvc'           => 'required',
        ];
    }

    public function getCharge(Order $entry): Response
    {
        return new Response(true, [
            'id'        => '123456789abcdefg',
            'last_four' => '4242',
            'date'      => (string) now()->subDays(14),
            'refunded'  => false,
        ]);
    }

    public function refundCharge(Order $entry): Response
    {
        return new Response(true, []);
    }

    public function webhook(Request $request)
    {
        return null;
    }

    public function paymentDisplay($value): array
    {
        return [
            'text' => $value['data']['id'],
            'url' => null,
        ];
    }
}
