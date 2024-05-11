<?php

use DuncanMcClean\SimpleCommerce\Contracts\Product as ProductContract;
use DuncanMcClean\SimpleCommerce\Exceptions\ProductNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Products\EntryQueryBuilder;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

uses(PreventsSavingStacheItemsToDisk::class);

it('can get all products', function () {
    Product::make()->id('one')->price(1500)->save();
    Product::make()->id('two')->price(2520)->save();
    Product::make()->id('three')->price(3000)->save();

    $products = Product::all();

    expect($products->count())->toBe(3);
    expect($products->map->id()->toArray())->toBe(['one', 'two', 'three']);
});

it('can query products', function () {
    Product::make()->id('one')->price(1500)->save();
    Product::make()->id('two')->price(2520)->save();
    Product::make()->id('three')->price(3000)->save();

    $query = Product::query();
    expect($query)->toBeInstanceOf(EntryQueryBuilder::class);
    expect($query->count(), 3);

    $query = Product::query()->where('price', '>=', 2000);
    expect($query->count())->toBe(2);
    expect($query->get()[0])->toBeInstanceOf(ProductContract::class);
});

it('can find product', function () {
    Product::make()->id('one')->price(1500)->save();

    $product = Product::find('one');

    expect($product)->toBeInstanceOf(ProductContract::class);
});

it('can findOrFail product', function () {
    Product::make()->id('one')->price(1500)->save();

    $product = Product::findOrFail('one');

    expect($product)->toBeInstanceOf(ProductContract::class);

    expect(fn () => Product::findOrFail(123))->toThrow(ProductNotFound::class);
});

it('can make product', function () {
    $product = Product::make();

    expect($product)->toBeInstanceOf(ProductContract::class);
});

it('can save product', function () {
    $product = Product::make()->id('one')->price(1500);

    expect($product->resource())->toBeNull();

    $product->save();

    expect($product->resource())->toBeInstanceOf(\Statamic\Contracts\Entries\Entry::class);
});

it('can delete product', function () {
    $product = Product::make()->id('one')->price(1500)->save();

    expect($product->resource())->toBeInstanceOf(\Statamic\Contracts\Entries\Entry::class);

    $product->delete();

    expect($product->resource()->fresh())->toBeNull();
});
