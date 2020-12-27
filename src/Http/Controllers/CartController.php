<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Cart\DestroyRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Cart\IndexRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Cart\UpdateRequest;
use DoubleThreeDigital\SimpleCommerce\SessionCart;
use Illuminate\Support\Arr;

class CartController extends BaseActionController
{
    use SessionCart;

    public function index(IndexRequest $request)
    {
        return $this
            ->getSessionCart()
            ->entry()
            ->data();
    }

    public function update(UpdateRequest $request)
    {
        $cart = $this->getSessionCart();
        $data = Arr::except($request->all(), ['_token', '_params', '_redirect']);

        foreach ($data as $key => $value) {
            if ($value === 'on') {
                $value = true;
            } elseif ($value === 'off') {
                $value = false;
            }

            $data[$key] = $value;
        }

        if (isset($data['customer'])) {
            try {
                if (isset($cart->data['customer']) && $cart->data['customer'] !== null) {
                    $customer = Customer::find($cart->data['customer']);
                } elseif (isset($data['customer']['email']) && $data['customer']['email'] !== null) {
                    $customer = Customer::findByEmail($data['customer']['email']);
                } else {
                    throw new CustomerNotFound(__('simple-commerce::customers.customer_not_found', ['id' => $data['customer']]));
                }
            } catch (CustomerNotFound $e) {
                $customer = Customer::make()
                    ->site($this->guessSiteFromRequest())
                    ->data([
                        'name'  => isset($data['customer']['name']) ? $data['customer']['name'] : '',
                        'email' => $data['customer']['email'],
                    ])
                    ->save();
            }

            if (is_array($data['customer'])) {
                $customer->update($data['customer']);
            }

            $cart->update([
                'customer' => $customer->id,
            ]);

            unset($data['customer']);
        }

        if (isset($data['email'])) {
            $customer = Customer::make()
                ->site($this->guessSiteFromRequest())
                ->data([
                    'name' => isset($data['name']) ? $data['name'] : '',
                    'email' => $data['email'],
                ])
                ->save();

            $cart->update([
                'customer' => $customer->id,
            ]);

            unset($data['name']);
            unset($data['email']);
        }

        $cart
            ->update($data)
            ->calculateTotals();

        return $this->withSuccess($request);
    }

    public function destroy(DestroyRequest $request)
    {
        $this
            ->getSessionCart()
            ->update([
                'items' => [],
            ])
            ->calculateTotals();

        return $this->withSuccess($request);
    }
}
