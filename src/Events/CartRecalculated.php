<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use Statamic\Contracts\Git\ProvidesCommitMessage;

class CartRecalculated implements ProvidesCommitMessage
{
    public function __construct(public Cart $cart) {}

    public function commitMessage()
    {
        return __('Cart recalculated', [], config('statamic.git.locale'));
    }
}
