<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class CartCleanupCommand extends Command
{
    use RunsInPlease;

    protected $name = 'sc:cart-cleanup';
    protected $description = 'Cleanup carts older than 14 days.';

    public function handle()
    {
        $this->info('Cleaning up..');

        if (isset(SimpleCommerce::orderDriver()['collection'])) {
            Order::query()
                ->reject(function ($order) {
                    return $order->isPaid();
                })
                ->filter(function ($order) {
                    return $order->date()->isBefore(now()->subDays(14));
                })
                ->each(function ($order) {
                    $this->line("Deleting order: {$order->id()}");

                    $order->delete();
                });

            return;
        }

        if (isset(SimpleCommerce::orderDriver()['model'])) {
            $orderModelClass = SimpleCommerce::orderDriver()['model'];

            (new $orderModelClass)
                ->query()
                ->where('is_paid', false)
                ->where('created_at', '<', now()->subDays(14))
                ->each(function ($model) {
                    $this->line("Deleting order: {$model->id}");

                    $model->delete();
                });

            return;
        }

        return $this->error('Unable to cleanup carts with provided cart driver.');
    }
}
