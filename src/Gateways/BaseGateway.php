<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayHasNotImplementedMethod;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class BaseGateway
{
    public function __construct(
        protected array $config = [],
        protected string $handle = '',
        protected string $webhookUrl = '',
        protected string $redirectUrl = '/',
        protected string $errorRedirectUrl = '/'
    ) {
    }

    public function handle(): string
    {
        return $this->handle;
    }

    public function name(): string
    {
        return Str::title(class_basename($this));
    }

    public function displayName()
    {
        if ($displayName = $this->config()->get('display')) {
            return $displayName;
        }

        return $this->name();
    }

    public function config(): Collection
    {
        return collect($this->config);
    }

    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    public function isOffsiteGateway(): bool
    {
        return false;
    }

    abstract public function prepare(Request $request, Order $order): array;

    public function checkout(Request $request, Order $order): array
    {
        throw new GatewayHasNotImplementedMethod('checkout');
    }

    public function checkoutRules(): array
    {
        return [];
    }

    public function checkoutMessages(): array
    {
        return [];
    }

    abstract public function refund(Order $order): ?array;

    public function callback(Request $request): bool
    {
        throw new GatewayHasNotImplementedMethod('callback');
    }

    public function webhook(Request $request)
    {
        throw new GatewayHasNotImplementedMethod('webhook');
    }

    public function fieldtypeDisplay($value): array
    {
        return [
            'text' => isset($value['data']) ? $value['data']['id'] : $value['id'],
            'url' => null,
        ];
    }

    public function callbackUrl(array $extraParamters = []): string
    {
        $data = array_merge($extraParamters, [
            'gateway'         => $this->handle,
            '_redirect'       => $this->redirectUrl,
            '_error_redirect' => $this->errorRedirectUrl,
        ]);

        // TODO: Can I not just make the third parameter true and get rid of the app url thing??
        return config('app.url') . route('statamic.simple-commerce.gateways.callback', $data, false);
    }

    public function webhookUrl(): string
    {
        return $this->webhookUrl;
    }

    public function redirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function errorRedirectUrl(): ?string
    {
        return $this->errorRedirectUrl;
    }

    // TODO: Can't this be protected?
    public function markOrderAsPaid(Order $order): bool
    {
        if ($this->isOffsiteGateway()) {
            $order = app(Pipeline::class)
                ->send($order)
                ->through([
                    \DoubleThreeDigital\SimpleCommerce\Orders\Checkout\HandleStock::class,
                ])
                ->thenReturn();

            if (! isset(SimpleCommerce::customerDriver()['model']) && $order->customer()) {
                $order->customer()->merge([
                    'orders' => $order->customer()->orders()
                        ->pluck('id')
                        ->push($order->id())
                        ->toArray(),
                ]);

                $order->customer()->save();
            }

            $order->updateOrderStatus(OrderStatus::Placed);
            $order->updatePaymentStatus(PaymentStatus::Paid);

            if ($order->coupon()) {
                $order->coupon()->redeem();
            }

            event(new PostCheckout($order, request()));

            return true;
        }

        $order->updatePaymentStatus(PaymentStatus::Paid);

        return true;
    }
}
