<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;

class DummyGateway implements Gateway
{
    public static $name = 'Dummy';
    public static $description = 'A dummy payment gateway to testing with.';

    public function prepare(array $data)
    {
        // Nothing needs done here.
    }

    public function purchase(array $data): array
    {
        if ($data['card_number'] === '1212 1212 1212 1212') return null;

        return $this->getCharge([]);
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

    public function getCharge(array $data): array
    {
        return [
            'id'        => '123456789abcdefg',
            'last_four' => '4242',
            'date'      => now()->subDays(14),
            'refunded'  => false,
        ];
    }

    public function refundCharge(array $data): array
    {
        return [];
    }
}