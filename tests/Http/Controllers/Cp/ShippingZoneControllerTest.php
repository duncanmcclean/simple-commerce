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
        $rate = factory(ShippingRate::class)->create(['shipping_zone_id' => $zone->id]);

        $this
            ->actAsSuper()
            ->post(cp_route('shipping-zones.update', ['zone' => $zone->uuid]), [
                'name' => 'New Shipping Zone Name',
                'countries' => [
                    0 => $zone->country_id,
                ],
                'rates' => [
                    $rate->toArray(),
                ],
            ])
            ->assertRedirect();
    }

    /** @test */
    public function can_update_shipping_zone_and_remove_country()
    {
        $zone = factory(ShippingZone::class)->create();
        $rate = factory(ShippingRate::class)->create(['shipping_zone_id' => $zone->id]);
        $countries = factory(Country::class, 2)->create(['shipping_zone_id' => $zone->id]);

        $this
            ->actAsSuper()
            ->post(cp_route('shipping-zones.update', ['zone' => $zone->uuid]), [
                'name' => 'New Shipping Zone Name',
                'countries' => [
                    0 => $countries[0]['id'],
                ],
                'rates' => [
                    $rate->toArray(),
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('countries', [
            'id' => $countries[0]['id'],
            'shipping_zone_id' => $zone->id,
        ]);
        
        $this->assertDatabaseMissing('countries', [
            'id' => $countries[1]['id'],
            'shipping_zone_id' => $zone->id,
        ]);
    }

    /** @test */
    public function can_update_shipping_zone_and_add_country()
    {
        $zone = factory(ShippingZone::class)->create();
        $rate = factory(ShippingRate::class)->create(['shipping_zone_id' => $zone->id]);

        $zoneCountry = factory(Country::class)->create(['shipping_zone_id' => $zone->id]);
        $otherCountry = factory(Country::class)->create();

        $this
            ->actAsSuper()
            ->post(cp_route('shipping-zones.update', ['zone' => $zone->uuid]), [
                'name' => 'New Shipping Zone Name',
                'countries' => [
                    0 => $zoneCountry->id,
                    1 => $otherCountry->id,
                ],
                'rates' => [
                    $rate->toArray(),
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('countries', [
            'id' => $zoneCountry->id,
            'shipping_zone_id' => $zone->id,
        ]);
        
        $this->assertDatabaseHas('countries', [
            'id' => $otherCountry->id,
            'shipping_zone_id' => $zone->id,
        ]);
    }

    /** @test */
    public function can_update_shipping_zone_and_create_shipping_rate()
    {
        $zone = factory(ShippingZone::class)->create();
        $rate = factory(ShippingRate::class)->create(['shipping_zone_id' => $zone->id]);
        $country = factory(Country::class)->create(['shipping_zone_id' => $zone->id]);

        $this
            ->actAsSuper()
            ->post(cp_route('shipping-zones.update', ['zone' => $zone->uuid]), [
                'name' => 'New Shipping Zone Name',
                'countries' => [
                    0 => $country->id,
                ],
                'rates' => [
                    $rate->toArray(),
                    [
                        'name' => 'Premium Postage',
                        'type' => 'price-based',
                        'minimum' => 50.00,
                        'maximum' => 250.00,
                        'rate' => 1.00,
                    ],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('shipping_rates', [
            'id' => $rate->id,
            'shipping_zone_id' => $zone->id,
        ]);

        $this->assertDatabaseHas('shipping_rates', [
            'name' => 'Premium Postage',
            'shipping_zone_id' => $zone->id,
        ]);
    }

    /** @test */
    public function can_update_shipping_zone_and_update_shipping_rate()
    {
        $zone = factory(ShippingZone::class)->create();
        $rate = factory(ShippingRate::class)->create(['shipping_zone_id' => $zone->id]);
        $country = factory(Country::class)->create(['shipping_zone_id' => $zone->id]);

        $this
            ->actAsSuper()
            ->post(cp_route('shipping-zones.update', ['zone' => $zone->uuid]), [
                'name' => 'New Shipping Zone Name',
                'countries' => [
                    0 => $country->id,
                ],
                'rates' => [
                    [
                        'uuid' => $rate->uuid,
                        'name' => 'Updated rate',
                        'type' => 'price-based',
                        'minimum' => 50.00,
                        'maximum' => 250.00,
                        'rate' => 1.00,
                    ],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('shipping_rates', [
            'id' => $rate->id,
            'name' => 'Updated rate',
            'shipping_zone_id' => $zone->id,
        ]);

    }

    /** @test */
    public function can_update_shipping_zone_and_delete_shipping_rate()
    {
        $zone = factory(ShippingZone::class)->create();
        $rates = factory(ShippingRate::class, 2)->create(['shipping_zone_id' => $zone->id]);
        $country = factory(Country::class)->create(['shipping_zone_id' => $zone->id]);

        $this
            ->actAsSuper()
            ->post(cp_route('shipping-zones.update', ['zone' => $zone->uuid]), [
                'name' => 'New Shipping Zone Name',
                'countries' => [
                    0 => $country->id,
                ],
                'rates' => [
                    [
                        'uuid' => $rates[0]['uuid'],
                        'name' => $rates[0]['name'],
                        'type' => $rates[0]['type'],
                        'minimum' => $rates[0]['minimum'],
                        'maximum' => $rates[0]['maximum'],
                        'rate' => $rates[0]['rate'],
                    ],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('shipping_rates', [
            'id' => $rates[0]['id'],
            'shipping_zone_id' => $zone->id,
        ]);

        $this->assertDatabaseMissing('shipping_rates', [
            'id' => $rates[1]['id'],
            'shipping_zone_id' => $zone->id,
        ]);
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
