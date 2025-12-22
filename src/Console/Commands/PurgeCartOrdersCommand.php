<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Statamic\Console\RunsInPlease;

class PurgeCartOrdersCommand extends Command
{
    use RunsInPlease;

    protected $name = 'sc:purge-cart-orders';

    protected $description = 'Purge cart orders that are older than 14 days.';

    public function handle()
    {
        $this->info('Cleaning up..');

        $fourteenDaysFromNow = Carbon::now()->subDays(14);

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], \DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository::class)) {
            $fourteenDaysFromNow = $fourteenDaysFromNow->timestamp;
        }

        Order::query()
            ->whereOrderStatus(OrderStatus::Cart)
            ->where('updated_at', '<=', $fourteenDaysFromNow)
            ->chunk(100, function ($orders) {
                $orders->each(function ($order) {
                    $this->line("Deleting Order: {$order->id()}");
                    $order->delete();
                });
            });
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
