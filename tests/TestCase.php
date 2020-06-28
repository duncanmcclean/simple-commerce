<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use Aerni\Factory\Factory;
use Aerni\Factory\Mapper;
use Statamic\Extend\Manifest;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use DoubleThreeDigital\SimpleCommerce\ServiceProvider;
use Faker\Generator as Faker;
use Generator;
use Statamic\Facades\Blueprint;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
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

        $app['config']->set('factory', [
            'published' => true,
            'title' => [
                'chars' => [$min = 10, $max = 20],
                'real_text' => false,
            ],
        ]);

        Blueprint::setDirectory(__DIR__.'/../resources/blueprints');
    }

    protected function factory()
    {
        $factory = new Factory(new Faker(), new Mapper);

        return $factory;
    }
}
