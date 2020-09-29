<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DoubleThreeDigital\SimpleCommerce\Facades\Gateway;
use DoubleThreeDigital\SimpleCommerce\SessionCart;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Exception;
use Illuminate\Support\Facades\URL;

class CheckoutTags extends SubTag
{
    use Concerns\FormBuilder,
        SessionCart;

    public function index()
    {
        $cart = $this->getSessionCart();
        $data = $cart->data;

        foreach (SimpleCommerce::gateways() as $gateway) {
            $prepare = Gateway::use($gateway['class'])->prepare(request(), $cart->entry());

            $cart->update([
                $gateway['handle'] => $prepare->data(),
            ]);

            $data = array_merge($data, $prepare->data());
        }

        return $this->createForm(
            route('statamic.simple-commerce.checkout.store'),
            $data,
            'POST'
        );
    }

    // {{ sc:checkout:mollie }}
    public function wildcard(string $tag)
    {
        $cart = $this->getSessionCart();
        $gatewayHandle = last(explode(':', $tag));

        $gateway = collect(SimpleCommerce::gateways())
            ->where('handle', $gatewayHandle)
            ->first();

        if (! $gateway) {
            throw new GatewayDoesNotExist(__('simple-commerce::gateways.gateway_does_not_exist', ['gateway' => $gatewayHandle]));
        }

        $prepare = Gateway::use($gateway['class'])
            ->withRedirectUrl($this->params['redirect'])
            ->prepare(request(), $cart->entry());

        if (! $prepare->checkoutUrl()) {
            throw new Exception('This gateway is not an off-site gateway. Please use the normal checkout tag.');
        }

        abort(redirect($prepare->checkoutUrl(), 302));
        return;
    }
}
