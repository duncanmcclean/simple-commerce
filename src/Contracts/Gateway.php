<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

use Illuminate\Http\Request;

interface Gateway
{
    /**
     * This method should return the name of the payment gateway. This name can be
     * overridden by sites in their config.
     */
    public function name(): string;

    /**
     * If your payment gateway is off-site (eg. your customer doesn't have to submit the
     * {{ sc:checkout }} form to confirm the payment), then you should return true here.
     */
    public function isOffsiteGateway(): bool;

    /**
     * This method is called when the {{ sc:checkout }} tag is used. It should return any
     * data you need in the front-end to handle a payment (like a Stripe Payment Intent).
     *
     * If you're building an off-site gateway, you should return a `checkout_url` key with the
     * URL the user should be redirected to for checkout.
     */
    public function prepare(Request $request, Order $order): array;

    /**
     * This method is called when you submit the {{ sc:checkout }} form. It should return
     * an array of payment data that'll be saved onto the order.
     *
     * If you need to display an error message, you should throw a GatewayCheckoutFailed exception.
     *
     * If you're building an off-site gateway, you don't need to implement this method.
     */
    public function checkout(Request $request, Order $order): array;

    /**
     * This method should return an array of validation rules that'll be run whenever
     * the {{ sc:checkout }} has been submitted.
     *
     * If you're building an off-site gateway, you don't need to implement this method.
     */
    public function checkoutRules(): array;

    /**
     * This method should return an array of validation messages that'll be used whenever
     * the {{ sc:checkout }} has been submitted. This method isn't mandatory.
     *
     * If you're building an off-site gateway, you don't need to implement this method.
     */
    public function checkoutMessages(): array;

    /**
     * When given an order, this method should process the refund of an order. You should
     * return an array of any data which may prove helpful in the future to track down
     * refunds (like a Refund ID).
     *
     * @return array|null
     */
    public function refund(Order $order): array;

    /**
     * This method will be called when users are redirected back to your site after
     * an off-site checkout. You should return true if the payment was successful.
     */
    public function callback(Request $request): bool;

    /**
     * This method will be called when a webhook is received from the payment gateway.
     * This is where you should handle any updates to order statuses.
     *
     * Whatever you return from this method will be sent back as the webhook's response.
     */
    public function webhook(Request $request);

    /**
     * This method should return an array containing `text` and `url` keys. The `text` key
     * should return something unique to the payment & the `url` should return a URL to
     * the payment in the payment gateway's dashboard.
     *
     * @param  mixed  $value
     */
    public function fieldtypeDisplay($value): array;
}
