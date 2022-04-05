<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\SessionDriver;
use DoubleThreeDigital\SimpleCommerce\ServiceProvider;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxEngine as StandardTaxEngine;
use Facades\Statamic\Version;
use Illuminate\Encryption\Encrypter;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Console\Processes\Composer;
use Statamic\Extend\Manifest;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Site;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Stache\Stores\UsersStore;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
    protected $shouldFakeVersion = true;

    public function setUp(): void
    {
        parent::setUp();

        if ($this->shouldFakeVersion) {
            Version::shouldReceive('get')
                ->andReturn(Composer::create(__DIR__ . '/../')->installedVersion(Statamic::PACKAGE));

            $this->addToAssertionCount(-1);
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Manifest::class)->manifest = [
            'doublethreedigital/simple-commerce' => [
                'id'        => 'doublethreedigital/simple-commerce',
                'namespace' => 'DoubleThreeDigital\\SimpleCommerce',
            ],
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = [
            'assets',
            'cp',
            'forms',
            'static_caching',
            'sites',
            'stache',
            'system',
            'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set(
                "statamic.$config",
                require(__DIR__ . "/../vendor/statamic/cms/config/{$config}.php")
            );
        }

        $app['config']->set('app.key', 'base64:' . base64_encode(
            Encrypter::generateKey($app['config']['app.cipher'])
        ));

        $app['config']->set('statamic.users.repository', 'file');

        $app['config']->set('statamic.stache.stores.users', [
            'class'     => UsersStore::class,
            'directory' => __DIR__ . '/__fixtures__/users',
        ]);

        $app['config']->set('simple-commerce', require(__DIR__ . '/../config/simple-commerce.php'));
        $app['config']->set('simple-commerce.cart.driver', SessionDriver::class);

        $app['config']->set('simple-commerce.tax_engine', StandardTaxEngine::class);

        $app['config']->set('statamic.sites.sites', [
            'default' => [
                'name'   => config('app.name'),
                'locale' => 'en_GB',
                'url'    => '/',
            ],
        ]);

        $app['config']->set('statamic.editions.pro', true);

        Statamic::booted(function () {
            Site::setCurrent('default');

            Blueprint::setDirectory(__DIR__ . '/../resources/blueprints');
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

        if (isset($uses[RefreshContent::class])) {
            $this->refreshContent();
        }

        if (isset($uses[SetupCollections::class])) {
            $this->setupCollections();
        }

        if (isset($uses[UseDatabaseContentDrivers::class])) {
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
        SimpleCommerce::setTaxEngine(\DoubleThreeDigital\SimpleCommerce\Tax\BasicTaxEngine::class);
    }

    protected function useStandardTaxEngine()
    {
        SimpleCommerce::setTaxEngine(\DoubleThreeDigital\SimpleCommerce\Tax\Standard\TaxEngine::class);
    }
}
