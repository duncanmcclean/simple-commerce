<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayException;
use DoubleThreeDigital\SimpleCommerce\Facades\Gateway;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Exception;

class CheckoutTags extends SubTag
{
    use Concerns\FormBuilder;
    use CartDriver;

    public function index()
    {
        $cart = $this->getCart();
        $data = $cart->data;

        foreach (SimpleCommerce::gateways() as $gateway) {
            try {
                $prepare = Gateway::use($gateway['class'])->prepare(request(), $cart->entry());

                $cart->data([
                    $gateway['handle'] => $prepare->data(),
                ])->save();

                $data = array_merge($data, $prepare->data());
            } catch (\Exception $e) {
                throw new GatewayException($e->getMessage());
            }
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
        if (!$tag || $tag === 'index') {
            return $this->index();
        }

        $cart = $this->getCart();
        $gatewayHandle = last(explode(':', $tag));

        $gateway = collect(SimpleCommerce::gateways())
            ->where('handle', $gatewayHandle)
            ->first();

        if (!$gateway) {
            throw new GatewayDoesNotExist(__('simple-commerce::gateways.gateway_does_not_exist', ['gateway' => $gatewayHandle]));
        }

        $prepare = Gateway::use($gateway['class']);

        if (isset($this->params['redirect'])) {
            $prepare->withRedirectUrl($this->params['redirect']);
        }

        $prepare = $prepare->prepare(request(), $cart->entry());

        $cart->data([
            $gateway['handle'] => $prepare->data(),
        ])->save();

        if (!$prepare->checkoutUrl()) {
            throw new Exception('This gateway is not an off-site gateway. Please use the normal checkout tag.');
        }

        abort(redirect($prepare->checkoutUrl(), 302));
    }
}
