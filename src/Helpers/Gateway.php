<?php

namespace DoubleThreeDigital\SimpleCommerce\Helpers;

use Omnipay\Omnipay;

class Gateway
{
    public $gateway;

    public function __construct()
    {
        // TODO: find way of setting up from the config (because stores can have multiple gateways)
        $this->gateway = Omnipay::create('Stripe');
        $this->gateway->setApiKey(config('simple-commerce.gateways.Stripe.secret'));
    }

    public function charge(array $card, array $properties)
    {
        if (! $card || ! $properties) {
            throw new \Exception('Please pass in card information and charge properties.');
        }

        $response = $this->gateway->purchase([
            'amount' => $properties['amount'],
            'currency' => $properties['currency'],
            'card' => $card,
        ])->send();

        if ($response->isSuccessful()) {
            return $response;
        }

        if ($response->isRedirect()) {
            return $response->redirect();
        }

        return $response->getMessage();
    }
}
