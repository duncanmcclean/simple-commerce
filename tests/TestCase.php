<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use DoubleThreeDigital\SimpleCommerce\ServiceProvider;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        require_once(__DIR__.'/ExceptionHandler.php');

        parent::setUp();

        $this->withFactories(realpath(__DIR__.'/../database/factories'));
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
                'id' => 'doublethreedigital/simple-commerce',
                'namespace' => 'DoubleThreeDigital\\SimpleCommerce\\',
            ],
        ];

        Statamic::pushWebRoutes(function() {
            return require_once realpath(__DIR__.'/../routes/web.php');
        });

        Statamic::pushCpRoutes(function() {
            return require_once realpath(__DIR__.'/../routes/cp.php');
        });
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = [
            'assets', 'cp', 'forms', 'static_caching',
            'sites', 'stache', 'system', 'users'
        ];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require(__DIR__."/../vendor/statamic/cms/config/{$config}.php"));
        }

        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('commerce', require(__DIR__.'/../config/commerce.php'));
    }
}
