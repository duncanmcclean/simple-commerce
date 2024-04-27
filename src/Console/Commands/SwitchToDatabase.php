<?php

namespace DuncanMcClean\SimpleCommerce\Console\Commands;

use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Statamic\Console\Processes\Composer;
use Statamic\Console\RunsInPlease;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class SwitchToDatabase extends Command
{
    use RunsInPlease;

    protected $name = 'sc:switch-to-database';

    protected $description = 'Switch your site to using a database for orders & customers.';

    protected $stubsPath;

    public function __construct()
    {
        parent::__construct();

        $this->stubsPath = __DIR__.'/stubs';
    }

    public function handle()
    {
        if (app()->environment('production')) {
            return $this->components->error('You should not run this command in production. Please switch locally first, then deploy the changes.');
        }

        if (! Composer::create()->isInstalled('statamic-rad-pack/runway')) {
            return $this->components->error('You need to install Runway before running this command. Run `composer require statamic-rad-pack/runway` first.');
        }

        $this
            ->copyMigrationStubs()
            ->copyBlueprintStubs()
            ->publishRunwayConfig()
            ->switchRepositories();

        $this->line('Next steps...');
        $this->components->bulletList([
            'Run `php artisan migrate`',
            'Test your site thoroughly',
            'Run the migrator command to migrate any existing customers & orders',
        ]);
    }

    protected function copyMigrationStubs(): self
    {
        if (count(File::glob(database_path('migrations').'/*_create_customers_table.php')) < 1) {
            File::copy($this->stubsPath.'/create_customers_table.php', database_path('migrations/'.date('Y_m_d_His').'_create_customers_table.php'));
        }

        if (count(File::glob(database_path('migrations').'/*_create_orders_table.php')) < 1) {
            File::copy($this->stubsPath.'/create_orders_table.php', database_path('migrations/'.date('Y_m_d_His').'_create_orders_table.php'));
        }

        if (count(File::glob(database_path('migrations').'/*_create_status_log_table.php')) < 1) {
            File::copy($this->stubsPath.'/create_status_log_table.php', database_path('migrations/'.date('Y_m_d_His').'_create_status_log_table.php'));
        }

        $this->components->info('Copied migration stubs successfully');

        return $this;
    }

    protected function copyBlueprintStubs(): self
    {
        File::ensureDirectoryExists(resource_path('blueprints/vendor/runway'));

        if (! File::exists(resource_path('blueprints/vendor/runway/customers.yaml'))) {
            File::copy($this->stubsPath.'/runway_customer_blueprint.yaml', resource_path('blueprints/vendor/runway/customers.yaml'));
        }

        if (! File::exists(resource_path('blueprints/vendor/runway/orders.yaml'))) {
            File::copy($this->stubsPath.'/runway_order_blueprint.yaml', resource_path('blueprints/vendor/runway/orders.yaml'));
        }

        $this->components->info('Copied blueprint stubs successfully');

        return $this;
    }

    protected function publishRunwayConfig(): self
    {
        if (! File::exists(config_path('runway.php'))) {
            File::copy($this->stubsPath.'/runway_config.php', config_path('runway.php'));
        }

        $this->components->info('Published Runway config file successfully');

        return $this;
    }

    protected function switchRepositories(): self
    {
        if (isset(SimpleCommerce::orderDriver()['model'])) {
            return $this;
        }

        ConfigWriter::edit('simple-commerce')
            ->replace('content.orders', [
                'repository' => \DuncanMcClean\SimpleCommerce\Orders\EloquentOrderRepository::class,
                'model' => \DuncanMcClean\SimpleCommerce\Orders\OrderModel::class,
            ])
            ->replace('content.customers', [
                'repository' => \DuncanMcClean\SimpleCommerce\Customers\EloquentCustomerRepository::class,
                'model' => \DuncanMcClean\SimpleCommerce\Customers\CustomerModel::class,
            ])
            ->save();

        $this->components->info('Switched to database repositories successfully');

        return $this;
    }
}
