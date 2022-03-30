<?php

namespace DoubleThreeDigital\SimpleCommerce\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\Coupons\Coupon;
use DoubleThreeDigital\SimpleCommerce\Customers\Customer;
use DoubleThreeDigital\SimpleCommerce\Orders\Order;
use DoubleThreeDigital\SimpleCommerce\Products\Product;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Collection;

class InstallCommand extends Command
{
    use RunsInPlease;

    protected $name = 'sc:install';
    protected $description = 'Install Simple Commerce';

    public function handle()
    {
        $this
            ->publishBlueprints()
            ->publishConfigurationFile()
            ->setupCollections();
    }

    protected function publishBlueprints(): self
    {
        $this->info('Publishing Blueprints');

        $this->callSilent('vendor:publish', [
            '--tag' => 'simple-commerce-blueprints',
        ]);

        return $this;
    }

    protected function publishConfigurationFile(): self
    {
        $this->info('Publishing Config file');

        $this->callSilent('vendor:publish', [
            '--tag' => 'simple-commerce-config',
        ]);

        return $this;
    }

    protected function setupCollections()
    {
        $productDriver = SimpleCommerce::productDriver();
        $customerDriver = SimpleCommerce::customerDriver();
        $orderDriver = SimpleCommerce::orderDriver();
        $couponDriver = SimpleCommerce::couponDriver();

        if ($productDriver['repository'] === Product::class && ! Collection::handleExists($productDriver['collection'])) {
            $this->info('Creating: Products');

            Collection::make($productDriver['collection'])
                ->title(Str::title($productDriver['collection']))
                ->pastDateBehavior('public')
                ->futureDateBehavior('private')
                ->sites(['default'])
                ->routes('/products/{slug}')
                ->save();
        } else {
            $this->warn('Skipping: Products');
        }

        if ($customerDriver['repository'] === Customer::class && ! Collection::handleExists($customerDriver['collection'])) {
            $this->info('Creating: Customers');

            Collection::make($customerDriver['collection'])
                ->title(Str::title($customerDriver['collection']))
                ->sites(['default'])
                ->save();
        } else {
            $this->warn('Skipping: Customers');
        }

        if ($orderDriver['repository'] === Order::class && ! Collection::handleExists($orderDriver['collection'])) {
            $this->info('Creating: Orders');

            Collection::make($orderDriver['collection'])
                ->title(Str::title($orderDriver['collection']))
                ->sites(['default'])
                ->save();
        } else {
            $this->warn('Skipping: Orders');
        }

        if ($couponDriver['repository'] === Coupon::class && ! Collection::handleExists($couponDriver['collection'])) {
            $this->info('Creating: Coupons');

            Collection::make($couponDriver['collection'])
                ->title(Str::title($couponDriver['collection']))
                ->sites(['default'])
                ->save();
        } else {
            $this->warn('Skipping: Coupons');
        }

        return $this;
    }
}
