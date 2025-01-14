<?php

namespace DuncanMcClean\SimpleCommerce\Payments\Gateways;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Contracts\Payments\Gateway as GatewayContract;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\Extend\HasHandle;
use Statamic\Extend\HasTitle;
use Statamic\Extend\RegistersItself;

abstract class PaymentGateway implements GatewayContract
{
    use HasHandle, HasTitle, RegistersItself;

    public function config(): Collection
    {
        return collect(config("statamic.simple-commerce.payments.gateways.{$this->handle()}"));
    }

    // todo: return types
    // todo: add methods to contract

    /**
     * This will be called when the cart has been recalculated. You may wish to use
     * this to update the payment in your gateway's API.
     */
    public function afterRecalculating(Cart $cart): void
    {
        //
    }

    // run when the payment form is loaded.
    abstract public function setup(Cart $cart): array;

    // run when the checkout form is submitted, AFTER the order has been created.
    abstract public function process(Order $order): void;

    public function rules(): array
    {
        return [];
    }

    // marks the order as paid (called by a webhook). does the actual charging of the card.
    abstract public function capture(Order $order);

    // cancels the payment (will be called if the order can't be fulfilled - eg. if no customer/address is provided, stock is out, etc)
    abstract public function cancel(Cart $cart): void;

    // run when the webhook is called and calls capture when the right event comes along
    abstract public function webhook(Request $request);

    abstract public function refund(Order $order, int $amount);
}
