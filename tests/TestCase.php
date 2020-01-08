<?php

namespace Damcclean\Commerce\Tests;

use Damcclean\Commerce\CommerceServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        require_once(__DIR__.'/ExceptionHandler.php');

        parent::setUp();
    }

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
            'Statamic' => Statamic::class,
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('commerce', require(__DIR__.'/../config/commerce.php'));
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make(\Statamic\Extend\Manifest::class)->manifest = [
            'damcclean/commerce' => [
                'id' => 'damcclean/commerce',
                'namespace' => 'Damcclean\\Commerce\\',
            ],
        ];
    }
}
