<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use Barryvdh\DomPDF\ServiceProvider as PDFServiceProvider;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\SessionDriver;
use DoubleThreeDigital\SimpleCommerce\ServiceProvider;
use Illuminate\Encryption\Encrypter;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Site;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Stache\Stores\UsersStore;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
    use CollectionSetup;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../src/Orders/Eloquent/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            ServiceProvider::class,
            PDFServiceProvider::class,
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
                'namespace' => 'DoubleThreeDigital\\SimpleCommerce\\',
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
                require(__DIR__."/../vendor/statamic/cms/config/{$config}.php")
            );
        }

        $app['config']->set('app.key', 'base64:'.base64_encode(
            Encrypter::generateKey($app['config']['app.cipher'])
        ));
        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('statamic.stache.stores.users', [
            'class'     => UsersStore::class,
            'directory' => __DIR__.'/__fixtures/users',
        ]);
        $app['config']->set('simple-commerce', require(__DIR__.'/../config/simple-commerce.php'));
        $app['config']->set('simple-commerce.cart.driver', SessionDriver::class);

        Blueprint::setDirectory(__DIR__.'/../resources/blueprints');

        $app['config']->set('statamic.sites.sites', [
            'default' => [
                'name'   => config('app.name'),
                'locale' => 'en_GB',
                'url'    => '/',
            ],
        ]);

        Statamic::booted(function () {
            Site::setCurrent('default');

            $this->setupCollections();
        });
    }
}
