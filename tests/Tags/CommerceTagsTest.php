<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use DoubleThreeDigital\SimpleCommerce\Tags\CommerceTags;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

class CommerceTagsTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations;

    public function setUp() : void
    {
        parent::setUp();

        $this->tag = new CommerceTags();
    }

    /** @test */
    public function commerce_tag_is_registered()
    {
        $this->assertTrue(isset(app()['statamic.tags']['commerce']));
    }

    /** @test */
    public function commerce_currency_code_tag()
    {
        Config::set('commerce.currency', 'USD');

        $currency = factory(Currency::class)->create([
            'iso' => 'USD',
            'symbol' => '$',
            'name' => 'United States Dollar',
        ]);

        $run = $this->tag->currencyCode();

        $this->assertSame($run, 'USD');
    }

    /** @test */
    public function commerce_currency_symbol_tag()
    {
        Config::set('commerce.currency', 'GBP');

        $currency = factory(Currency::class)->create([
            'iso' => 'GBP',
            'symbol' => '£',
            'name' => 'Great British Pound',
        ]);

        $run = $this->tag->currencySymbol();

        $this->assertSame($run, '£');
    }

    /** @test */
    public function commerce_stripe_key_tag()
    {
        Config::set('commerce.stripe.key', 'sk-123456789');

        $run = $this->tag->stripeKey();

        $this->assertSame($run, 'sk-123456789');
    }

    /** @test */
    public function commerce_route_tag()
    {
        // TODO: wait until I get routing sorted for tests
    }

    /** @test */
    public function commerce_categories_tag()
    {
        $categories = factory(ProductCategory::class, 5)->create();

//        $this->tag->setParameters([]);
//        $run = $this->tag->categories();

        // TODO: wait until routes fixed
    }

    /** @test */
    public function commerce_products_tag()
    {
        //
    }

    /** @test */
    public function commerce_countries_tag()
    {
        $countries = factory(Country::class, 15)->create();

        $run = $this->tag->countries();

        $this->assertIsObject($run);
    }

    /** @test */
    public function commerce_states_tag()
    {
        // TODO: once params are done, states requires a country code

//        $states = factory(State::class, 15)->create();
//
////        $run = $this->tag->states();
//
//        $this->assertIsObject($run);
    }

    /** @test */
    public function commerce_currencies_tag()
    {
        $currencies = factory(Currency::class, 15)->create();

        $run = $this->tag->currencies();

        $this->assertIsObject($run);
    }
}
