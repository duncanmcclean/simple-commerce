<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class CartCleanupCommand extends Command
{
    use RunsInPlease;

    protected $name = 'sc:cart-cleanup';
    protected $description = 'Cleanup carts older than 14 days.';

    public function handle()
    {
        Order::query()
            ->reject(function ($order) {
                return $order->isPaid();
            })
            ->filter(function ($order) {
                return $order->date()->isBefore(now()->subDays(14));
            })
            ->each(function ($order) {
                $this->info("Deleting Order {$order->id()}");

                $order->delete();
            });
    }
}
