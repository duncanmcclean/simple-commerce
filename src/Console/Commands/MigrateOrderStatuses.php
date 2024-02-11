<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use Carbon\Carbon;
use DuncanMcClean\SimpleCommerce\Orders\EloquentOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\OrderModel;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Entry;

class MigrateOrderStatuses extends Command
{
    use ConfirmableTrait, RunsInPlease;

    protected $name = 'sc:migrate-order-statuses';

    protected $description = 'Migrate your orders to use the new order & payment statuses. (Part of the v5.0 update)';

    protected $stubsPath;

    public function __construct()
    {
        parent::__construct();

        $this->stubsPath = __DIR__.'/stubs';
    }

    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            $this->migrateForOrderEntries();

            return $this->info('Migration complete!');
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            $this->migrateForDatabaseOrders();

            return $this->info('Migration complete!');
        }

        $this->error("This migration script doesn't support your current order repository.");
    }

    protected function migrateForOrderEntries(): void
    {
        Entry::query()
            ->where('collection', SimpleCommerce::orderDriver()['collection'])
            ->get()
            ->reject(function ($entry) {
                // If we've already migrated the order, we don't need to do it again.
                return $entry->has('order_status') && $entry->has('payment_status');
            })
            ->each(function ($entry) {
                if ($entry->get('is_paid') === true) {
                    $entry->set('order_status', OrderStatus::Placed->value);
                    $entry->set('payment_status', PaymentStatus::Paid->value);

                    if ($entry->get('is_shipped') === true) {
                        $entry->set('order_status', OrderStatus::Dispatched->value);
                    }

                    if ($entry->get('is_refunded') === true) {
                        $entry->set('payment_status', PaymentStatus::Refunded->value);
                    }

                    $entry->set('status_log', [
                        'paid' => Carbon::parse($entry->get('paid_date'))->format('Y-m-d H:i'),
                    ]);

                    $entry->set('paid_date', null);
                    $entry->set('is_paid', null);
                    $entry->set('is_shipped', null);
                    $entry->set('is_refunded', null);

                    $entry->save();

                    return;
                }

                $entry->set('order_status', OrderStatus::Cart->value);
                $entry->set('payment_status', PaymentStatus::Unpaid->value);

                $entry->save();
            });
    }

    protected function migrateForDatabaseOrders(): void
    {
        if (count(File::glob(database_path('migrations').'/*_add_status_columns_to_orders_table.php')) < 1) {
            File::copy($this->stubsPath.'/add_status_columns_to_orders_table.php', database_path('migrations/'.date('Y_m_d_His').'_add_status_columns_to_orders_table.php'));
        }

        Artisan::call('migrate');

        OrderModel::query()
            ->chunk(200, function ($orders) {
                $orders->each(function (OrderModel $model) {
                    if ($model->is_paid) {
                        $model->order_status = OrderStatus::Placed->value;
                        $model->payment_status = PaymentStatus::Paid->value;

                        if ($model->is_shipped) {
                            $model->order_status = OrderStatus::Dispatched->value;
                        }

                        if ($model->is_refunded) {
                            $model->payment_status = PaymentStatus::Refunded->value;
                        }

                        $model->data = array_merge($model->data, [
                            'status_log' => [
                                'paid' => Carbon::parse($model->paid_date)->format('Y-m-d H:i'),
                            ],
                        ]);

                        $model->paid_date = null;
                        $model->is_paid = false;
                        $model->is_shipped = false;
                        $model->is_refunded = false;

                        $model->save();

                        return;
                    }

                    $model->order_status = OrderStatus::Cart->value;
                    $model->payment_status = PaymentStatus::Unpaid->value;

                    $model->save();
                });
            });
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
