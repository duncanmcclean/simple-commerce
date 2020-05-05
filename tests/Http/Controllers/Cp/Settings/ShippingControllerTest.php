<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp\Settings;

use Illuminate\Support\Facades\Config;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class ShippingControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $currency = factory(Currency::class)->create();
        Config::set('simple-commerce.currency.iso', $currency->iso);
    }

    /** @test */
    public function can_get_index()
    {
        $this
            ->actAsSuper()
            ->get(cp_route('settings.shipping.index'))
            ->assertOk()
            ->assertSee('Shipping Zones');
    }
}
