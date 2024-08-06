<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns\HandlesCustomerInformation;
use DuncanMcClean\SimpleCommerce\Http\Requests\CartItem\DestroyRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CartItem\StoreRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\CartItem\UpdateRequest;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\Products\ProductType;
use Illuminate\Support\Arr;

class CartItemController extends BaseActionController
{
    use HandlesCustomerInformation;

    protected $reservedKeys = [
        'product', 'quantity', 'variant', '_token', '_redirect', '_error_redirect', '_request',
    ];

    public function store(StoreRequest $request)
    {
        $cart = Cart::current();
        $product = Product::find($request->product);

        $items = $cart->lineItems();

        $cart = $this->handleCustomerInformation($request, $cart);

        // Ensure there's enough stock to fulfill the customer's quantity
        if ($product->purchasableType() === ProductType::Product) {
            if (is_int($product->stock()) && $request->quantity > $product->stock()) {
                return $this->withErrors($request, __("There's not enough stock to fulfil the quantity you selected. Please try again later."));
            }
        } elseif ($product->purchasableType() === ProductType::Variant) {
            $variant = $product->variant($request->get('variant'));

            if ($variant !== null && is_int($variant->stock()) && $request->quantity > $variant->stock()) {
                return $this->withErrors($request, __("There's not enough stock to fulfil the quantity you selected. Please try again later."));
            }
        }

        // If this product requires another one, ensure the customer has already purchased it...
        if ($product->has('prerequisite_product')) {
            $customer = $cart->customer();

            if (! $customer) {
                return $this->withErrors($request, __('Please login/register before purchasing this product.'));
            }

            $prerequisiteProduct = Product::find($product->get('prerequisite_product'));

            $hasPurchasedPrerequisiteProduct = $customer->orders()
                ->filter(function ($order) {
                    return $order->paymentStatus() === PaymentStatus::Paid;
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
                'product' => $request->product,
                'quantity' => (int) $request->quantity,
                'unit_price' => $product->price(),
                'total' => 0,
            ];

            if ($request->has('variant')) {
                $item['unit_price'] = $product->variant($request->variant)->price();
                $item['variant'] = [
                    'variant' => $request->variant,
                    'product' => $request->product,
                ];
            }

            $item = array_merge($item, $metadata);

            $cart->lineItems()->create($item);
            $cart->save();
        }

        return $this->withSuccess($request, [
            'message' => __('Added to Cart'),
            'cart' => $cart
                ->toAugmentedCollection()
                ->withRelations(['customer', 'customer_id'])
                ->withShallowNesting()
                ->toArray(),
        ]);
    }

    public function update(UpdateRequest $request, string $requestItem)
    {
        $cart = Cart::current();
        $lineItem = $cart->lineItems()->find($requestItem);

        $data = Arr::only($request->all(), 'quantity', 'variant');

        if (isset($data['quantity']) && is_string($data['quantity'])) {
            $data['quantity'] = (int) $data['quantity'];
        }

        $product = $lineItem->product();

        // Ensure there's enough stock to fulfill the customer's quantity
        if ($product->purchasableType() === ProductType::Product) {
            if (is_int($product->stock()) && $request->quantity > $product->stock()) {
                return $this->withErrors($request, __("There's not enough stock to fulfil the quantity you selected. Please try again later."));
            }
        } elseif ($product->purchasableType() === ProductType::Variant) {
            $variant = $request->has('variant')
                ? $product->variant($request->get('variant'))
                : $product->variant($lineItem->variant()['variant']);

            if ($variant !== null && is_int($variant->stock()) && $request->quantity > $variant->stock()) {
                return $this->withErrors($request, __("There's not enough stock to fulfil the quantity you selected. Please try again later."));
            }
        }

//        $metadata = Arr::only($request->all(), config('simple-commerce.field_whitelist.line_items'));

        $cart->lineItems()->update(
            id: $requestItem,
            data: $data
        );

        $cart->save();

        return $this->withSuccess($request, [
            'message' => __('Line Item Updated'),
            'cart' => $cart
                ->toAugmentedCollection()
                ->withRelations(['customer', 'customer_id'])
                ->withShallowNesting()
                ->toArray(),
        ]);
    }

    public function destroy(DestroyRequest $request, string $item)
    {
        $cart = Cart::current();

        $cart->lineItems()->remove($item);
        $cart->save();

        return $this->withSuccess($request, [
            'message' => __('Item Removed from Cart'),
            'cart' => $cart
                ->toAugmentedCollection()
                ->withRelations(['customer', 'customer_id'])
                ->withShallowNesting()
                ->toArray(),
        ]);
    }
}
