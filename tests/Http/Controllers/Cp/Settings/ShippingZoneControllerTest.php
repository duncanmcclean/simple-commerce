<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp\Settings;

use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class ShippingZoneControllerTest extends TestCase
{
    /** @test */
    public function can_get_index()
    {
        $this
            ->actAsSuper()
            ->get(cp_route('settings.shipping-zones.index'))
            ->assertOk()
            ->assertSee('Shipping Zones');
    }
}
