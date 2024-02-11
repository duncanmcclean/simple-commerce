<?php

namespace DuncanMcClean\SimpleCommerce\Actions;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
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
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            return $item instanceof Entry
                && $item->collectionHandle() === SimpleCommerce::orderDriver()['collection']
                && $item->get('payment_status') === 'paid';
        }

        if (isset(SimpleCommerce::orderDriver()['model'])) {
            $orderModelClass = SimpleCommerce::orderDriver()['model'];

            return $item instanceof $orderModelClass && $item->payment_status === 'paid';
        }

        return false;
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function run($items, $values)
    {
        collect($items)
            ->each(function ($entry) {
                $order = Order::find($entry->id);

                return $order->gatewayData()->gateway()->refund($order);
            });
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
