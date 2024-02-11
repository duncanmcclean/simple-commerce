<?php

namespace DuncanMcClean\SimpleCommerce\Tags;

use DuncanMcClean\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DuncanMcClean\SimpleCommerce\Exceptions\PreventCheckout;
use DuncanMcClean\SimpleCommerce\Facades\Gateway;
use DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DuncanMcClean\SimpleCommerce\Orders\Checkout\CheckoutValidationPipeline;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Exception;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class CheckoutTags extends SubTag
{
    use CartDriver;
    use Concerns\FormBuilder;

    public function index()
    {
        $cart = $this->getCart();
        $data = $cart->data()->toArray();

        if ($cart->grandTotal() > 0) {
            SimpleCommerce::gateways()
                ->filter(function ($gateway) {
                    if ($specifiedGateway = $this->params->get('gateway')) {
                        return $gateway['handle'] === $specifiedGateway;
                    }

                    return true;
                })
                ->each(function ($gateway) use (&$cart, &$data) {
                    $config = Gateway::use($gateway['handle'])->config();
                    $prepare = Gateway::use($gateway['handle'])->prepare(request(), $cart);

                    $callbackUrl = Gateway::use($gateway['handle'])
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

        $gateway = SimpleCommerce::gateways()->firstWhere('handle', $gatewayHandle);

        if (! $gateway) {
            throw new GatewayDoesNotExist($gatewayHandle);
        }

        // Run the checkout validation pipeline to ensure the order is valid
        // (eg. ensure there's enough stock to fulfil the customer's order)
        try {
            $cart = app(CheckoutValidationPipeline::class)
                ->send($cart)
                ->thenReturn();
        } catch (PreventCheckout $e) {
            return Redirect::back()->withErrors($e->getMessage());
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
                'cart' => $cart->toAugmentedArray(),
                'is_checkout_request' => true,
            ];

            return $this->params->get('redirect') ?
                redirect($this->params->get('redirect'))->with($data)
                : back()->with($data);
        }

        $prepare = Gateway::use($gateway['handle']);

        if ($this->params->has('redirect')) {
            $prepare->withRedirectUrl($this->params->get('redirect'));
        }

        if ($this->params->has('error_redirect')) {
            $prepare->withErrorRedirectUrl($this->params->get('error_redirect'));
        }

        $prepare = $prepare->prepare(request(), $cart);

        $cart->gatewayData(gateway: $gateway['class']::handle());
        $cart->set($gateway['handle'], $prepare);

        $cart->save();

        if (! isset($prepare['checkout_url'])) {
            throw new Exception('This gateway is not an off-site gateway. Please use the normal checkout tag.');
        }

        abort(redirect($prepare['checkout_url'], 302));
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
