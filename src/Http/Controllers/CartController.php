<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns\HandlesCustomerInformation;
use DuncanMcClean\SimpleCommerce\Http\Requests\Cart\DestroyRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\Cart\IndexRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\Cart\UpdateRequest;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as SitesSite;

class CartController extends BaseActionController
{
    use HandlesCustomerInformation;

    public function index(IndexRequest $request)
    {
        if (! Cart::exists()) {
            return [];
        }

        return [
            'data' => Cart::get()
                ->toAugmentedCollection()
                ->withRelations(['customer', 'customer_id'])
                ->withShallowNesting()
                ->toArray(),
        ];
    }

    public function update(UpdateRequest $request)
    {
        $cart = Cart::get();
        $cart = $this->handleCustomerInformation($request, $cart);

        $data = collect($request->all())
            ->except(['_token', '_params', '_redirect', '_request', 'customer', 'email'])
            ->only(config('simple-commerce.field_whitelist.orders'))
            ->map(function ($value) {
                if ($value === 'on') {
                    return true;
                }

                if ($value === 'off') {
                    return false;
                }

                return $value;
            });

        if ($data->isNotEmpty()) {
            $cart->merge($data->toArray());
        }

        $cart->save();
        $cart->recalculate();

        return $this->withSuccess($request, [
            'message' => __('Cart Updated'),
            'cart' => $cart
                ->toAugmentedCollection()
                ->withRelations(['customer', 'customer_id'])
                ->withShallowNesting()
                ->toArray(),
        ]);
    }

    public function destroy(DestroyRequest $request)
    {
        $cart = Cart::get();

        $cart->clearLineItems();

        $cart->save()->recalculate();

        return $this->withSuccess($request, [
            'message' => __('Cart Deleted'),
        ]);
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
