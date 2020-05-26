<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\Models\Coupon;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;

class InstallCommand extends Command
{
    use RunsInPlease;

    protected $signature = 'simple-commerce:install';
    protected $description = 'Install Simple Commerce';

    public function handle()
    {
        if (! $this->confirm('Have you already setup a database?')) {
            $this->error('Please setup a database then run this command again.');

            return;
        }

        $this
            ->publish()
            ->migrate()
            ->seed()
            ->seedTestData()
            ->replaceUserModel();
    }

    protected function publish()
    {
        $this->call('vendor:publish', ['--tag' => 'simple-commerce-config']);
        $this->call('vendor:publish', ['--tag' => 'simple-commerce-blueprints']);
        $this->call('vendor:publish', ['--tag' => 'simple-commerce-fieldsets']);
        $this->call('vendor:publish', ['--tag' => 'simple-commerce-assets']);

        return $this;
    }

    protected function migrate()
    {
        $this->call('migrate');

        return $this;
    }

    protected function seed()
    {
        $this->call('db:seed', ['--class' => 'DoubleThreeDigital\SimpleCommerce\Seeders\CountrySeeder']);
        $this->call('db:seed', ['--class' => 'DoubleThreeDigital\SimpleCommerce\Seeders\CurrencySeeder']);
        $this->call('db:seed', ['--class' => 'DoubleThreeDigital\SimpleCommerce\Seeders\OrderStatusSeeder']);
        $this->call('db:seed', ['--class' => 'DoubleThreeDigital\SimpleCommerce\Seeders\StateSeeder']);

        return $this;
    }

    protected function seedTestData()
    {
        if ($this->confirm('Would you like test data to be added to your database?')) {
            $coupon = Coupon::create([
                'name' => '',
                'code' => '',
                'type' => '',
                'value' => '',
                'minimum_total' => '',
                'total_uses' => '',
                'start_date' => '',
                'end_date' => '',
            ]);


            // product data
            // variant data
            // coupon data
            // product category data
            // basic tax rate
            // basic shipping zone and rate
        }

        return $this;
    }

    protected function replaceUserModel()
    {
        if ($this->confirm('Would you like for your App\User class to be replaced by Simple Commerce stub?')) {
            copy(__DIR__.'/stubs/AppUser.php.stub', app_path('User.php'));
        } else {
            // TODO: replace this link with one to the docs
            $this->info("That's fine. Follow the instructions over here to make the necessary changes: https://simple-commerce-docs.netlify.app/install.html#the-user-model");
        }

        return $this;
    }
}
