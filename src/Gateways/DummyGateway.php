<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use Statamic\View\View;

class DummyGateway implements Gateway
{
    public function completePurchase($data)
    {
        if ($data['cardNumber'] === '4242 4242 4242 4242') {
            $isPaid = true;
        } else if ($data['cardNumber'] === '1111 1111 1111 1111') {
            $isPaid = false;
        } else {
            throw new \Exception('The card provided is invalid.');
        }

        return [
            'is_paid' => $isPaid,
            'transaction_id' => uniqid(),
        ];
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
