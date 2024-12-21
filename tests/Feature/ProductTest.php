<?php

namespace Tests\Feature;

use DuncanMcClean\SimpleCommerce\Facades\Product;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function can_get_all_products()
    {
        Entry::make()->collection('products')->id('1')->save();
        Entry::make()->collection('products')->id('2')->save();
        Entry::make()->collection('products')->id('3')->save();

        $products = Product::all();

        $this->assertCount(3, $products);
        $this->assertEquals(['1', '2', '3'], $products->map->id()->all());
    }

    #[Test]
    public function can_get_all_products_across_collections()
    {
        Config::set('statamic.simple-commerce.products.collections', ['merch', 'courses']);

        Collection::make('merch')->save();
        Collection::make('courses')->save();

        Entry::make()->collection('merch')->id('a')->save();
        Entry::make()->collection('merch')->id('b')->save();
        Entry::make()->collection('courses')->id('c')->save();

        $products = Product::all();

        $this->assertCount(3, $products);
        $this->assertEquals(['a', 'b', 'c'], $products->map->id()->all());
    }

    #[Test]
    public function can_find_product()
    {
        Entry::make()->collection('products')->id('abc')->save();

        $product = Product::find('abc');

        $this->assertInstanceOf(\DuncanMcClean\SimpleCommerce\Products\Product::class, $product);
    }

    #[Test]
    public function can_make_product_from_entry()
    {
        $entry = tap(Entry::make()->collection('products')->id('abc')->set('price', 2500)->template('foo')->layout('bar'))->save();

        $product = Product::fromEntry($entry);

        $this->assertInstanceOf(\DuncanMcClean\SimpleCommerce\Products\Product::class, $product);

        $this->assertEquals($entry->id(), $product->id());
        $this->assertEquals($entry->collectionHandle(), $product->collectionHandle());
        $this->assertEquals($entry->locale(), $product->locale());
        $this->assertEquals($entry->template(), $product->template());
        $this->assertEquals($entry->layout(), $product->layout());
        $this->assertEquals($entry->data(), $product->data());
    }
}
