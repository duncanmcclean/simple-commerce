<?php

namespace DuncanMcClean\SimpleCommerce\Payments\Gateways;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;

abstract class PaymentGateway
{
    use HasHandle, HasTitle, RegistersItself;

    abstract public function setup(Cart $cart): array;

    abstract public function process(Order $order): void;

    abstract public function capture(Order $order): void;

    abstract public function cancel(Cart $cart): void;

    abstract public function webhook(Request $request): Response;

    abstract public function refund(Order $order, int $amount): void;

    public function fieldtypeDetails(Order $order): array
    {
        return [
            __('Amount') => Money::format($order->grandTotal(), $order->site()),
        ];
    }

    public function logo(): ?string
    {
        return null;
    }

    public function config(): Collection
    {
        return collect(config("statamic.simple-commerce.payments.gateways.{$this->handle()}"));
    }

    public function checkoutUrl(): string
    {
        return route('statamic.simple-commerce.payments.checkout', $this->handle());
    }

    public function webhookUrl(): string
    {
        return route('statamic.simple-commerce.payments.webhook', $this->handle());
    }
}
