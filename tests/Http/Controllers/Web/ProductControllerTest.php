<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations;

    /** @test */
    public function can_get_index_of_products()
    {
        // TODO: for some reason routes are not being loaded in
    }
}
