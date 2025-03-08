<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Statamic\Console\RunsInPlease;
use Statamic\Statamic;
use Statamic\Support\Str;
use Stillat\Proteus\Support\Facades\ConfigWriter;

use function Laravel\Prompts\progress;

class DatabaseOrders extends Command
{
    use Concerns\PublishesMigrations, RunsInPlease;

    protected $signature = 'statamic:simple-commerce:database-orders';

    protected $description = 'Migrates orders to the database.';

    public function __construct()
    {
        parent::__construct();

        app()->bind('simple-commerce.orders.eloquent.model', function () {
            return \DuncanMcClean\SimpleCommerce\Orders\Eloquent\OrderModel::class;
        });

        app()->bind('simple-commerce.orders.eloquent.line_items_model', function () {
            return \DuncanMcClean\SimpleCommerce\Orders\Eloquent\LineItemModel::class;
        });

        Statamic::repository(
            \DuncanMcClean\SimpleCommerce\Contracts\Orders\OrderRepository::class,
            \DuncanMcClean\SimpleCommerce\Stache\Repositories\OrderRepository::class
        );
    }

    public function handle(): void
    {
        $this
            ->publishMigrations()
            ->runMigrations()
            ->importOrders()
            ->updateConfig();
    }

    private function publishMigrations(): self
    {
        $this->publishMigration(
            stubPath: __DIR__.'/stubs/create_orders_table.php.stub',
            name: 'create_orders_table.php',
            replacements: [
                'ORDERS_TABLE' => config('statamic.simple-commerce.orders.table', 'orders'),
            ]
        );

        $this->publishMigration(
            stubPath: __DIR__.'/stubs/create_order_line_items_table.php.stub',
            name: 'create_order_line_items_table.php',
            replacements: [
                'ORDER_LINE_ITEMS_TABLE' => config('statamic.simple-commerce.orders.line_items_table', 'order_line_items'),
            ]
        );

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
                $model = app('simple-commerce.orders.eloquent.model')::updateOrCreate(
                    ['id' => $order->id()],
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
                        'data' => $order->data()->except('updated_at')->all(),
                        'updated_at' => $order->get('updated_at'),
                    ]
                );

                $order->lineItems()->each(function (LineItem $lineItem) use ($model) {
                    $model->lineItems()->create([
                        'id' => $lineItem->id,
                        'product' => $lineItem->product,
                        'variant' => $lineItem->variant,
                        'quantity' => $lineItem->quantity,
                        'unit_price' => $lineItem->unitPrice ?? 0,
                        'sub_total' => $lineItem->subTotal ?? 0,
                        'tax_total' => $lineItem->taxTotal ?? 0,
                        'total' => $lineItem->total,
                        'data' => $lineItem->data()->filter()->all(),
                    ]);
                });

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
