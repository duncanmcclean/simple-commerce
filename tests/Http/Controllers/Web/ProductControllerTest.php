<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_index_of_products()
    {
        $response = $this->get('/products');

        dd($response);
    }
}
