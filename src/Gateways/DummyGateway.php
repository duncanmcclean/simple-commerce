<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use Statamic\View\View;

class DummyGateway implements Gateway
{
    public function completePurchase($data)
    {
        if ($data['cardNumber'] === '4242 4242 4242 4242') {
            return true;
        }

        if ($data['cardNumber'] === '1111 1111 1111 1111') {
            return false;
        }

        return 'Invalid card';
    }

    public function rules(): array
    {
        return [
            'cardholder' => 'required|string',
            'cardNumber' => 'required|string',
            'expiryMonth' => 'required',
            'expiryYear' => 'required',
            'cvc' => 'required',
        ];
    }

    public function paymentForm()
    {
        return (new View)
            ->template('commerce::gateways.dummy')
            ->with([
                'class' => get_class($this),
            ]);
    }

    public function refund($payment)
    {
        return true;
    }

    public function name(): string
    {
        return 'Dummy';
    }
}
