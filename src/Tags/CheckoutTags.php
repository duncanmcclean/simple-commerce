<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayException;
use DoubleThreeDigital\SimpleCommerce\Facades\Gateway;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Exception;
use Illuminate\Support\Facades\Session;

class CheckoutTags extends SubTag
{
    use Concerns\FormBuilder;
    use CartDriver;

    public function index()
    {
        $cart = $this->getCart();
        $data = $cart->data()->toArray();

        if ($cart->grandTotal() > 0) {
            collect(SimpleCommerce::gateways())
                ->filter(function ($gateway) {
                    if ($specifiedGateway = $this->params->get('gateway')) {
                        return $gateway['handle'] === $specifiedGateway;
                    }

                    return true;
                })
                ->each(function ($gateway) use (&$cart, &$data) {
                    $config = Gateway::use($gateway['class'])->config();
                    $prepare = Gateway::use($gateway['class'])->prepare(request(), $cart);

                    $callbackUrl = Gateway::use($gateway['class'])
                        ->withRedirectUrl($this->params->get('redirect') ?? request()->path())
                        ->withErrorRedirectUrl($this->params->get('error_redirect') ?? request()->path())
                        ->callbackUrl();

                    $data[$gateway['handle']] = array_merge($prepare, [
                        'config' => $config,
                        'callback_url' => $callbackUrl,
                    ]);

                    $cart->set($gateway['handle'], $prepare)->save();
                });
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

        // If the cart total is 0, don't redirect to the payment gateway,
        // mark the order as paid here and redirect to the success page
        if ($cart->grandTotal() === 0) {
            $cart->updateOrderStatus(OrderStatus::Placed);
            $cart->updatePaymentStatus(PaymentStatus::Paid);

            $this->forgetCart();

            Session::put('simple-commerce.checkout.success', [
                'order_id' => $cart->id(),
                'expiry' => now()->addMinutes(30),
                'url' => $this->params->get('redirect'),
            ]);

            $data = [
                'success' => __('Checkout Complete!'),
                'cart'    => $cart->toAugmentedArray(),
                'is_checkout_request' => true,
            ];

            return $this->params->get('redirect') ?
                redirect($this->params->get('redirect'))->with($data)
                : back()->with($data);
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

        $cart->set($gateway['handle'], $prepare);

        $cart->save();

        if (! isset($prepare['checkout_url'])) {
            throw new Exception('This gateway is not an off-site gateway. Please use the normal checkout tag.');
        }

        abort(redirect($prepare['checkout_url'], 302));
    }
}
