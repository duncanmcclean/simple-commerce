<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Events\PreCheckout;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CheckoutProductHasNoStockException;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayNotProvided;
use DoubleThreeDigital\SimpleCommerce\Exceptions\PreventCheckout;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Gateway;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\AcceptsFormRequests;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Checkout\StoreRequest;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DoubleThreeDigital\SimpleCommerce\Orders\Checkout\CheckoutPipeline;
use DoubleThreeDigital\SimpleCommerce\Orders\Checkout\CheckoutValidationPipeline;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\Rules\ValidCoupon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as SitesSite;

class CheckoutController extends BaseActionController
{
    use CartDriver, AcceptsFormRequests;

    public $order;

    public StoreRequest $request;

    public $excludedKeys = ['_token', '_params', '_redirect', '_request'];

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
        } catch (CheckoutProductHasNoStockException $e) {
            $lineItem = $this->order->lineItems()->filter(function ($lineItem) use ($e) {
                return $lineItem->product()->id() === $e->product->id();
            })->first();

            $this->order->removeLineItem($lineItem->id());
            $this->order->save();

            return $this->withErrors($this->request, __('Checkout failed. A product in your cart has no stock left. The product has been removed from your cart.'));
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
        $customerData = $this->request->has('customer')
            ? $this->request->get('customer')
            : [];

        if (is_string($customerData)) {
            $this->order->customer($customerData);
            $this->order->save();

            $this->excludedKeys[] = 'customer';

            return $this;
        }

        if ($this->request->has('name') && $this->request->has('email')) {
            $customerData['name'] = $this->request->get('name');
            $customerData['email'] = $this->request->get('email');

            $this->excludedKeys[] = 'name';
            $this->excludedKeys[] = 'email';
        } elseif ($this->request->has('first_name') && $this->request->has('last_name') && $this->request->has('email')) {
            $customerData['first_name'] = $this->request->get('first_name');
            $customerData['last_name'] = $this->request->get('last_name');
            $customerData['email'] = $this->request->get('email');

            $this->excludedKeys[] = 'first_name';
            $this->excludedKeys[] = 'last_name';
            $this->excludedKeys[] = 'email';
        } elseif ($this->request->has('email')) {
            $customerData['email'] = $this->request->get('email');

            $this->excludedKeys[] = 'email';
        }

        if (isset($customerData['email'])) {
            try {
                $customer = Customer::findByEmail($customerData['email']);
            } catch (CustomerNotFound $e) {
                $customerItemData = [
                    'published' => true,
                ];

                if (isset($customerData['name'])) {
                    $customerItemData['name'] = $customerData['name'];
                }

                if (isset($customerData['first_name']) && isset($customerData['last_name'])) {
                    $customerItemData['first_name'] = $customerData['first_name'];
                    $customerItemData['last_name'] = $customerData['last_name'];
                }

                $customer = Customer::make()
                    ->email($customerData['email'])
                    ->data($customerItemData);

                $customer->save();
            }

            $customer
                ->merge(
                    Arr::only($customerData, config('simple-commerce.field_whitelist.customers'))
                )
                ->save();

            $this->order->customer($customer->id());
            $this->order->save();

            $this->order = $this->order->fresh();
        }

        $this->excludedKeys[] = 'customer';

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

        foreach (Site::all() as $site) {
            if (Str::contains(request()->url(), $site->url())) {
                return $site;
            }
        }

        if ($referer = request()->header('referer')) {
            foreach (Site::all() as $site) {
                if (Str::contains($referer, $site->url())) {
                    return $site;
                }
            }
        }

        return Site::current();
    }
}
