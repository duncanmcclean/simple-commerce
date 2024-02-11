<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
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

        Order::query()
            ->whereOrderStatus(OrderStatus::Cart)
            ->where('updated_at', '<=', Carbon::now()->subDays(14)->timestamp)
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
