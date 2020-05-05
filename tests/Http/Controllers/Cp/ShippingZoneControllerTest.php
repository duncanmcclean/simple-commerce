<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingRate;
use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
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
            ->get(cp_route('shipping-zones.index'))
            ->assertOk();
    }

    /** @test */
    public function can_store_shipping_zone()
    {
        $uk = factory(Country::class)->create([
            'name' => 'United Kingdom',
            'iso' => 'UK',
        ]);

        $this
            ->actAsSuper()
            ->post(cp_route('shipping-zones.store'), [
                'name' => 'United Kingdom',
                'countries' => [
                    0 => $uk->id,
                ],
                'rates' => [
                    [
                        'name' => '2nd Class',
                        'type' => 'price-based',
                        'minimum' => '0',
                        'maximum' => '100',
                        'rate' => '2.50',
                        'note' => 'Delivery within 2-3 days',
                    ],
                ],
            ])
            ->assertCreated();
    }
    
    /** @test */
    public function can_edit_shipping_zone()
    {
        $zone = factory(ShippingZone::class)->create();
        $rates = factory(ShippingRate::class, 2)->create(['shipping_zone_id' => $zone->id]);

        $this
            ->actAsSuper()
            ->get(cp_route('shipping-zones.edit', ['zone' => $zone->uuid]))
            ->assertOk()
            ->assertSee($zone->name)
            ->assertSee($rates[0]['name'])
            ->assertSee($rates[1]['name']);
    }

    /** @test */
    public function can_update_shipping_zone()
    {
        $zone = factory(ShippingZone::class)->create();

        $this
            ->actAsSuper()
            ->post(cp_route('shipping-zones.update', ['zone' => $zone->uuid]), [
                'name' => 'New Shipping Zone Name',
                'countries' => [
                    0 => $zone->country_id,
                ],
                'rates' => [
                    [
                        'name' => '2nd Class',
                        'type' => 'price-based',
                        'minimum' => '0',
                        'maximum' => '100',
                        'rate' => '2.50',
                        'note' => 'Delivery within 2-3 days',
                    ],
                ],
            ])
            ->assertRedirect();
    }

    /** @test */
    public function can_destroy_shipping_zone()
    {
        $zone = factory(ShippingZone::class)->create();

        $this
            ->actAsSuper()
            ->delete(cp_route('shipping-zones.destroy', ['zone' => $zone->uuid]))
            ->assertOk();

        $this->assertDatabaseMissing('shipping_zones', [
            'id' => $zone->id,
        ]);    
    }
}
