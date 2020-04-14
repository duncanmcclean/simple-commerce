<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Actions;

use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CartControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        factory(Currency::class)->create();
    }

    //
}
