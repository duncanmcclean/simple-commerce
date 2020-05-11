<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Models\Transaction;
use Illuminate\Support\Collection;
use Statamic\View\View;

class DummyGateway implements Gateway
{
    public function completePurchase(array $data, float $total): Collection
    {
        $isPaid = true;

        if ($data['cardNumber'] === '1111 1111 1111 1111') {
            throw new \Exception('The card provided is invalid.');
        }

        if ($data['expiryYear'] < now()->format('Y')) {
            $isPaid = false;
        }

        return collect([
            'is_complete' => $isPaid,
            'amount'      => $total,
            'data'        => [
                'id' => 'DummyID',
            ],
        ]);
    }

    public function rules(): array
    {
        return [
            'cardholder' => 'required|string',
            'cardNumber' => 'required|string',
            'expiryMonth' => 'required|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'expiryYear' => 'required',
            'cvc' => 'required|min:3|max:4',
        ];
    }

    public function paymentForm(): string
    {
        return (new View())
            ->template('simple-commerce::gateways.dummy')
            ->with([
                'class' => get_class($this),
            ])
            ->render();
    }

    public function refund(Transaction $transaction): Collection
    {
        return collect([
            'is_refunded' => true,
        ]);
    }

    public function name(): string
    {
        return 'Dummy';
    }
}
