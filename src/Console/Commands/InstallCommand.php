<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Coupon;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
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
            // Coupons
            $freeShipping = Coupon::create([
                'name' => 'Free Shipping',
                'code' => 'FREE-SHIPPING',
                'type' => 'free_shipping',
                'value' => 0,
                'minimum_total' => 0,
                'total_uses' => 0,
                'start_date' => null,
                'end_date' => null,
            ]);

            $tenPercentOff = Coupon::create([
                'name' => '10% Off Everything',
                'code' => '10-OFF',
                'type' => 'percent_discount',
                'value' => 10,
                'minimum_total' => 0,
                'total_uses' => 0,
                'start_date' => null,
                'end_date' => null,
            ]);

            // Tax Rates
            $standardtaxRate = TaxRate::create([
                'name' => 'Standard Tax Rate',
                'description' => 'The standard 20% tax rate for UK goods.',
                'rate' => 20,
            ]);

            // Product Categories
            $desks = ProductCategory::create([
                'title' => 'Office Desks',
                'slug' => 'office-desks',
            ]);

            $monitors = ProductCategory::create([
                'title' => 'Monitors',
                'slug' => 'monitors',
            ]);

            // Products
            $normalDesk = Product::create([
                'title' => 'Standard Desk',
                'slug' => 'standard-desk',
                'is_enabled' => true,
                'needs_shipping' => true,
                'tax_rate_id' => $standardtaxRate->id,
            ]);
            $normalDesk->productCategories()->attach($desks->id);
            $normalDesk->variants()->create([
                'name' => 'Black Top',
                'sku' => 'STAN_DES_BLA',
                'description' => 'This variant of the standard desk has a black top.',
                'images' => [],
                'weight' => 500,
                'price' => 250,
                'stock' => 50,
                'unlimited_stock' => false,
                'max_quantity' => 5,
            ]);
            $normalDesk->variants()->create([
                'name' => 'Wooden Top',
                'sku' => 'STAN_DES_WOO',
                'description' => 'This variant of the standard desk has a wooden top.',
                'images' => [],
                'weight' => 500,
                'price' => 250,
                'stock' => 50,
                'unlimited_stock' => false,
                'max_quantity' => 5,
            ]);

            $standingDesk = Product::create([
                'title' => 'Standing Desk',
                'slug' => 'standing-desk',
                'is_enabled' => true,
                'needs_shipping' => true,
                'tax_rate_id' => $standardtaxRate->id,
            ]);
            $standingDesk->productCategories()->attach($desks->id);
            $standingDesk->variants()->create([
                'name' => 'Black Top',
                'sku' => 'STAND_DES_BLA',
                'description' => 'This variant of the standing desk has a black top.',
                'images' => [],
                'weight' => 500,
                'price' => 450,
                'stock' => 50,
                'unlimited_stock' => false,
                'max_quantity' => 5,
            ]);
            $standingDesk->variants()->create([
                'name' => 'Wooden Top',
                'sku' => 'STAND_DES_WOO',
                'description' => 'This variant of the standing desk has a wooden top.',
                'images' => [],
                'weight' => 500,
                'price' => 450,
                'stock' => 50,
                'unlimited_stock' => false,
                'max_quantity' => 5,
            ]);

            $fourKMonitor = Product::create([
                'title' => '4K Monitor',
                'slug' => '4k-monitor',
                'is_enabled' => true,
                'needs_shipping' => true,
                'tax_rate_id' => $standardtaxRate->id,
            ]);
            $fourKMonitor->productCategories()->attach($monitors->id);
            $fourKMonitor->variants()->create([
                'name' => '23 inches',
                'sku' => '4K_MON_23IN',
                'description' => 'This variant of the 4k monitor is 23 inches.',
                'images' => [],
                'weight' => 50,
                'price' => 160,
                'stock' => 25,
                'unlimited_stock' => false,
                'max_quantity' => 2,
            ]);
            $fourKMonitor->variants()->create([
                'name' => '31 inches',
                'sku' => '4K_MON_31IN',
                'description' => 'This variant of the 4k monitor is 31 inches.',
                'images' => [],
                'weight' => 50,
                'price' => 460,
                'stock' => 25,
                'unlimited_stock' => false,
                'max_quantity' => 2,
            ]);
            $fourKMonitor->variants()->create([
                'name' => '42 inches',
                'sku' => '4K_MON_42IN',
                'description' => 'This variant of the 4k monitor is 42 inches.',
                'images' => [],
                'weight' => 50,
                'price' => 740,
                'stock' => 25,
                'unlimited_stock' => false,
                'max_quantity' => 2,
            ]);

            // Shipping
            $ukShippingZone = ShippingZone::create([
                'name' => 'United Kingdom',
            ]);
            $ukShippingZone->rates()->create([
                'name' => 'Royal Mail 2nd Class',
                'type' => 'price-based',
                'minimum' => 0,
                'maximum' => 150,
                'rate' => 5.20,
            ]);
            $ukShippingZone->rates()->create([
                'name' => 'Royal Mail 1st Class',
                'type' => 'price-based',
                'minimum' => 151,
                'maximum' => 1000,
                'rate' => 9.99,
            ]);
            Country::where('iso', 'GB')
                ->first()
                ->update(['shipping_zone_id' => $ukShippingZone->id]);
        }

        return $this;
    }

    protected function replaceUserModel()
    {
        if ($this->confirm('Would you like for your App\User class to be replaced by Simple Commerce stub?')) {
            copy(__DIR__.'/stubs/AppUser.php.stub', app_path('User.php'));
        } else {
            $this->info("That's fine. Follow the instructions over here to make the necessary changes: https://simple-commerce.doublethree.digital/install.html#the-user-model");
        }

        return $this;
    }
}
