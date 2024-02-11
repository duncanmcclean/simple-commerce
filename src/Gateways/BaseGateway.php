<?php

namespace DuncanMcClean\SimpleCommerce\Gateways;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Events\PostCheckout;
use DuncanMcClean\SimpleCommerce\Exceptions\GatewayHasNotImplementedMethod;
use DuncanMcClean\SimpleCommerce\Orders\Checkout\CheckoutPipeline;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Extend\HasHandle;

abstract class BaseGateway
{
    use HasHandle;

    public function __construct(
        protected array $config = [],
        protected string $redirectUrl = '/',
        protected string $errorRedirectUrl = '/'
    ) {
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
            'gateway' => static::handle(),
            '_redirect' => $this->redirectUrl,
            '_error_redirect' => $this->errorRedirectUrl,
        ]);

        return route('statamic.simple-commerce.gateways.callback', $data);
    }

    public function webhookUrl(): ?string
    {
        if (app()->environment('testing')) {
            return null;
        }

        return Str::finish(config('app.url'), '/').config('statamic.routes.action').'/simple-commerce/gateways/'.$this::handle().'/webhook';
    }

    public function redirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    public function errorRedirectUrl(): ?string
    {
        return $this->errorRedirectUrl;
    }

    /**
     * Once you've confirmed that the payment has been made, you can mark the order as paid
     * using this method. For off-site gateways, it'll handle updating stock & redeeming any coupons.
     */
    public function markOrderAsPaid(Order $order, array $data = []): bool
    {
        // We need to ensure that the gateway is available in the
        // order when the OrderPaid event is dispatched.
        $order->gatewayData(gateway: static::handle(), data: []);

        if ($this->isOffsiteGateway()) {
            $order = app(CheckoutPipeline::class)
                ->send($order)
                ->thenReturn();

            $order->updateOrderStatus(OrderStatus::Placed);
            $order->updatePaymentStatus(PaymentStatus::Paid, $data);

            if ($order->coupon()) {
                $order->coupon()->redeem();
            }

            event(new PostCheckout($order, request()));

            return true;
        }

        $order->updatePaymentStatus(PaymentStatus::Paid, $data);

        return true;
    }
}
