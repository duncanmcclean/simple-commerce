<?php

namespace DoubleThreeDigital\SimpleCommerce\Actions;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Gateway;
use Statamic\Actions\Action;
use Statamic\Entries\Entry;

class RefundAction extends Action
{
    public static function title()
    {
        return __('Refund');
    }

    public function visibleTo($item)
    {
        return $item instanceof Entry &&
            $item->collectionHandle() === config('simple-commerce.collections.orders') &&
            ($item->data()->has('is_paid') && $item->data()->get('is_paid')) &&
            ($item->data()->get('is_refunded') === false || $item->data()->get('is_refunded') === null);
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function run($items, $values)
    {
        collect($items)
            ->each(function ($entry) {
                $cart = Cart::find($entry->id());

                return Gateway::use($cart->data['gateway'])->refundCharge($cart->entry());
            });
    }
}
