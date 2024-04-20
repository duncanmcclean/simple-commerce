<?php

namespace DuncanMcClean\SimpleCommerce\Tests;

use DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\SessionDriver;
use DuncanMcClean\SimpleCommerce\ServiceProvider;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Tax\Standard\TaxEngine as StandardTaxEngine;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Site;
use Statamic\Stache\Stores\UsersStore;
use Statamic\Statamic;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.users.repository', 'file');

        $app['config']->set('statamic.stache.stores.users', [
            'class' => UsersStore::class,
            'directory' => __DIR__.'/__fixtures__/users',
        ]);

        $app['config']->set('simple-commerce', require (__DIR__.'/../config/simple-commerce.php'));
        $app['config']->set('simple-commerce.cart.driver', SessionDriver::class);

        $app['config']->set('simple-commerce.tax_engine', StandardTaxEngine::class);

        $app['config']->set('statamic.sites.sites', [
            'default' => [
                'name' => config('app.name'),
                'locale' => 'en_GB',
                'url' => '/',
            ],
        ]);

        $app['config']->set('statamic.editions.pro', true);

        $app['config']->set('filesystems.disks.test', [
            'driver' => 'local',
            'root' => storage_path('app'),
        ]);

        Statamic::booted(function () {
            Site::setCurrent('default');

            Blueprint::setDirectory(__DIR__.'/../resources/blueprints');

            AssetContainer::make('assets')->disk('test')->save();
            AssetContainer::make('test')->disk('test')->save();
        });

        $this->ensureContentDirectoriesExist();
    }

    /**
     * Boot the testing helper traits.
     *
     * @return array<class-string, class-string>
     */
    protected function setUpTraits()
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[Helpers\RefreshContent::class])) {
            $this->refreshContent();
        }

        if (isset($uses[Helpers\SetupCollections::class])) {
            $this->setupCollections();
        }

        if (isset($uses[Helpers\UseDatabaseContentDrivers::class])) {
            $this->setUpDatabaseContentDrivers();
        }

        return $this->setUpTheTestEnvironmentTraits($uses);
    }

    protected function ensureContentDirectoriesExist(): void
    {
        if (! file_exists(base_path('content'))) {
            mkdir(base_path('content'));
        }

        if (! file_exists(base_path('content/simple-commerce'))) {
            mkdir(base_path('content/simple-commerce'));
        }

        if (! file_exists(base_path('content/simple-commerce/coupons'))) {
            mkdir(base_path('content/simple-commerce/coupons'));
        }

        if (! file_exists(base_path('content/simple-commerce/tax-categories'))) {
            mkdir(base_path('content/simple-commerce/tax-categories'));
        }

        if (! file_exists(base_path('content/simple-commerce/tax-rates'))) {
            mkdir(base_path('content/simple-commerce/tax-rates'));
        }

        if (! file_exists(base_path('content/simple-commerce/tax-zones'))) {
            mkdir(base_path('content/simple-commerce/tax-zones'));
        }
    }

    protected function useBasicTaxEngine()
    {
        SimpleCommerce::setTaxEngine(\DuncanMcClean\SimpleCommerce\Tax\BasicTaxEngine::class);
    }

    protected function useStandardTaxEngine()
    {
        SimpleCommerce::setTaxEngine(\DuncanMcClean\SimpleCommerce\Tax\Standard\TaxEngine::class);
    }
}
