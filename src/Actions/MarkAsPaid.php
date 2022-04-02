<?php

namespace DoubleThreeDigital\SimpleCommerce\Actions;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Actions\Action;
use Statamic\Entries\Entry;

class MarkAsPaid extends Action
{
    public static function title()
    {
        return __('simple-commerce::messages.actions.mark_as_paid');
    }

    public function visibleTo($item)
    {
        return $item instanceof Entry
            && isset(SimpleCommerce::orderDriver()['collection'])
            && $item->collectionHandle() === SimpleCommerce::orderDriver()['collection']
            && $item->get('is_paid') !== true;
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

                return $order->markAsPaid();
            });
    }
}
