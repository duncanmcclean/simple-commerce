<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Support\Arr;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\progress;
use function Laravel\Prompts\select;

class MigrateOrders extends Command
{
    use RunsInPlease;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statamic:simple-commerce:migrate-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate orders from Simple Commerce v7.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $previousDriver = select('How were you storing orders in Simple Commerce v7?', [
            'Entries',
            'Database',
        ]);

        match ($previousDriver) {
            'Entries' => $this->migrateFromEntries(),
            'Database' => $this->migrateFromDatabase(),
        };

        return $this->components->info('Migration complete!');
    }

    private function migrateFromEntries(): void
    {
        $collection = select(
            label: 'Which collections currently store your orders?',
            options: Collection::all()->pluck('title', 'handle')->all(),
            default: 'orders',
        );

        $entries = Entry::query()->where('collection', $collection);

        progress(
            'Migrating orders...',
            $entries->count(),
            function () use ($entries) {
                $entries->chunk(100, function ($entries) {
                    $entries->each(function ($entry) {
                        $data = $entry->data()->except(['blueprint', 'title'])->toArray();

                        if (Order::query()->where('order_number', Arr::get($data, 'order_number'))->exisdfpts()) {
                            return;
                        }

                        Order::make()
                            ->orderNumber(Arr::pull($data, 'order_number'))
//                            ->status(Arr::pull($data, 'order_status'))
                            ->customer($this->migrateCustomer(Arr::pull($data, 'customer')))
                            ->lineItems(Arr::pull($data, 'items'))
                            ->grandTotal(Arr::pull($data, 'grand_total'))
                            ->subTotal(Arr::pull($data, 'items_total'))
                            ->couponTotal(Arr::pull($data, 'coupon_total'))
                            ->taxTotal(Arr::pull($data, 'tax_total'))
                            ->shippingTotal(Arr::pull($data, 'shipping_total'))
                            ->paymentGateway(Arr::pull($data, 'gateway.use'))
                            ->paymentData(Arr::pull($data, 'gateway.data'))
                            ->shippingMethod(Arr::pull($data, 'shipping_method'))
                            ->data($data)
                            ->save();
                    });
                });
            },
        );

        if (confirm("Would you like to delete the [$collection] orders collection?")) {

        }
    }

    private function migrateFromDatabase(): void
    {
        // todo
    }

    private function migrateCustomer(?string $customer = null)
    {
        if (! $customer) {
            return null;
        }

        // todo: migrate customers to users (or guest customers if using Statamic Solo)

        return null;
    }
}
