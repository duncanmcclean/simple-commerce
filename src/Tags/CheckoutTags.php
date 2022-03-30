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

        if ($cart->grandTotal() > 0) {
            collect(SimpleCommerce::gateways())
                ->filter(function ($gateway) {
                    if ($specifiedGateway = $this->params->get('gateway')) {
                        return $gateway['handle'] === $specifiedGateway;
                    }

                    return true;
                })
                ->filter(function ($gateway) {
                    return ! Gateway::use($gateway['class'])->isOffsiteGateway();
                })
                ->each(function ($gateway) use (&$cart, &$data) {
                    try {
                        $prepare = Gateway::use($gateway['class'])->prepare(request(), $cart);

                        $cart->set($gateway['handle'], $prepare->data());
                        $cart->save();

                        $data = $data->merge($prepare->data());
                    } catch (\Exception $e) {
                        throw new GatewayException($e->getMessage());
                    }

                    try {
                        $config = Gateway::use($gateway['class'])->config();

                        $callbackUrl = Gateway::use($gateway['class'])
                            ->withRedirectUrl($this->params->get('redirect'))
                            ->withErrorRedirectUrl($this->params->get('error_redirect') ?? request()->path())
                            ->callbackUrl();

                        $data = $data->merge([
                            'gateway-config' => $config,
                            'callback_url' => $callbackUrl,
                        ]);
                    } catch (\Exception $e) {
                        throw new GatewayException($e->getMessage());
                    }
                });
        }

        return $this->createForm(
            route('statamic.simple-commerce.checkout.store'),
            $data->toArray(),
            'POST'
        );
    }

    // {{ sc:checkout:mollie }}
    public function wildcard(string $tag)
    {
        if (! $tag || $tag === 'index') {
            return $this->index();
        }

        $cart = $this->getCart();
        $gatewayHandle = last(explode(':', $tag));

        $gateway = collect(SimpleCommerce::gateways())
            ->where('handle', $gatewayHandle)
            ->first();

        if (! $gateway) {
            throw new GatewayDoesNotExist($gatewayHandle);
        }

        $prepare = Gateway::use($gateway['class']);

        if ($this->params->has('redirect')) {
            $prepare->withRedirectUrl($this->params->get('redirect'));
        }

        if ($this->params->has('error_redirect')) {
            $prepare->withErrorRedirectUrl($this->params->get('error_redirect'));
        }

        $prepare = $prepare->prepare(request(), $cart);

        $cart->gateway(
            array_merge(
                $cart->gateway() !== null && is_string($cart->gateway()) ? $cart->gateway() : [],
                [
                    'use' => $gateway['class'],
                ]
            )
        );

        $cart->set($gateway['handle'], $prepare->data());

        $cart->save();

        if (! $prepare->checkoutUrl()) {
            throw new Exception('This gateway is not an off-site gateway. Please use the normal checkout tag.');
        }

        abort(redirect($prepare->checkoutUrl(), 302));
    }
}
