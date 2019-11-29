<?php

namespace Damcclean\Commerce\Tests;

use Statamic\Providers\StatamicServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
    use CreatesApplication;

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class
        ];
    }
}
