<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\GatewayManager as Contract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayCallbackMethodDoesNotExist;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DoubleThreeDigital\SimpleCommerce\Exceptions\NoGatewayProvided;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class Manager implements Contract
{
    protected $className;
    protected $redirectUrl;
    protected $errorRedirectUrl;

    public function use($className): self
    {
        $this->className = $className;

        return $this;
    }

    public function name()
    {
        return $this->resolve()->name();
    }

    public function config()
    {
        return $this->resolve()->config();
    }

    public function prepare($request, $order)
    {
        return $this->resolve()->prepare(new Prepare($request, $order));
    }

    public function purchase($request, $order)
    {
        $purchase = $this->resolve()->purchase(new Purchase($request, $order));

        if ($purchase->success()) {
            Order::find($order->id())->data([
                'gateway' => [
                    'use' => $this->className,
                    'data' => $purchase->data(),
                ],
            ])->save();
        } else {
            throw ValidationException::withMessages([$purchase->error()]);
        }

        return $purchase;
    }

    public function purchaseRules()
    {
        return $this->resolve()->purchaseRules();
    }

    public function getCharge($order)
    {
        return $this->resolve()->getCharge($order);
    }

    public function refundCharge($order)
    {
        $refund = $this->resolve()->refundCharge($order);

        $cart = Order::find($order->id());

        $cart->data([
            'is_refunded'  => true,
            'gateway' => array_merge($cart->get('gateway'), [
                'refund' => $refund,
            ]),
        ])->save();

        return $refund;
    }

    public function callback(Request $request)
    {
        if (method_exists($this->resolve(), 'callback')) {
            return $this->resolve()->callback($request);
        }

        return new GatewayCallbackMethodDoesNotExist("Gateway [{$this->className}] does not have a `callback` method.");
    }

    public function webhook(Request $request)
    {
        return $this->resolve()->webhook($request);
    }

    public function isOffsiteGateway(): bool
    {
        return $this->resolve()->isOffsiteGateway();
    }

    public function withRedirectUrl(string $redirectUrl): self
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function withErrorRedirectUrl(string $errorRedirectUrl): self
    {
        $this->errorRedirectUrl = $errorRedirectUrl;

        return $this;
    }

    protected function resolve()
    {
        if (! $this->className) {
            throw new NoGatewayProvided('No gateway provided.');
        }

        if (! resolve($this->className)) {
            throw new GatewayDoesNotExist("Gateway [{$this->className}] does not exist.");
        }

        $gateway = collect(SimpleCommerce::gateways())
            ->where('class', $this->className)
            ->first();

        $data = [
            'config' => $gateway['gateway-config'],
            'handle' => $gateway['handle'],
        ];

        if (isset($gateway['webhook_url'])) {
            $data['webhookUrl'] = $gateway['webhook_url'];
        }

        if ($this->redirectUrl) {
            $data['redirectUrl'] = $this->redirectUrl;
        }

        if ($this->errorRedirectUrl) {
            $data['errorRedirectUrl'] = $this->errorRedirectUrl;
        }

        return resolve($this->className, $data);
    }

    public static function bindings(): array
    {
        return [];
    }
}
