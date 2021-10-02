<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Events\PreCheckout;
use DoubleThreeDigital\SimpleCommerce\Events\StockRunningLow;
use DoubleThreeDigital\SimpleCommerce\Events\StockRunOut;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CheckoutProductHasNoStockException;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Exceptions\NoGatewayProvided;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Gateway;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\AcceptsFormRequests;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Checkout\StoreRequest;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DoubleThreeDigital\SimpleCommerce\Support\Rules\ValidCoupon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as SitesSite;

class CheckoutController extends BaseActionController
{
    use CartDriver, AcceptsFormRequests;

    public $cart;
    public StoreRequest $request;
    public $excludedKeys = ['_token', '_params', '_redirect', '_request'];

    public function __invoke(StoreRequest $request)
    {
        $this->cart = $this->getCart();
        $this->request = $request;

        try {
            $this
                ->preCheckout()
                ->handleValidation()
                ->handleCustomerDetails()
                ->handleCoupon()
                ->handleStock()
                ->handleRemainingData()
                ->handlePayment()
                ->postCheckout();
        } catch (CheckoutProductHasNoStockException $e) {
            $lineItem = $this->cart->lineItems()->filter(function ($lineItem) use ($e) {
                return $lineItem['product'] === $e->product->id();
            })->first();

            $this->cart->removeLineItem($lineItem['id']);
            $this->cart->save();

            return $this->withErrors($this->request, __("Checkout failed. A product in your cart has no stock left. The product has been removed from your cart."));
        }

        return $this->withSuccess($request, [
            'message' => __('simple-commerce.messages.checkout_complete'),
            'cart'    => $request->wantsJson()
                ? $this->cart->toResource()
                : $this->cart->toAugmentedArray(),
        ]);
    }

    protected function preCheckout()
    {
        event(new PreCheckout($this->cart));

        return $this;
    }

    protected function handleValidation()
    {
        $rules = array_merge(
            $this->request->has('_request')
                ? $this->buildFormRequest($this->request->get('_request'), $this->request)->rules()
                : [],
            $this->request->has('gateway')
                ? Gateway::use($this->request->get('gateway'))->purchaseRules()
                : [],
            [
                'coupon' => ['nullable', new ValidCoupon($this->cart)]
            ],
        );

        $messages = array_merge(
            $this->request->has('_request')
                ? $this->buildFormRequest($this->request->get('_request'), $this->request)->messages()
                : [],
            // TODO: gateway custom validation messages?
            [],
        );

        $this->request->validate($rules, $messages);

        return $this;
    }

    protected function handleCustomerDetails()
    {
        $customerData = $this->request->has('customer') ? $this->request->get('customer') : [];

        if (is_string($customerData)) {
            $this->cart->set('customer', $customerData);

            $this->excludedKeys[] = 'customer';

            return $this;
        }

        if ($this->request->has('name') && $this->request->has('email')) {
            $customerData['name'] = $this->request->get('name');
            $customerData['email'] = $this->request->get('email');

            $this->excludedKeys[] = 'name';
            $this->excludedKeys[] = 'email';
        }

        if (isset($customerData['email'])) {
            try {
                $customer = Customer::findByEmail($customerData['email']);
            } catch (CustomerNotFound $e) {
                $customer = Customer::create([
                    'name'  => isset($customerData['name']) ? $customerData['name'] : '',
                    'email' => $customerData['email'],
                    'published' => true,
                ], $this->guessSiteFromRequest()->handle());
            }

            $customer->data($customerData)->save();

            $this->cart->data([
                'customer' => $customer->id,
            ])->save();
        }

        $this->excludedKeys[] = 'customer';

        return $this;
    }

    protected function handleCoupon()
    {
        if ($coupon = $this->request->get('coupon')) {
            $this->cart->set('coupon', Coupon::findByCode($coupon)->id())->save();

            $this->excludedKeys[] = 'coupon';
        }

        if (isset($this->cart->data['coupon'])) {
            $this->cart->coupon()->redeem();
        }

        return $this;
    }

    protected function handleStock()
    {
        $this->cart->lineItems()
            ->each(function ($item) {
                $product = Product::find($item['product']);

                if ($product->purchasableType() === 'product') {
                    if ($product->has('stock') && $product->get('stock') !== null) {
                        $stockCount = $product->get('stock') - $item['quantity'];

                        // Need to do this check before actually setting the stock
                        if ($stockCount <= 0) {
                            event(new StockRunOut($product, $stockCount));

                            throw new CheckoutProductHasNoStockException($product);
                        }

                        $product->set(
                            'stock',
                            $stockCount = $product->get('stock') - $item['quantity']
                        )->save();

                        if ($stockCount <= config('simple-commerce.low_stock_threshold')) {
                            event(new StockRunningLow($product, $stockCount));
                        }
                    }
                }

                if ($product->purchasableType() === 'variants') {
                    $variant = $product->variant($item['variant']);

                    if ($variant->stockCount() !== null) {
                        $stockCount = $variant->stockCount();

                        // Need to do this check before actually setting the stock
                        if ($stockCount <= 0) {
                            event(new StockRunOut($product, $stockCount));

                            throw new CheckoutProductHasNoStockException($product);
                        }

                        $variant->set(
                            'stock',
                            $stockCount = $variant->stockCount() - $item['quantity']
                        );

                        if ($stockCount <= config('simple-commerce.low_stock_threshold')) {
                            event(new StockRunningLow($product, $stockCount));
                        }
                    }
                }
            });

        return $this;
    }

    protected function handleRemainingData()
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
            $this->cart->data($data)->save();
        }

        return $this;
    }

    protected function handlePayment()
    {
        $this->cart = $this->cart->recalculate();

        if ($this->cart->get('grand_total') <= 0) {
            $this->cart->markAsPaid();

            return $this;
        }

        if (! $this->request->has('gateway') && $this->cart->get('is_paid') === false && $this->cart->get('grand_total') !== 0) {
            throw new NoGatewayProvided("No gateway provided.");
        }

        $purchase = Gateway::use($this->request->gateway)->purchase($this->request, $this->cart);

        $this->excludedKeys[] = 'gateway';

        foreach (Gateway::use($this->request->gateway)->purchaseRules() as $key => $rule) {
            $this->excludedKeys[] = $key;
        }

        return $this;
    }

    protected function postCheckout()
    {
        if ($this->cart->customer()) {
            $this->cart->customer()->addOrder($this->cart->id);
        }

        if (! $this->request->has('gateway') && $this->cart->get('is_paid') === false && $this->cart->get('grand_total') === 0) {
            $this->cart->markAsPaid();
        }

        $this->forgetCart();

        event(new PostCheckout($this->cart));

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
