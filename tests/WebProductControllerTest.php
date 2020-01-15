<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WebProductControllerTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations;

    /** @test */
    public function can_get_index_of_products()
    {
        dd('hi there!');

        $response = $this->get('/products');

        $response->assertOk();
    }
}
