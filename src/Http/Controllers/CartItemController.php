<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem\DestroyRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem\StoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem\UpdateRequest;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use Illuminate\Support\Arr;

class CartItemController extends BaseActionController
{
    use CartDriver;

    protected $reservedKeys = [
        'product', 'quantity', 'variant', '_token', '_params', '_redirect',
    ];

    public function store(StoreRequest $request)
    {
        $cart = $this->hasCart() ? $this->getCart() : $this->makeCart();
        $product = Product::find($request->product);

        $items = $cart->has('items') ? $cart->get('items') : [];

        // Handle customer stuff..
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
                $customer = Customer::create([
                    'name'  => isset($data['customer']['name']) ? $data['customer']['name'] : '',
                    'email' => $data['customer']['email'],
                ], $this->guessSiteFromRequest()->handle());
            }

            if (is_array($data['customer'])) {
                $customer->data($data['customer'])->save();
            }

            $cart->data([
                'customer' => $customer->id,
            ])->save();

            unset($data['customer']);
        }

        // Ensure there's enough stock to fulfill the customer's quantity
        if ($product->has('stock') && $product->get('stock') !== null && $product->get('stock') < $request->quantity) {
            return $this->withErrors($request, __("There's not enough stock to fulfil the quantity you selected. Please try again later."));
        }

        // If this product requires another one, ensure the customer has already purchased it...
        if ($product->has('prerequisite_product')) {
            /** @var \DoubleThreeDigital\SimpleCommerce\Contracts\Customer $customer */
            $customer = $cart->customer();

            if (! $customer) {
                return $this->withErrors($request, __('Please login/register before purchasing this product.'));
            }

            $hasPurchasedPrerequisiteProduct = $customer->orders()
                ->filter(function ($order) {
                    return $order->get('is_paid') === true;
                })
                ->filter(function ($order) {
                    // return $order

                    dd($order);
                })
                ->count() > 0;
        }

        // Ensure the product doesn't already exist in the cart
        $alreadyExistsQuery = collect($items);

        if ($request->has('variant')) {
            $alreadyExistsQuery = $alreadyExistsQuery->where('variant', [
                'variant' => $request->get('variant'),
                'product' => $request->get('product'),
            ]);
        } else {
            $alreadyExistsQuery = $alreadyExistsQuery->where('product', $request->product);
        }

        if ($alreadyExistsQuery->count() >= 1) {
            $cart->updateLineItem($alreadyExistsQuery->first()['id'], [
                'quantity' => (int) $alreadyExistsQuery->first()['quantity'] + $request->quantity,
            ]);
        } else {
            $item = [
                'product'  => $request->product,
                'quantity' => (int) $request->quantity,
                'total'    => 0000,
            ];

            if ($request->has('variant')) {
                $item['variant'] = [
                    'variant' => $request->variant,
                    'product' => $request->product,
                ];
            }

            $item = array_merge(
                $item,
                [
                    'metadata' => Arr::except($request->all(), $this->reservedKeys),
                ]
            );

            $cart->addLineItem($item);
        }

        return $this->withSuccess($request, [
            'message' => __('simple-commerce.messages.cart_item_added'),
            'cart'    => $cart->toResource(),
        ]);
    }

    public function update(UpdateRequest $request, string $requestItem)
    {
        $cart = $this->getCart();
        $lineItem = $cart->lineItem($requestItem);

        $cart->updateLineItem(
            $requestItem,
            array_merge(
                Arr::only($request->all(), 'quantity', 'variant'),
                [
                    'metadata' => array_merge(
                        isset($lineItem['metadata']) ? $lineItem['metadata'] : [],
                        Arr::except($request->all(), $this->reservedKeys),
                    )
                ]
            ),
        );

        return $this->withSuccess($request, [
            'message' => __('simple-commerce.messages.cart_item_updated'),
            'cart'    => $cart->toResource(),
        ]);
    }

    public function destroy(DestroyRequest $request, string $item)
    {
        $cart = $this->getCart();

        $cart->removeLineItem($item);

        return $this->withSuccess($request, [
            'message' => __('simple-commerce.messages.cart_item_deleted'),
            'cart'    => $cart->toResource(),
        ]);
    }
}
