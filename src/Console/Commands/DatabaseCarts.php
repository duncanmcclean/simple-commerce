<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Statamic\Console\RunsInPlease;
use Statamic\Statamic;
use Stillat\Proteus\Support\Facades\ConfigWriter;

use function Laravel\Prompts\progress;

class DatabaseCarts extends Command
{
    use Concerns\PublishesMigrations, RunsInPlease;

    protected $signature = 'statamic:simple-commerce:database-carts';

    protected $description = 'Migrates carts to the database.';

    public function __construct()
    {
        parent::__construct();

        app()->bind('simple-commerce.carts.eloquent.model', function () {
            return \DuncanMcClean\SimpleCommerce\Cart\Eloquent\CartModel::class;
        });

        app()->bind('simple-commerce.carts.eloquent.line_items_model', function () {
            return \DuncanMcClean\SimpleCommerce\Cart\Eloquent\LineItemModel::class;
        });

        Statamic::repository(
            \DuncanMcClean\SimpleCommerce\Contracts\Cart\CartRepository::class,
            \DuncanMcClean\SimpleCommerce\Stache\Repositories\CartRepository::class
        );
    }

    public function handle(): void
    {
        $this
            ->publishMigrations()
            ->runMigrations()
            ->importCarts()
            ->updateConfig();
    }

    private function publishMigrations(): self
    {
        $this->publishMigration(
            stubPath: __DIR__.'/stubs/create_carts_table.php.stub',
            name: 'create_carts_table.php',
            replacements: [
                'CARTS_TABLE' => config('statamic.simple-commerce.carts.table', 'carts'),
            ]
        );

        $this->publishMigration(
            stubPath: __DIR__.'/stubs/create_cart_line_items_table.php.stub',
            name: 'create_cart_line_items_table.php',
            replacements: [
                'CART_LINE_ITEMS_TABLE' => config('statamic.simple-commerce.carts.line_items_table', 'cart_line_items'),
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

    private function importCarts(): self
    {
        $query = Cart::query();

        $progress = progress(label: 'Importing carts', steps: $query->count());

        $progress->start();

        $query->chunk(50, function (Collection $carts) use ($progress) {
            $carts->each(function (CartContract $cart) use ($progress) {
                $model = app('simple-commerce.carts.eloquent.model')::updateOrCreate(
                    ['id' => $cart->id()],
                    [
                        'site' => $cart->site(),
                        'customer' => $cart->customer() instanceof GuestCustomer
                            ? json_encode($cart->customer()->toArray())
                            : $cart->customer()?->getKey(),
                        'coupon' => $cart->coupon()?->id(),
                        'grand_total' => $cart->grandTotal(),
                        'sub_total' => $cart->subTotal(),
                        'discount_total' => $cart->discountTotal(),
                        'tax_total' => $cart->taxTotal(),
                        'shipping_total' => $cart->shippingTotal(),
                        'data' => $cart->data()->except('updated_at')->all(),
                        'updated_at' => $cart->get('updated_at'),
                    ]
                );

                $cart->lineItems()->each(function (LineItem $lineItem) use ($model) {
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

        return $this;
    }

    private function updateConfig(): self
    {
        if (config('statamic.simple-commerce.carts.repository') === 'eloquent') {
            $this->components->info('Carts repository is already set to `eloquent`.');

            return $this;
        }

        ConfigWriter::write('statamic.simple-commerce.carts.repository', 'eloquent');

        $this->components->info('Simple Commerce carts repository set to `eloquent`.');

        return $this;
    }
}
