<?php

namespace DuncanMcClean\SimpleCommerce\Listeners;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use Illuminate\Auth\Events\Login;
use Statamic\Facades\User;

class AssignUserToCart
{
    public function handle(Login $event): void
    {
        $user = User::fromUser($event->user);

        $recentCart = Cart::query()
            ->where('customer', $user->id())
            ->when(Cart::hasCurrentCart(), fn ($query) => $query->where('id', '!=', Cart::current()->id()))
            ->first();

        if (! $recentCart) {
            Cart::current()->customer(User::fromUser($event->user))->save();
            return;
        }

        if (! Cart::hasCurrentCart()) {
            Cart::setCurrent($recentCart);
            return;
        }

        $shouldMerge = config('simple-commerce.carts.merge_on_login', true);

        if ($shouldMerge) {
            $currentCart = Cart::current();

            $currentCart->lineItems()->each(function (LineItem $lineItem) use ($recentCart) {
                $recentCart->lineItems()->create($lineItem->fileData());
            });

            Cart::forgetCurrentCart();
            Cart::setCurrent($recentCart);
        } else {
            $recentCart->delete();

            Cart::current()->customer(User::fromUser($event->user))->save();
        }
    }
}