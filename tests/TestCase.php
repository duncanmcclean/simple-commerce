<?php

namespace Damcclean\Commerce\Tests;

use Damcclean\Commerce\CommerceServiceProvider;
use Statamic\Providers\StatamicServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            CommerceServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = [
            'assets', 'cp', 'forms', 'routes', 'static_caching',
            'sites', 'stache', 'system', 'users'
        ];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require(__DIR__."/../vendor/statamic/cms/config/{$config}.php"));
        }

        $app['config']->set('commerce', require(__DIR__.'/../config/commerce.php'));
    }
}
