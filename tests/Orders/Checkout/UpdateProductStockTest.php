<?php

use DuncanMcClean\SimpleCommerce\Events\StockRunningLow;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\Checkout\UpdateProductStock;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;

uses(SetupCollections::class);

test('can decrease stock for standard product', function () {
    $product = Product::make()
        ->price(1200)
        ->stock(10)
        ->data([
            'title' => 'Medium Jumper',
        ]);

    $product->save();

    $order = Order::make()
        ->lineItems([
            [
                'product' => $product->id(),
                'quantity' => 3,
            ],
        ]);

    $order->save();

    app(Pipeline::class)
        ->send($order)
        ->through([UpdateProductStock::class])
        ->thenReturn();

    $product->fresh();

    expect($product->stock())->toBe(7);
});

test('ensure low stock event is fired when product stock is below threshold', function () {
    Event::fake();

    Config::set('simple-commerce.low_stock_threshold', 8);

    $product = Product::make()
        ->price(1200)
        ->stock(10)
        ->data([
            'title' => 'Medium Jumper',
        ]);

    $product->save();

    $order = Order::make()
        ->lineItems([
            [
                'product' => $product->id(),
                'quantity' => 3,
            ],
        ]);

    $order->save();

    app(Pipeline::class)
        ->send($order)
        ->through([UpdateProductStock::class])
        ->thenReturn();

    $product->fresh();

    expect($product->stock())->toBe(7);

    Event::assertDispatched(StockRunningLow::class);
});

test('can decrease stock for standard product with non localised stock field', function () {
    $this->markTestIncomplete("Getting an error due to how we're setting up multi-site. Need to dig into it at some point.");

    File::deleteDirectory(base_path('content/collections/products'));

    File::makeDirectory(base_path('content/collections/products'));
    File::makeDirectory(base_path('content/collections/products/english'));
    File::makeDirectory(base_path('content/collections/products/french'));

    Site::setConfig(['sites' => [
        'english' => ['url' => 'http://localhost/', 'locale' => 'en'],
        'french' => ['url' => 'http://anotherhost.com/', 'locale' => 'fr'],
    ], 'default' => 'english']);

    Site::setCurrent('english');

    Collection::findByHandle('products')->sites(['english', 'french'])->routes('{slug}')->save();

    // $englishProduct = Product::make()
    //     ->price(1200)
    //     ->stock(10)
    //     ->data([
    //         'title' => 'Medium Jumper',
    //     ]);

    // $englishProduct->save();

    $englishProduct = Entry::make()
        ->locale('english')
        ->data([
            'title' => 'Medium Jumper',
            'price' => 1200,
            'stock' => 10,
        ]);

    $englishProduct->save(); //  Undefined array key 1:: vendor/statamic/cms/src/Stache/Stores/CollectionEntriesStore.php:38

    dd('here');

    $frenchProduct = $englishProduct->resource()->makeLocalization('french');
    $frenchProduct->save();

    $frenchProduct = Product::find($frenchProduct->id());

    dd($frenchProduct);

    $order = Order::make()
        ->lineItems([
            [
                'product' => $frenchProduct->id(),
                'quantity' => 3,
            ],
        ]);

    $order->save();

    app(Pipeline::class)
        ->send($order)
        ->through([UpdateProductStock::class])
        ->thenReturn();

    $englishProduct->fresh();
    $frenchProduct->fresh();

    expect($product->stock())->toBe(7);
});

test('can decrease stock for variant product', function () {
    $product = Product::make()
        ->productVariants([
            'variants' => [
                [
                    'name' => 'Colour',
                    'values' => ['Yellow'],
                ],
                [
                    'name' => 'Size',
                    'values' => ['Large'],
                ],
            ],
            'options' => [
                [
                    'key' => 'Yellow_Large',
                    'variant' => 'Yellow, Large',
                    'price' => 1500,
                    'stock' => 10,
                ],
            ],
        ]);

    $product->save();

    $order = Order::make()
        ->lineItems([
            [
                'product' => $product->id(),
                'variant' => 'Yellow_Large',
                'quantity' => 3,
            ],
        ]);

    $order->save();

    app(Pipeline::class)
        ->send($order)
        ->through([UpdateProductStock::class])
        ->thenReturn();

    $product->fresh();

    expect($product->stock())->toBeNull();
    expect($product->variant('Yellow_Large')->stock())->toBe(7);
});

test('can decrease stock for variant product with non localised stock field', function () {
    $this->markTestIncomplete('TODO');
});

test('ensure low stock event is fired when variant stock is below threshold', function () {
    Event::fake();

    Config::set('simple-commerce.low_stock_threshold', 8);

    $product = Product::make()
        ->productVariants([
            'variants' => [
                [
                    'name' => 'Colour',
                    'values' => ['Yellow'],
                ],
                [
                    'name' => 'Size',
                    'values' => ['Large'],
                ],
            ],
            'options' => [
                [
                    'key' => 'Yellow_Large',
                    'variant' => 'Yellow, Large',
                    'price' => 1500,
                    'stock' => 10,
                ],
            ],
        ]);

    $product->save();

    $order = Order::make()
        ->lineItems([
            [
                'product' => $product->id(),
                'variant' => 'Yellow_Large',
                'quantity' => 3,
            ],
        ]);

    $order->save();

    app(Pipeline::class)
        ->send($order)
        ->through([UpdateProductStock::class])
        ->thenReturn();

    $product->fresh();

    expect($product->stock())->toBeNull();
    expect($product->variant('Yellow_Large')->stock())->toBe(7);

    Event::assertDispatched(StockRunningLow::class);
});
