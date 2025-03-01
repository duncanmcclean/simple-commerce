<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Statamic\Console\RunsInPlease;
use Statamic\Statamic;
use Statamic\Support\Str;
use Stillat\Proteus\Support\Facades\ConfigWriter;
use function Laravel\Prompts\progress;

class Database extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:simple-commerce:database';

    protected $description = 'Migrates carts and orders to the database.';

    public function __construct()
    {
        parent::__construct();

        Statamic::repository(
            \DuncanMcClean\SimpleCommerce\Contracts\Orders\OrderRepository::class,
            \DuncanMcClean\SimpleCommerce\Stache\Repositories\OrderRepository::class
        );
    }

    public function handle(): void
    {
        $this
            ->publishMigration()
            ->runMigrations()
            ->importOrders()
            ->updateConfig();
    }

    private function publishMigration(): self
    {
        $existingMigration = collect(File::allFiles(database_path('migrations')))
            ->map->getFilename()
            ->filter(fn (string $filename) => Str::contains($filename, '_create_orders_table.php'))
            ->first();

        if ($existingMigration) {
            $this->components->info("Migration [database/migrations/{$existingMigration}] already exists.");

            return $this;
        }

        $filename = date('Y_m_d_His').'_create_orders_table.php';

        $orderMigration = Str::of(File::get(__DIR__.'/stubs/create_orders_table.php.stub'))
            ->replace('ORDERS_TABLE', config('statamic.simple-commerce.orders.table', 'orders'))
            ->__toString();

        File::put(database_path('migrations/'.$filename), $orderMigration);

        $this->components->info("Migration [database/migrations/{$filename}] published successfully.");

        return $this;
    }

    private function runMigrations(): self
    {
        $this->call('migrate');

        $this->newLine();

        return $this;
    }

    private function importOrders(): self
    {
        $query = Order::query();

        $progress = progress(label: 'Importing orders', steps: $query->count());

        $progress->start();

        $query->chunk(50, function (Collection $orders) use ($progress) {
            $orders->each(function (OrderContract $order) use ($progress) {
                app('simple-commerce.orders.eloquent.model')::updateOrCreate(
                    ['uuid' => $order->id()],
                    [
                        'order_number' => $order->orderNumber(),
                        'date' => $order->date(),
                        'site' => $order->site(),
                        'cart' => $order->cart(),
                        'status' => $order->status(),
                        'customer' => $order->customer() instanceof GuestCustomer
                            ? json_encode($order->customer()->toArray())
                            : $order->customer()->getKey(),
                        'coupon' => $order->coupon()?->id(),
                        'grand_total' => $order->grandTotal(),
                        'sub_total' => $order->subTotal(),
                        'discount_total' => $order->discountTotal(),
                        'tax_total' => $order->taxTotal(),
                        'shipping_total' => $order->shippingTotal(),
                        'line_items' => $order->lineItems()->map->fileData()->all(),
                        'data' => $order->data(),
                        'updated_at' => $order->updated_at,
                    ]
                );

                $progress->advance();
            });
        });

        $progress->finish();

        $this->components->info('Orders imported successfully.');

        return $this;
    }

    private function updateConfig(): self
    {
        if (config('statamic.simple-commerce.orders.repository') === 'eloquent') {
            $this->components->info('Orders repository is already set to `eloquent`.');

            return $this;
        }

        ConfigWriter::write('statamic.simple-commerce.orders.repository', 'eloquent');

        $this->components->info('Simple Commerce orders repository set to `eloquent`.');

        return $this;
    }
}
