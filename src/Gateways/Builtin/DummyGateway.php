<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayPrep;
use DoubleThreeDigital\SimpleCommerce\Gateways\Purchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\Response;
use Illuminate\Http\Request;
use Statamic\Entries\Entry;

class DummyGateway extends BaseGateway implements Gateway
{
    public function name(): string
    {
        return 'Dummy';
    }

    public function prepare(GatewayPrep $data): Response
    {
        return new Response(true, []);
    }

    public function purchase(Purchase $data): Response
    {
        return $this->getCharge(new Entry());
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

    public function getCharge(Entry $entry): Response
    {
        return new Response(true, [
            'id'        => '123456789abcdefg',
            'last_four' => '4242',
            'date'      => (string) now()->subDays(14),
            'refunded'  => false,
        ]);
    }

    public function refundCharge(Entry $entry): Response
    {
        return new Response(true, []);
    }

    public function webhook(Request $request)
    {
        return null;
    }
}
