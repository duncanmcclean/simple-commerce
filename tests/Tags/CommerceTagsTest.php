<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Gateways\DummyGateway;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Models\State;
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

        $this->tag->setParameters([]);

        $run = $this->tag->categories();

        $this->assertIsArray($run);
    }

    /** @test */
    public function commerce_categories_tag_count()
    {
        $categories = factory(ProductCategory::class, 5)->create();

        $this->tag->setParameters(['count' => true]);

        $count = $this->tag->categories();

        $this->assertIsNumeric($count);
    }

    /** @test */
    public function commerce_products_tag()
    {
        $products = factory(Product::class, 5)->create();

        $this->tag->setParameters([]);

        $run = $this->tag->products();

        $this->assertIsArray($run);
    }

    /** @test */
    public function commerce_products_tag_in_category()
    {
        $category = factory(ProductCategory::class)->create();
        $products = factory(Product::class, 5)->create([
            'product_category_id' => $category->id,
        ]);

        $this->tag->setParameters([
            'category' => $category->slug,
        ]);

        $run = $this->tag->products();

        $this->assertIsArray($run);
        $this->assertStringContainsString($products[2]['title'], json_encode($run));
    }

    /** @test */
    public function commerce_products_tag_and_include_disabled()
    {
        $enabledProduct = factory(Product::class)->create([
            'is_enabled' => true,
        ]);

        $disabledProduct = factory(Product::class)->create([
            'is_enabled' => false,
        ]);

        $this->tag->setParameters([
            'include_disabled' => true,
        ]);

        $run = $this->tag->products();

        $this->assertIsArray($run);
        $this->assertStringContainsString($enabledProduct->title, json_encode($run));
        $this->assertStringContainsString($disabledProduct->title, json_encode($run));
    }

    /** @test */
    public function commerce_products_tag_not_with_id()
    {
        $keepProduct = factory(Product::class)->create();
        $removeProduct = factory(Product::class)->create();

        $this->tag->setParameters([
            'not' => $removeProduct->id,
        ]);

        $run = $this->tag->products();

        $this->assertIsArray($run);
        $this->assertStringContainsString($keepProduct->title, json_encode($run));
        $this->assertStringNotContainsString($removeProduct->title, json_encode($run));
    }

    /** @test */
    public function commerce_products_tag_not_with_uuid()
    {
        $keepProduct = factory(Product::class)->create();
        $removeProduct = factory(Product::class)->create();

        $this->tag->setParameters([
            'not' => $removeProduct->uuid,
        ]);

        $run = $this->tag->products();

        $this->assertIsArray($run);
        $this->assertStringContainsString($keepProduct->title, json_encode($run));
        $this->assertStringNotContainsString($removeProduct->title, json_encode($run));
    }

    /** @test */
    public function commerce_products_tag_not_with_slug()
    {
        $keepProduct = factory(Product::class)->create();
        $removeProduct = factory(Product::class)->create();

        $this->tag->setParameters([
            'not' => $removeProduct->slug,
        ]);

        $run = $this->tag->products();

        $this->assertIsArray($run);
        $this->assertStringContainsString($keepProduct->title, json_encode($run));
        $this->assertStringNotContainsString($removeProduct->title, json_encode($run));
    }

    /** @test */
    public function commerce_products_tag_limit()
    {
        $products = factory(Product::class, 5)->create();

        $this->tag->setParameters([
            'limit' => 3
        ]);

        $run = $this->tag->products();

        $this->assertIsArray($run);
        $this->assertStringContainsString($products[0]['title'], json_encode($run));
        $this->assertStringNotContainsString($products[4]['title'], json_encode($run));
    }

    /** @test */
    public function commerce_products_tag_count()
    {
        $products = factory(Product::class, 5)->create();

        $this->tag->setParameters([
            'count' => true,
        ]);

        $run = $this->tag->products();

        $this->assertIsInt($run);
        $this->assertSame($run, 5);
    }

    /** @test */
    public function commerce_countries_tag()
    {
        $countries = factory(Country::class, 15)->create();

        $this->tag->setParameters([]);

        $run = $this->tag->countries();

        $this->assertIsArray($run);
        $this->assertStringContainsString($countries[2]['name'], json_encode($run));
        $this->assertStringContainsString($countries[5]['name'], json_encode($run));
        $this->assertStringContainsString($countries[10]['name'], json_encode($run));
        $this->assertStringContainsString($countries[13]['name'], json_encode($run));
    }

    /** @test */
    public function commerce_states_tag()
    {
        $states = factory(State::class, 5)->create();

        $this->tag->setParameters([]);

        $run = $this->tag->states();

        $this->assertIsArray($run);
        $this->assertStringContainsString($states[0]['name'], json_encode($run));
        $this->assertStringContainsString($states[1]['name'], json_encode($run));
        $this->assertStringContainsString($states[2]['name'], json_encode($run));
        $this->assertStringContainsString($states[3]['name'], json_encode($run));
        $this->assertStringContainsString($states[4]['name'], json_encode($run));
    }

    /** @test */
    public function commerce_currencies_tag()
    {
        $currencies = factory(Currency::class, 5)->create();

        $run = $this->tag->currencies();

        $this->assertIsArray($run);
        $this->assertStringContainsString($currencies[0]['name'], json_encode($run));
        $this->assertStringContainsString($currencies[1]['name'], json_encode($run));
        $this->assertStringContainsString($currencies[2]['name'], json_encode($run));
        $this->assertStringContainsString($currencies[3]['name'], json_encode($run));
        $this->assertStringContainsString($currencies[4]['name'], json_encode($run));
    }

    /** @test */
    public function commerce_gateways_tag()
    {
        Config::set('simple-commerce.gateways', [
            DummyGateway::class,
        ]);

        $this->tag->setParameters([]);

        $run = $this->tag->gateways();

        $this->assertIsArray($run);
        $this->assertStringContainsString('Dummy', json_encode($run));
    }
}
