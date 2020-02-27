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

    public function authorize(array $data)
    {
        $response = $this->gateway->authorize($data)->send();

        if ($response->isSuccessful()) {
            return $response;
        }

        if ($response->isRedirect()) {
            return $response->redirect();
        }

        throw new \Exception($response->getMessage());
    }
}
