<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns\HandlesCustomerInformation;
use DuncanMcClean\SimpleCommerce\Http\Requests\Cart\DestroyRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\Cart\IndexRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\Cart\UpdateRequest;
use DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Sites\Site as SitesSite;

class CartController extends BaseActionController
{
    use CartDriver, HandlesCustomerInformation;

    public function index(IndexRequest $request)
    {
        if (! $this->hasCart()) {
            return [];
        }

        return [
            'data' => $this->getCart()
                ->toAugmentedCollection()
                ->withRelations(['customer', 'customer_id'])
                ->withShallowNesting()
                ->toArray(),
        ];
    }

    public function update(UpdateRequest $request)
    {
        $cart = $this->getCart();
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
        $cart = $this->getCart();

        $cart->clearLineItems();

        $cart->save()->recalculate();

        return $this->withSuccess($request, [
            'message' => __('Cart Deleted'),
        ]);
    }

    protected function guessSiteFromRequest(): SitesSite
    {
        if ($site = request()->get('site')) {
            return Site::get($site);
        }

        if ($referer = request()->header('referer')) {
            foreach (Site::all() as $site) {
                if (Str::contains($referer, $site->url())) {
                    return $site;
                }
            }
        }

        foreach (Site::all() as $site) {
            if (Str::contains(request()->url(), $site->url())) {
                return $site;
            }
        }

        return Site::current();
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
