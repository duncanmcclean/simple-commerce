<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use Illuminate\Console\Command;

class CartDeletionCommand extends Command
{
    protected $signature = 'commerce:cart-delete';

    public function __construct()
    {
        parent::__construct();

        $this->description = 'Deletes carts older than '.config('commerce.cart-retention').' days old';
    }

    public function handle()
    {
        $this->info('Working on deleting old carts.');

        Cart::where('updated_at', now()->subDays(config('commerce.cart-retention'))->get())
            ->each(function (Cart $cart) {
                $items = CartItem::where('cart_id', $cart->id)
                     ->get();

                collect($items)
                     ->each(function (CartItem $item) {
                         $item->delete();
                     });

                $cart->delete();
            });

        $this->comment('Complete');
    }
}
