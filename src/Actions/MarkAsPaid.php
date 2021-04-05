<?php

namespace DoubleThreeDigital\SimpleCommerce\Actions;

use DoubleThreeDigital\SimpleCommerce\Facades\Gateway;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use Statamic\Actions\Action;
use Statamic\Entries\Entry;

class MarkAsPaid extends Action
{
    public static function title()
    {
        return __('Mark As Paid');
    }

    public function visibleTo($item)
    {
        return $item instanceof Entry
            && $item->get('is_paid') === false;
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
