<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Events\PostCheckout;
use DuncanMcClean\SimpleCommerce\Events\PreCheckout;
use DuncanMcClean\SimpleCommerce\Exceptions\GatewayNotProvided;
use DuncanMcClean\SimpleCommerce\Exceptions\PreventCheckout;
use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use DuncanMcClean\SimpleCommerce\Facades\Gateway;
use DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns\HandlesCustomerInformation;
use DuncanMcClean\SimpleCommerce\Http\Requests\AcceptsFormRequests;
use DuncanMcClean\SimpleCommerce\Http\Requests\CheckoutRequest;
use DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DuncanMcClean\SimpleCommerce\Orders\Checkout\CheckoutPipeline;
use DuncanMcClean\SimpleCommerce\Orders\Checkout\CheckoutValidationPipeline;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as SitesSite;

class CheckoutController extends BaseActionController
{
    use AcceptsFormRequests, CartDriver, HandlesCustomerInformation;

    public $order;

    public CheckoutRequest $request;

    public $excludedKeys = ['_token', '_params', '_redirect', '_request', 'customer', 'email', 'name', 'first_name', 'last_name'];

    public function __invoke(CheckoutRequest $request)
    {
        $this->request = $request;
        $this->order = $this->getCart();

        try {
            event(new PreCheckout($this->order, $this->request));

            $this
                ->handleCustomerDetails()
                ->handleCoupon()
                ->handleRemainingData()
                ->handleCheckoutValidation()
                ->handlePayment()
                ->postCheckout();
        } catch (PreventCheckout $e) {
            return $this->withErrors($this->request, $e->getMessage());
        }

        return $this->withSuccess($request, [
            'message' => __('Checkout Complete!'),
            'cart' => $this->order
                ->toAugmentedCollection()
                ->withRelations(['customer', 'customer_id'])
                ->withShallowNesting()
                ->toArray(),
            'is_checkout_request' => true,
        ]);
    }

    protected function handleCustomerDetails(): self
    {
        $this->order = $this->handleCustomerInformation($this->request, $this->order);

        return $this;
    }

    protected function handleCoupon(): self
    {
        if ($coupon = $this->request->get('coupon')) {
            $coupon = Coupon::findByCode($coupon);

            $this->order->coupon($coupon);
            $this->order->save();

            $this->excludedKeys[] = 'coupon';
        }

        return $this;
    }

    protected function handleRemainingData(): self
    {
        $data = [];

        foreach (Arr::except($this->request->all(), $this->excludedKeys) as $key => $value) {
            if ($value === 'on') {
                $value = true;
            } elseif ($value === 'off') {
                $value = false;
            }

            $data[$key] = $value;
        }

        if ($data !== []) {
            $this->order->merge(Arr::only($data, config('simple-commerce.field_whitelist.orders')))->save();
            $this->order->save();

            $this->order = $this->order->fresh();
        }

        return $this;
    }

    protected function handleCheckoutValidation(): self
    {
        $this->order = app(CheckoutValidationPipeline::class)
            ->send($this->order)
            ->thenReturn();

        return $this;
    }

    protected function handlePayment(): self
    {
        $this->order = $this->order->recalculate();

        if ($this->order->grandTotal() <= 0) {
            $this->order->updatePaymentStatus(PaymentStatus::Paid);

            return $this;
        }

        if (! $this->request->has('gateway') && $this->order->paymentStatus() !== PaymentStatus::Paid && $this->order->grandTotal() !== 0) {
            throw new GatewayNotProvided('No gateway provided.');
        }

        Gateway::use($this->request->gateway)->checkout($this->request, $this->order);

        $this->excludedKeys[] = 'gateway';

        foreach (Gateway::use($this->request->gateway)->checkoutRules() as $key => $rule) {
            $this->excludedKeys[] = $key;
        }

        $this->order->fresh();

        return $this;
    }

    protected function postCheckout(): self
    {
        $this->order = app(CheckoutPipeline::class)
            ->send($this->order)
            ->thenReturn();

        if (
            ! $this->request->has('gateway')
            && $this->order->status() !== PaymentStatus::Paid
            && $this->order->grandTotal() === 0
        ) {
            $this->order->updatePaymentStatus(PaymentStatus::Paid);
        }

        $this->order->updateOrderStatus(OrderStatus::Placed);

        $this->forgetCart();

        event(new PostCheckout($this->order, $this->request));

        return $this;
    }

    protected function guessSiteFromRequest(): SitesSite
    {
        if ($site = request()->get('site')) {
            return Site::get($site);
        }

        if ($referer = request()->header('referer')) {
            foreach (Site::all() as $site) {
                if (Str::contains($referer, $site->url())) {
                    return $site;
                }
            }
        }

        foreach (Site::all() as $site) {
            if (Str::contains(request()->url(), $site->url())) {
                return $site;
            }
        }

        return Site::current();
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
