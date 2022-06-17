<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem\DestroyRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem\StoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\CartItem\UpdateRequest;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DoubleThreeDigital\SimpleCommerce\Products\ProductType;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as SitesSite;

class CartItemController extends BaseActionController
{
    use CartDriver;

    protected $reservedKeys = [
        'product', 'quantity', 'variant', '_token', '_redirect', '_error_redirect', '_request',
    ];

    public function store(StoreRequest $request)
    {
        $cart = $this->hasCart() ? $this->getCart() : $this->makeCart();
        $product = Product::find($request->product);

        $items = $cart->lineItems();

        // Handle customer stuff..
        if ($request->has('customer')) {
            if (is_string($request->get('customer'))) {
                $customer = Customer::find($request->get('customer'));

                $cart->customer($customer->id());
            }

            if (is_array($request->get('customer'))) {
                try {
                    if ($cart->customer() && $cart->customer() !== null) {
                        $customer = $cart->customer();
                    } elseif (isset($request->get('customer')['email'])) {
                        $customer = Customer::findByEmail($request->get('customer')['email']);

                        $cart->customer($customer->id());
                    }
                } catch (CustomerNotFound $e) {
                    $customerData = [
                        'published' => true,
                    ];

                    if (isset($request->get('customer')['name'])) {
                        $customerData['name'] = $request->get('customer')['name'];
                    }

                    if (isset($request->get('customer')['first_name']) && isset($request->get('customer')['last_name'])) {
                        $customerData['first_name'] = $request->get('customer')['first_name'];
                        $customerData['last_name'] = $request->get('customer')['last_name'];
                    }

                    $customer = Customer::make()
                        ->email($request->get('customer')['email'])
                        ->data($customerData);

                    $customer->save();

                    $cart->customer($customer->id());
                }

                if (isset($customer) && Arr::except($request->get('customer'), ['email', 'name', 'first_name', 'last_name']) !== []) {
                    $customer
                        ->merge(
                            Arr::except($request->get('customer'), ['email', 'name', 'first_name', 'last_name'])
                        )
                        ->save();
                }
            }
        } elseif ($request->has('email')) {
            try {
                $customer = Customer::findByEmail($request->get('email'));
            } catch (CustomerNotFound $e) {
                $customerData = [
                    'published' => true,
                ];

                if ($request->get('name')) {
                    $customerData['name'] = $request->get('name');
                }

                if ($request->get('first_name') && $request->get('last_name')) {
                    $customerData['first_name'] = $request->get('first_name');
                    $customerData['last_name'] = $request->get('last_name');
                }

                $customer = Customer::make()
                    ->email($request->get('email'))
                    ->data($customerData);

                $customer->save();
            }

            $cart->customer($customer->id());
        }

        // Ensure there's enough stock to fulfill the customer's quantity
        if ($product->purchasableType() === ProductType::PRODUCT()) {
            if ($product->stock() && $product->stock() !== null && $product->stock() < $request->quantity) {
                return $this->withErrors($request, __("There's not enough stock to fulfil the quantity you selected. Please try again later."));
            }
        } elseif ($product->purchasableType() === ProductType::VARIANT()) {
            $variant = $product->variant($request->get('variant'));

            if ($variant !== null && $variant->stock() !== null && $variant->stock() < $request->quantity) {
                return $this->withErrors($request, __("There's not enough stock to fulfil the quantity you selected. Please try again later."));
            }
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
                    return $order->isPaid() === true;
                })
                ->filter(function ($order) use ($product) {
                    return $order->lineItems()
                        ->where('product', $product->get('prerequisite_product'))
                        ->count() > 0;
                })
                ->count() > 0;

            if (! $hasPurchasedPrerequisiteProduct) {
                return $this->withErrors($request, __("Before purchasing this product, you must purchase {$prerequisiteProduct->get('title')} first."));
            }
        }

        // Ensure the product doesn't already exist in the cart
        $alreadyExistsQuery = $items;
        $metadata = Arr::only($request->all(), config('simple-commerce.field_whitelist.line_items'));

        if ($request->has('variant')) {
            $alreadyExistsQuery = $alreadyExistsQuery->where('variant', [
                'variant' => $request->get('variant'),
                'product' => $request->get('product'),
            ]);
        } else {
            $alreadyExistsQuery = $alreadyExistsQuery->where('product', Product::find($request->product));
        }

        if (config('simple-commerce.cart.unique_metadata', false)) {
            $alreadyExistsQuery = $alreadyExistsQuery->where('metadata', collect($metadata));
        }

        if ($alreadyExistsQuery->count() >= 1) {
            $cart->updateLineItem($alreadyExistsQuery->first()->id(), [
                'quantity' => (int) $alreadyExistsQuery->first()->quantity() + $request->quantity,
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
                    'metadata' => $metadata,
                ]
            );

            $cart->addLineItem($item);
        }

        return $this->withSuccess($request, [
            'message' => __('simple-commerce.messages.cart_item_added'),
            'cart'    => $cart->fresh()->toResource(),
        ]);
    }

    public function update(UpdateRequest $request, string $requestItem)
    {
        $cart = $this->getCart();
        $lineItem = $cart->lineItem($requestItem);

        $data = Arr::only($request->all(), 'quantity', 'variant');

        if (isset($data['quantity']) && is_string($data['quantity'])) {
            $data['quantity'] = (int) $data['quantity'];
        }

        $cart->updateLineItem(
            $requestItem,
            array_merge(
                $data,
                [
                    'metadata' => $lineItem->metadata()->merge(Arr::only($request->all(), config('simple-commerce.field_whitelist.line_items')))->toArray(),
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
