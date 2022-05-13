<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Entry;

class CartCleanupCommand extends Command
{
    use RunsInPlease;

    protected $name = 'sc:cart-cleanup';
    protected $description = 'Cleanup carts older than 14 days.';

    public function handle()
    {
        $this->info('Cleaning up..');

        if (isset(SimpleCommerce::orderDriver()['collection'])) {
            Entry::whereCollection(SimpleCommerce::orderDriver()['collection'])
                ->where('is_paid', false)
                ->filter(function ($entry) {
                    return $entry->date()->isBefore(now()->subDays(14));
                })
                ->each(function ($entry) {
                    $this->line("Deleting order: {$entry->id()}");

                    $entry->delete();
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
