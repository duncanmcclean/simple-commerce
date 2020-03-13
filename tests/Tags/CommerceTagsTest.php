<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Tags\CommerceTags;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Antlers;

class CommerceTagsTest extends TestCase
{
    public $tag;

    use RefreshDatabase;

    public function setUp() : void
    {
        parent::setUp();

        $this->tag = new CommerceTags();

        $this->tag = (new CommerceTags())
            ->setParser(Antlers::parser())
            ->setContext([]);
    }

    /** @test */
    public function commerce_tag_is_registered()
    {
        $this->assertTrue(isset(app()['statamic.tags']['commerce']));
    }

    /** @test */
    public function commerce_currency_code_tag()
    {
        Config::set('simple-commerce.currency.iso', 'USD');

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
        Config::set('simple-commerce.currency.iso', 'GBP');

        $currency = factory(Currency::class)->create([
            'iso' => 'GBP',
            'symbol' => '£',
            'name' => 'Great British Pound',
        ]);

        $run = $this->tag->currencySymbol();

        $this->assertSame($run, '£');
    }

    /** @test */
    public function commerce_route_tag_works_with_key()
    {
        $this->tag->setParameters([
            'key' => 'products.index',
        ]);

        $route = $this->tag->route();

        $this->assertIsString($route);
        $this->assertStringContainsString('/products', $route);
    }

    /** @test */
    public function commerce_route_tag_does_not_work_with_no_key()
    {
        $this->tag->setParameters([]);

        $this->expectExceptionMessage('Please set a route key.');

        $route = $this->tag->route();
    }

    /** @test */
    public function commerce_route_tag_does_not_work_with_invalid_key()
    {
        $this->tag->setParameters([
            'key' => 'fish-tank'
        ]);

        $this->expectExceptionMessage('The route key (fish-tank) you are referencing does not exist.');

        $route = $this->tag->route();
    }

    /** @test */
    public function commerce_categories_tag()
    {
        $categories = factory(ProductCategory::class, 5)->create();

        $run = $this->tag->categories();

        $this->assertIsArray($run);
    }

    /** @test */
    public function commerce_categories_tag_count()
    {
        $categories = factory(ProductCategory::class, 5)->create();

        $this->tag->setParameters(['count' => true]);

        $count = $this->tag->categories();

        $this->assertIsNumeric();
    }

    /** @test */
    public function commerce_products_tag()
    {
        $products = factory(Product::class, 5)->create();

        $run = $this->tag->products();

        $this->assertIsArray($run);
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

    /** @test */
    public function commerce_gateways_tag()
    {
        //
    }
}
