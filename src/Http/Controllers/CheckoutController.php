<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Statamic\Facades\User;

class CheckoutController extends BaseActionController
{
    // After a key has been used, put it here so we exclude it on update.
    public $excludedKeys = ['_token', '_params'];

    public function store(Request $request)
    {
        $cart = Cart::find(Session::get(config('simple-commerce.cart_key')));
        $gateway = (new $request->gateway());

        $requestData = Arr::except($request->all(), $this->excludedKeys);
        $cartData = [];

        $request->validate(array_merge($gateway->purchaseRules(), [
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
        ]));

        // TODO: maybe we could merge in blueprint validation too, for custom fields?

        if (isset($requestData['name']) && isset($requestData['email'])) {
            $customer = User::findByEmail($requestData['email']);

            if (! $customer) {
                $customer = User::make()
                    ->email($requestData['email'])
                    ->data(['name' => $requestData['name']])
                    ->save();
            }

            $cart->attachCustomer($customer);

            $this->excludedKeys[] = 'name';
            $this->excludedKeys[] = 'email';
        }

        $cartData['gateway'] = $requestData['gateway'];
        $cartData['gateway_data'] = $gateway->purchase($requestData);

        $this->excludedKeys[] = 'gateway';
        foreach($gateway->purchaseRules() as $key => $rule) $this->excludedKeys[] = $key;

        foreach (Arr::except($requestData, $this->excludedKeys) as $key => $value) {
            if ($value === 'on') {
                $value = true;
            } elseif ($value === 'off') {
                $value = false;
            }

            $cartData[$key] = $value;
        }

        $cart
            ->update($cartData)
            ->markAsCompleted();

        return $this->withSuccess($request);
    }
}