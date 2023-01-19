<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Entry;

class PurgeCartOrdersCommand extends Command
{
    use RunsInPlease;

    protected $name = 'sc:purge-cart-orders';
    protected $description = "Purge cart orders that are older than 14 days.";

    public function handle()
    {
        $this->info('Cleaning up..');

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            Entry::whereCollection(SimpleCommerce::orderDriver()['collection'])
                ->where('order_status', OrderStatus::Cart->value)
                ->filter(function ($entry) {
                    return $entry->date()->isBefore(now()->subDays(14));
                })
                ->each(function ($entry) {
                    $this->line("Deleting Order: {$entry->id()}");

                    $entry->delete();
                });
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            $orderModelClass = SimpleCommerce::orderDriver()['model'];

            (new $orderModelClass)
                ->query()
                ->where('order_status', OrderStatus::Cart->value)
                ->where('created_at', '<', now()->subDays(14))
                ->each(function ($model) {
                    $this->line("Deleting Order: {$model->id}");

                    $model->delete();
                });
        }
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
