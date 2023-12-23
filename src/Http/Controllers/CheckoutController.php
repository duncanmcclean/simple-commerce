<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Events\PreCheckout;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayNotProvided;
use DoubleThreeDigital\SimpleCommerce\Exceptions\PreventCheckout;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Gateway;
use DoubleThreeDigital\SimpleCommerce\Http\Controllers\Concerns\HandlesCustomerInformation;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\AcceptsFormRequests;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Checkout\StoreRequest;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DoubleThreeDigital\SimpleCommerce\Orders\Checkout\CheckoutPipeline;
use DoubleThreeDigital\SimpleCommerce\Orders\Checkout\CheckoutValidationPipeline;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\Rules\ValidCoupon;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as SitesSite;

class CheckoutController extends BaseActionController
{
    use AcceptsFormRequests, CartDriver, HandlesCustomerInformation;

    public $order;

    public StoreRequest $request;

    public $excludedKeys = ['_token', '_params', '_redirect', '_request', 'customer', 'email', 'name', 'first_name', 'last_name'];

    public function __invoke(StoreRequest $request)
    {
        $this->order = $this->getCart();
        $this->request = $request;

        try {
            event(new PreCheckout($this->order, $this->request));

            $this
                ->handleAdditionalValidation()
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
            'cart' => $this->order->toAugmentedArray(),
            'is_checkout_request' => true,
        ]);
    }

    protected function handleAdditionalValidation(): self
    {
        $rules = array_merge(
            $this->request->get('_request')
                ? $this->buildFormRequest($this->request->get('_request'), $this->request)->rules()
            : [],
            $this->request->has('gateway')
                    ? Gateway::use($this->request->get('gateway'))->checkoutRules()
                    : [],
            [
                'coupon' => ['nullable', new ValidCoupon($this->order)],
                'email' => ['nullable', 'email', function ($attribute, $value, $fail) {
                    if (preg_match('/^\S*$/u', $value) === 0) {
                        return $fail(__('Your email may not contain any spaces.'));
                    }
                }],
            ],
        );

        $messages = array_merge(
            $this->request->get('_request')
                ? $this->buildFormRequest($this->request->get('_request'), $this->request)->messages()
                : [],
            $this->request->has('gateway')
                ? Gateway::use($this->request->get('gateway'))->checkoutMessages()
                : [],
            [],
        );

        $this->request->validate($rules, $messages);

        return $this;
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
