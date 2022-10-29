<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Orders;

use DoubleThreeDigital\SimpleCommerce\Events\StockRunningLow;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CheckoutProductHasNoStockException;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\Checkout\HandleStock;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;

class HandleStockTest extends TestCase
{
    use SetupCollections;

    /** @test */
    public function can_decrease_stock_for_standard_product()
    {
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
            ->through([HandleStock::class])
            ->thenReturn();

        $product->fresh();

        $this->assertSame(7, $product->stock());
    }

    /** @test */
    public function cant_decrease_stock_for_standard_product_when_product_has_no_stock()
    {
        $product = Product::make()
            ->price(1200)
            ->stock(0)
            ->data([
                'title' => 'Medium Jumper',
            ]);

        $product->save();

        $order = Order::make()
            ->lineItems([
                [
                    'product' => $product->id(),
                    'quantity' => 1,
                ],
            ]);

        $order->save();

        $this->expectException(CheckoutProductHasNoStockException::class);

        app(Pipeline::class)
            ->send($order)
            ->through([HandleStock::class])
            ->thenReturn();

        $product->fresh();

        $this->assertSame(0, $product->stock());
    }

    /** @test */
    public function cant_decrease_stock_for_standard_product_when_quantity_is_greater_than_stock()
    {
        $this->markTestIncomplete("TODO");
    }

    /** @test */
    public function ensure_low_stock_event_is_fired_when_product_stock_is_below_threshold()
    {
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
            ->through([HandleStock::class])
            ->thenReturn();

        $product->fresh();

        $this->assertSame(7, $product->stock());

        Event::assertDispatched(StockRunningLow::class);
    }

    /** @test */
    public function can_decrease_stock_for_standard_product_with_non_localised_stock_field()
    {
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
            ->through([HandleStock::class])
            ->thenReturn();

        $englishProduct->fresh();
        $frenchProduct->fresh();

        $this->assertSame(7, $product->stock());
    }

    /** @test */
    public function can_decrease_stock_for_variant_product()
    {
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
            ->through([HandleStock::class])
            ->thenReturn();

        $product->fresh();

        $this->assertNull($product->stock());
        $this->assertSame(7, $product->variant('Yellow_Large')->stock());
    }

    /** @test */
    public function cant_decrease_stock_for_variant_product_when_product_has_no_stock()
    {
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
                        'stock' => 0,
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

        $this->expectException(CheckoutProductHasNoStockException::class);

        app(Pipeline::class)
            ->send($order)
            ->through([HandleStock::class])
            ->thenReturn();

        $product->fresh();

        $this->assertNull($product->stock());
        $this->assertSame(0, $product->variant('Yellow_Large')->stock());
    }

    /** @test */
    public function cant_decrease_stock_for_variant_product_when_quantity_is_greater_than_stock()
    {
        $this->markTestIncomplete("TODO");
    }

    /** @test */
    public function can_decrease_stock_for_variant_product_with_non_localised_stock_field()
    {
        $this->markTestIncomplete("TODO");
    }

    /** @test */
    public function ensure_low_stock_event_is_fired_when_variant_stock_is_below_threshold()
    {
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
            ->through([HandleStock::class])
            ->thenReturn();

        $product->fresh();

        $this->assertNull($product->stock());
        $this->assertSame(7, $product->variant('Yellow_Large')->stock());

        Event::assertDispatched(StockRunningLow::class);
    }
}
