<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Cart\DestroyRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Cart\IndexRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Cart\UpdateRequest;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as SitesSite;

class CartController extends BaseActionController
{
    use CartDriver;

    public function index(IndexRequest $request)
    {
        if (! $this->hasCart()) {
            return [];
        }

        return ['data' => $this->getCart()->toAugmentedArray()];
    }

    public function update(UpdateRequest $request)
    {
        $cart = $this->getCart();
        $data = Arr::except($request->all(), ['_token', '_params', '_redirect', '_request']);

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
                if ($cart->customer() && $cart->customer() !== null) {
                    $customer = $cart->customer();
                } elseif (isset($data['customer']['email']) && $data['customer']['email'] !== null) {
                    $customer = Customer::findByEmail($data['customer']['email']);
                } else {
                    throw new CustomerNotFound("Customer with ID [{$data['customer']}] could not be found.");
                }
            } catch (CustomerNotFound $e) {
                $customerData = [
                    'published' => true,
                ];

                if (isset($customerData['customer']['name'])) {
                    $customerData['name'] = $customerData['customer']['name'];
                }

                if (isset($customerData['customer']['first_name'])) {
                    $customerData['first_name'] = $customerData['customer']['first_name'];
                    $customerData['last_name'] = $customerData['customer']['last_name'];
                }

                $customer = Customer::make()
                    ->email($data['customer']['email'])
                    ->data($customerData);

                $customer->save();
            }

            if (is_array($data['customer'])) {
                $customer->merge($data['customer'])->save();
            }

            $cart->customer($customer->id());
            $cart->save();

            $cart = $cart->fresh();

            unset($data['customer']);
        }

        if (isset($data['email'])) {
            try {
                if (isset($data['email']) && $data['email'] !== null) {
                    $customer = Customer::findByEmail($data['email']);
                } else {
                    throw new CustomerNotFound("Customer with ID [{$data['customer']}] could not be found.");
                }
            } catch (CustomerNotFound $e) {
                $customerData = [
                    'published' => true,
                ];

                if (isset($data['name'])) {
                    $customerData['name'] = $data['name'];
                }

                if (isset($data['first_name']) && isset($data['last_name'])) {
                    $customerData['first_name'] = $data['first_name'];
                    $customerData['last_name'] = $data['last_name'];
                }

                $customer = Customer::make()
                    ->email($data['email'])
                    ->data($customerData);

                $customer->save();
            }

            $cart->customer($customer->id());
            $cart->save();

            $cart = $cart->fresh();

            unset($data['name']);
            unset($data['first_name']);
            unset($data['last_name']);
            unset($data['email']);
        }

        if ($data !== null) {
            $cart = $cart->merge(Arr::only($data, config('simple-commerce.field_whitelist.orders')));
        }

        $cart->save();
        $cart->recalculate();

        return $this->withSuccess($request, [
            'message' => __('simple-commerce.messages.cart_updated'),
            'cart'    => $cart->toResource(),
        ]);
    }

    public function destroy(DestroyRequest $request)
    {
        $cart = $this->getCart();

        $cart->clearLineItems();

        $cart->save()->recalculate();

        return $this->withSuccess($request, [
            'message' => __('simple-commerce.messages.cart_deleted'),
            'cart'    => null,
        ]);
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
