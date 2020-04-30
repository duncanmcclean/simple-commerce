<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Console;

use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Facades\Statamic\Console\Processes\Composer;

class VersionCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Composer::swap(new \Statamic\Console\Processes\Composer(__DIR__.'/../__fixtures__/'));
    }

    /** @test */
    public function can_get_version()
    {
        $this->markTestIncomplete();

//        $this
//            ->artisan('simple-commerce:version')
//            ->expectsOutput('You are running Simple Commerce <Any>')
//            ->assertExitCode(0);
    }
}
