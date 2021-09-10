<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem\DestroyRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem\StoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem\UpdateRequest;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as SitesSite;

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
        if ($request->has('customer')) {
            try {
                if ($cart->customer() && $cart->customer() !== null) {
                    $customer = $cart->customer();
                } elseif ($request->has('email') && $request->get('email') !== null) {
                    $customer = Customer::findByEmail($request->get('email'));
                } else {
                    throw new CustomerNotFound("Customer with ID [{$request->get('customer')}] could not be found.");
                }
            } catch (CustomerNotFound $e) {
                if (is_array($request->get('customer'))) {
                    $customer = Customer::create([
                        'name'  => isset($request->get('customer')['name']) ? $request->get('customer')['name'] : $request->get('customer')['email'],
                        'email' => $request->get('customer')['email'],
                        'published' => true,
                    ], $this->guessSiteFromRequest()->handle());
                } elseif (is_string($request->get('customer'))) {
                    $customer = Customer::find($request->get('customer'));
                }
            }

            $cart->data([
                'customer' => $customer->id,
            ])->save();
        } elseif ($request->has('email')) {
            try {
                $customer = Customer::findByEmail($request->get('email'));
            } catch (CustomerNotFound $e) {
                $customer = Customer::create([
                    'name'  => $request->get('name') ?? $request->get('email'),
                    'email' => $request->get('email'),
                    'published' => true,
                ], $this->guessSiteFromRequest()->handle());
            }

            $cart->data([
                'customer' => $customer->id,
            ])->save();
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

            $prerequisiteProduct = Product::find($product->get('prerequisite_product'));

            $hasPurchasedPrerequisiteProduct = $customer->orders()
                ->filter(function ($order) {
                    return $order->get('is_paid') === true;
                })
                ->filter(function ($order) use ($product) {
                    return collect($order->get('items'))
                        ->where('product', $product->get('prerequisite_product'))
                        ->count() > 0;
                })
                ->count() > 0;

            if (! $hasPurchasedPrerequisiteProduct) {
                return $this->withErrors($request, __("Before purchasing this product, you must purchase {$prerequisiteProduct->title()} first."));
            }
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
