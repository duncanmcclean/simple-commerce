<?php

namespace DuncanMcClean\SimpleCommerce\Events;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use Statamic\Contracts\Git\ProvidesCommitMessage;

class CartDeleted implements ProvidesCommitMessage
{
    public function __construct(public Cart $cart) {}

    public function commitMessage()
    {
        return __('Cart deleted', [], config('statamic.git.locale'));
    }
}
