<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Console;

use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    /** @test */
    public function it_asks_user_for_confirmation_of_database_before_starting()
    {
        $this
            ->artisan('simple-commerce:install')
            ->expectsConfirmation('Have you already setup a database?');
    }

    /** @test */
    public function an_error_is_shown_if_database_has_not_been_setup()
    {
        $this
            ->artisan('simple-commerce:install')
            ->expectsConfirmation('Have you already setup a database?', 'no')
            ->expectsOutput('Please setup a database then run this command again.')
            ->assertExitCode(0);
    }
}
