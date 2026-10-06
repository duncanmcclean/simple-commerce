<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Helpers;

use DuncanMcClean\SimpleCommerce\Contracts\CustomerRepository;
use DuncanMcClean\SimpleCommerce\Contracts\OrderRepository;
use DuncanMcClean\SimpleCommerce\Customers\CustomerModel;
use DuncanMcClean\SimpleCommerce\Customers\EloquentCustomerRepository;
use DuncanMcClean\SimpleCommerce\Orders\EloquentOrderRepository;
use DuncanMcClean\SimpleCommerce\Orders\OrderModel;
use Illuminate\Support\Facades\File;

trait UseDatabaseContentDrivers
{
    public function setUpDatabaseContentDrivers()
    {
        $this->stubsPath = __DIR__.'/../__fixtures__/database/migrations';

        if (count(File::glob(database_path('migrations').'/*_create_customers_table.php')) < 1) {
            File::copy($this->stubsPath.'/create_customers_table.php', database_path('migrations/'.date('Y_m_d_His').'_create_customers_table.php'));
        }

        if (count(File::glob(database_path('migrations').'/*_create_orders_table.php')) < 1) {
            File::copy($this->stubsPath.'/create_orders_table.php', database_path('migrations/'.date('Y_m_d_His').'_create_orders_table.php'));
        }

        if (count(File::glob(database_path('migrations').'/*_create_status_log_table.php')) < 1) {
            File::copy($this->stubsPath.'/create_status_log_table.php', database_path('migrations/'.date('Y_m_d_His').'_create_status_log_table.php'));
        }

        $this->runLaravelMigrations();

        $this->app['config']->set('simple-commerce.content.customers', [
            'repository' => EloquentCustomerRepository::class,
            'model' => CustomerModel::class,
        ]);

        $this->app['config']->set('simple-commerce.content.orders', [
            'repository' => EloquentOrderRepository::class,
            'model' => OrderModel::class,
        ]);

        $this->app->bind(
            CustomerRepository::class,
            $this->app['config']->get('simple-commerce.content.customers.repository')
        );

        $this->app->bind(
            OrderRepository::class,
            $this->app['config']->get('simple-commerce.content.orders.repository')
        );
    }
}
