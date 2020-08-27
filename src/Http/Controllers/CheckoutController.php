<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Contracts\CartRepository;
use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Events\PreCheckout;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DoubleThreeDigital\SimpleCommerce\Exceptions\NoGatewayProvided;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Checkout\StoreRequest;
use DoubleThreeDigital\SimpleCommerce\SessionCart;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CheckoutController extends BaseActionController
{
    use SessionCart;

    public CartRepository $cart;
    public StoreRequest $request;
    public $excludedKeys = ['_token', '_params', '_redirect'];

    public function store(StoreRequest $request)
    {
        $this->cart = $this->getSessionCart();
        $this->request = $request;

        $this
            ->preCheckout()
            ->handleValidation()
            ->handleCustomerDetails()
            ->handlePayment()
            ->handleCoupon()
            ->handleRemainingData()
            ->postCheckout();

        return $this->withSuccess($request);
    }

    protected function preCheckout()
    {
        event(new PreCheckout($this->cart->data));

        return $this;
    }

    protected function handleValidation()
    {
        // $request->validate($cart->entry()->blueprint()->fields()->validator()->rules());

        $this->request->validate([
            'name'  => 'sometimes|string',
            'email' => 'sometimes|email',
        ]);

        if ($this->request->has('gateway')) {
            $gatewayClass = $this->request->gateway;

            if (! class_exists($gatewayClass)) {
                throw new GatewayDoesNotExist(__('simple-commerce::gateways.gateway_does_not_exist'));
            }

            $gateway = new $gatewayClass();
            $this->request->validate($gateway->purchaseRules());
        }

        return $this;
    }

    protected function handleCustomerDetails()
    {
        $customerData = $this->request->has('customer') ? $this->request->get('customer') : [];

        if ($this->request->has('name') && $this->request->has('email')) {
            $customerData['name'] = $this->request->get('name');
            $customerData['email'] = $this->request->get('email');

            $this->excludedKeys[] = 'name';
            $this->excludedKeys[] = 'email';
        }

        try {
            $customer = Customer::findByEmail($customerData['email']);
        } catch (CustomerNotFound $e) {
            $customer = Customer::make()
                ->data([
                    'name'  => isset($customerData['name']) ? $customerData['name'] : '',
                    'email' => $customerData['email'],
                ])
                ->save();
        }

        $customer->update($customerData);

        $this->cart->update([
            'customer' => $customer->id,
        ]);

        $this->excludedKeys[] = 'customer';

        return $this;
    }

    protected function handlePayment()
    {
        if (! $this->request->has('gateway') && $this->cart->toArray()['is_paid'] === false && $this->cart->data['grand_total'] !== 0) {
            throw new NoGatewayProvided(__('simple-commerce::gateways.no_gateway_provided'));
        }

        $gateway = new $this->request->gateway();
        $gatewayHandle = Str::camel($gateway->name());

        $purchase = $gateway->purchase($this->request->all(), $this->request);

        $this->cart->update([
            'gateway' => $this->request->get('gateway'),
            'gateway_data' => array_merge($purchase, (isset($this->cart->data[$gatewayHandle]) ? $this->cart->data[$gatewayHandle] : [])),
            $gatewayHandle => [],
        ]);

        $this->excludedKeys[] = 'gateway';

        foreach ($gateway->purchaseRules() as $key => $rule) {
            $this->excludedKeys[] = $key;
        }

        return $this;
    }

    protected function handleCoupon()
    {
        if (isset($this->cart->data['coupon'])) {
            $coupon = Coupon::find($this->cart->data['coupon']);

            $coupon->update([
                'redeemed' => $coupon->data['redeemed']++,
            ]);
        }

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

        $this->cart->update($data);

        return $this;
    }

    protected function postCheckout()
    {
        $this->cart->markAsCompleted();
        $this->forgetSessionCart();

        event(new PostCheckout($this->cart->data));

        return $this;
    }
}
