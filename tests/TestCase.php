<?php

namespace Tests;

use DuncanMcClean\SimpleCommerce\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.editions.pro', true);
        $app['config']->set('simple-commerce', require (__DIR__.'/../config/simple-commerce.php'));
    }
}
