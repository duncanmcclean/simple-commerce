<?php

namespace DoubleThreeDigital\SimpleCommerce\Actions;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Actions\Action;
use Statamic\Entries\Entry;

class MarkAsShipped extends Action
{
    public static function title()
    {
        return __('Mark as Shipped');
    }

    public function visibleTo($item)
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            return $item instanceof Entry
                && $item->collectionHandle() === SimpleCommerce::orderDriver()['collection']
                && $item->get('is_paid') === true
                && $item->get('is_shipped') !== true;
        }

        if (isset(SimpleCommerce::orderDriver()['model'])) {
            $orderModelClass = SimpleCommerce::orderDriver()['model'];

            return $item instanceof $orderModelClass
                && $item->is_paid
                && ! $item->is_shipped;
        }

        return false;
    }

    public function visibleToBulk($items)
    {
        $allowedOnItems = $items->filter(function ($item) {
            return $this->visibleTo($item);
        });

        return $items->count() === $allowedOnItems->count();
    }

    public function run($items, $values)
    {
        collect($items)
            ->each(function ($entry) {
                $order = Order::find($entry->id);

                return $order->markAsShipped();
            });
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
