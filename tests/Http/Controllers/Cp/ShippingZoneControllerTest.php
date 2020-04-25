<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp;

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
                    $uk->id,
                ],
                'rates' => [
                    'name' => '2nd Class',
                    'type' => 'price-based',
                    'minimum' => 0,
                    'maximum' => 100,
                    'rate' => 2.50,
                    'note' => '',
                ],
            ])
            ->assertCreated();
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
                    $zone->country_id,
                ],
                'rates' => [
                    'name' => $zone['rates'][0]['name'],
                    'type' => $zone['rates'][0]['type'],
                    'minimum' => $zone['rates'][0]['minimum'],
                    'maximum' => $zone['rates'][0]['maximum'],
                    'rate' => $zone['rates'][0]['rate'],
                    'note' => $zone['rates'][0]['note'],
                ],
            ])
            ->assertOk();
    }

    /** @test */
    public function can_destroy_shipping_zone()
    {
        $zone = factory(ShippingZone::class)->create();

        $this
            ->actAsSuper()
            ->delete(cp_route('shipping-zones.destroy', ['zone' => $zone->uuid]))
            ->assertRedirect();

        $this->assertDatabaseMissing('shipping_zones', [
            'id' => $zone->id,
        ])    
    }
}
