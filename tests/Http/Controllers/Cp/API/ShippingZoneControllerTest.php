<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp\API;

use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class ShippingZoneControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $currency = factory(Currency::class)->create();
        Config::set('simple-commerce.currency.iso', $currency->iso);
    }

    /** @test */
    public function can_index_shipping_zones()
    {
        $zones = factory(ShippingZone::class, 5)->create();

        $this
            ->actAsSuper()
            ->get(cp_route('commerce-api.shipping-zones.index'))
            ->assertOk();
    }

    /** @test */
    public function can_store_shipping_zone()
    {
        $this
            ->actAsSuper()
            ->post(cp_route('commerce-api.shipping-zones.store'), [
                'country' => [factory(Country::class)->create()->id],
                'state' => [factory(State::class)->create()->id],
                'start_of_zip_code' => 'G72 A12',
                'price' => '10.25',
            ])
            ->assertCreated();
    }

    /** @test */
    public function can_update_shipping_zone()
    {
        $zone = factory(ShippingZone::class)->create();

        $this
            ->actAsSuper()
            ->post(cp_route('commerce-api.shipping-zones.update', ['zone' => $zone->uuid]), [
                'country' => [$zone->country_id],
                'state' => [$zone->state_id],
                'start_of_zip_code' => $zone->start_of_zip_code,
                'price' => '10.30',
            ])
            ->assertOk();
    }

    /** @test */
    public function can_destroy_shipping_zone()
    {
        $zone = factory(ShippingZone::class)->create();

        $this
            ->actAsSuper()
            ->get(cp_route('commerce-api.shipping-zones.destroy', ['zone' => $zone->uuid]))
            ->assertRedirect();
    }
}
