<?php

namespace DoubleThreeDigital\SimpleCommerce\Actions;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use Statamic\Actions\Action;
use Statamic\Entries\Entry;

class MarkAsShipped extends Action
{
    public static function title()
    {
        return __('simple-commerce::messages.actions.mark_as_shipped');
    }

    public function visibleTo($item)
    {
        return $item instanceof Entry
            && $item->get('is_paid')
            && !$item->has('is_shipped');
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function run($items, $values)
    {
        collect($items)
            ->each(function ($entry) {
                $order = Order::find($entry->id());

                return $order->markAsShipped();
            });
    }
}
