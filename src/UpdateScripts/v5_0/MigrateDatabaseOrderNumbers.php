<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v5_0;

use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderModel;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Statamic\UpdateScripts\UpdateScript;

class MigrateDatabaseOrderNumbers extends UpdateScript
{
    protected $stubsPath = __DIR__.'/stubs';

    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('5.0.0-beta.1');
    }

    public function update()
    {
        // Skip if the site's not using the EloquentOrderRepository
        if (! $this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            return;
        }

        if (count(File::glob(database_path('migrations').'/*_add_order_number_column_to_orders_table.php')) < 1) {
            File::copy($this->stubsPath.'/add_order_number_column_to_orders_table.php', database_path('migrations/'.date('Y_m_d_His').'_add_order_number_column_to_orders_table.php'));

            Artisan::call('migrate');
        }

        OrderModel::query()
            ->select('id', 'data')
            ->chunk(200, function ($orders) {
                $orders->each(function (OrderModel $order) {
                    $orderNumber = $order->id;

                    if (array_key_exists('title', $order->data)) {
                        $orderNumber = (int) str_replace('#', '', $order->data['title']);
                    }

                    $order->updateQuietly([
                        'order_number' => $orderNumber,
                    ]);
                });
            });
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
