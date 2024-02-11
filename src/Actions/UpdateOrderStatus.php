<?php

namespace DuncanMcClean\SimpleCommerce\Actions;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Config;
use Statamic\Actions\Action;
use Statamic\Entries\Entry;
use Statamic\Facades\User;

class UpdateOrderStatus extends Action
{
    public static function title()
    {
        return __('Update Order Status');
    }

    protected function fieldItems()
    {
        return [
            'order_status' => [
                'type' => 'select',
                'options' => collect(OrderStatus::cases())->mapWithKeys(fn ($case) => [
                    $case->value => $case->name,
                ])->toArray(),
                'instructions' => __('**Note:** Changing the order status will not refund or charge the customer.'),
                'validate' => 'required',
            ],
            'reason' => [
                'type' => 'textarea',
                'instructions' => __("Provide a reason for this status change. This will be visible in the order's status log."),
            ],
        ];
    }

    public function visibleTo($item)
    {
        if (Config::get('simple-commerce.disable_order_status_actions')) {
            return false;
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            return $item instanceof Entry
                && $item->collectionHandle() === SimpleCommerce::orderDriver()['collection'];
        }

        if (isset(SimpleCommerce::orderDriver()['model'])) {
            $orderModelClass = SimpleCommerce::orderDriver()['model'];

            return $item instanceof $orderModelClass;
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
        $orderStatus = OrderStatus::from($values['order_status']);

        $data = collect([
            'user' => User::current()->id(),
            'reason' => $values['reason'] ?? null,
        ])->filter();

        collect($items)->each(function ($entry) use ($orderStatus, $data) {
            $order = Order::find($entry->id);
            $order->updateOrderStatus($orderStatus, $data->toArray())->save();
        });
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
