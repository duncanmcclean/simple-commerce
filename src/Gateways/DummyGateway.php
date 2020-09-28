<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayPrep;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayPurchase;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayResponse;
use Illuminate\Http\Request;
use Statamic\Entries\Entry;

class DummyGateway extends BaseGateway implements Gateway
{
    public function name(): string
    {
        return 'Dummy';
    }

    public function prepare(GatewayPrep $data): GatewayResponse
    {
        return new GatewayResponse(true, []);
    }

    public function purchase(GatewayPurchase $data): GatewayResponse
    {
        return $this->getCharge(new Entry);
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

    public function getCharge(Entry $entry): GatewayResponse
    {
        return new GatewayResponse(true, [
            'id'        => '123456789abcdefg',
            'last_four' => '4242',
            'date'      => (string) now()->subDays(14),
            'refunded'  => false,
        ]);
    }

    public function refundCharge(Entry $entry): GatewayResponse
    {
        return new GatewayResponse(true, []);
    }

    public function webhook(Request $request)
    {
        return null;
    }
}
