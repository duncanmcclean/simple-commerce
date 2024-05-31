<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Statamic\Facades\Blink;
use Statamic\Facades\Site;
use DuncanMcClean\SimpleCommerce\Facades\Order as OrderFacade;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as OrderContract;

class Cart
{
    public function get(): OrderContract
    {
        if (! $this->exists()) {
            return $this->make();
        }

        return OrderFacade::find(Cookie::has($this->getKey()));
    }

    public function exists(): bool
    {
        return Blink::has($this->getKey()) || Cookie::has($this->getKey());
    }

    public function make(): OrderContract
    {
        $order = OrderFacade::make();
        $order->save();

        Cookie::queue($this->getKey(), $order->orderNumber());

        // Because the cookie won't be set until the end of the request,
        // we need to set it somewhere for the remainder of the request.
        // And that somewhere is Blink.
        Blink::put($this->getKey(), $order->orderNumber());

        return $order;
    }

    public function forget(): void
    {
        $this->get()->delete();

        Cookie::queue(Cookie::forget($this->getKey()));
        Blink::forget($this->getKey());
    }

    private function getKey(): string
    {
        $key = 'simple-commerce-cart';
        $site = $this->guessSiteFromRequest();

        if (Site::hasMultiple() && ! Config::get('simple-commerce.cart.single_cart')) {
            return $key.'-'.$site->handle();
        }

        return $key;
    }

    private function guessSiteFromRequest(): \Statamic\Sites\Site
    {
        if ($site = request()->get('site')) {
            return Site::get($site);
        }

        if ($referer = request()->header('referer')) {
            foreach (Site::all()->reverse() as $site) {
                if (Str::contains($referer, $site->url())) {
                    return $site;
                }
            }
        }

        foreach (Site::all()->reverse() as $site) {
            if (Str::contains(request()->url(), $site->url())) {
                return $site;
            }
        }

        return Site::current();
    }
}