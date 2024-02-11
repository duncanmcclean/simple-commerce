<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

uses(SetupCollections::class);

beforeEach(function () {
    $this->useBasicTaxEngine();
});

test('can get line items', function () {
    $productOne = Product::make()->price(1000);
    $productOne->save();

    $productTwo = Product::make()->price(1000);
    $productTwo->save();

    $order = Order::make()->lineItems([
        [
            'id' => 'un-doone-two-three-twa',
            'product' => $productOne->id(),
            'quantity' => 2,
        ],
        [
            'id' => 'nine-ten-eleven',
            'product' => $productTwo->id(),
            'quantity' => 2,
        ],
    ]);

    $order->save();

    $lineItems = $order->lineItems();

    expect($lineItems instanceof Collection)->toBeTrue();
    expect(2)->toBe($lineItems->count());
});

test('can get line items when item has to be filtered out to a deleted product', function () {
    $productOne = Product::make()->price(1000);
    $productOne->save();

    $order = Order::make()->lineItems([
        [
            'id' => 'un-doone-two-three-twa',
            'product' => $productOne->id(),
            'quantity' => 2,
        ],
        [
            'id' => 'nine-ten-eleven',
            'product' => 'blah-blah', // this product doesn't exist
            'quantity' => 2,
        ],
    ]);

    $order->save();

    $lineItems = $order->lineItems();

    expect($lineItems instanceof Collection)->toBeTrue();
    expect(1)->toBe($lineItems->count());
});

test('can get line items when item has null product due to a deleted product and paid order', function () {
    $productOne = Product::make()->price(1000);
    $productOne->save();

    $order = Order::make()->status(OrderStatus::Placed)->paymentStatus(PaymentStatus::Paid)->lineItems([
        [
            'id' => 'un-doone-two-three-twa',
            'product' => $productOne->id(),
            'quantity' => 2,
        ],
        [
            'id' => 'nine-ten-eleven',
            'product' => 'blah-blah', // this product doesn't exist
            'quantity' => 2,
        ],
    ]);

    $order->save();

    $lineItems = $order->lineItems();

    expect($lineItems instanceof Collection)->toBeTrue();
    expect(2)->toBe($lineItems->count());
});

test('line items return empty if order has no items', function () {
    $order = Order::make();
    $order->save();

    $lineItems = $order->lineItems();

    expect($lineItems instanceof Collection)->toBeTrue();
    expect(0)->toBe($lineItems->count());
});

test('can use the totalIncludingTax method on a LineItem when tax is included in prices', function () {
    Config::set('simple-commerce.tax_engine_config.included_in_prices', true);

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => 'ein-zwei-drei',
            'product' => $product->id(),
            'quantity' => 1,
            'total' => $product->price() - 200,
            'tax' => [
                'amount' => 200,
                'percentage' => 20,
                'included_in_price' => true,
            ],
        ],
    ]);

    $order->save();

    $lineItem = $order->lineItems()->first();

    expect($lineItem->totalIncludingTax())->toBe(1000);
});

test('can use the totalIncludingTax method on a LineItem when tax is not included in prices', function () {
    Config::set('simple-commerce.tax_engine_config.included_in_prices', false);

    $product = Product::make()->price(1000);
    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => 'ein-zwei-drei',
            'product' => $product->id(),
            'quantity' => 1,
            'total' => $product->price(),
            'tax' => [
                'amount' => 200,
                'percentage' => 20,
                'included_in_price' => false,
            ],
        ],
    ]);

    $order->save();

    $lineItem = $order->lineItems()->first();

    expect($lineItem->totalIncludingTax())->toBe(1200);
});

test('can update line item', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Four Five Six',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => 'ideeeeee-of-item',
            'product' => $product->id,
            'quantity' => 2,
        ],
    ]);

    $order->save();

    $update = $order->updateLineItem('ideeeeee-of-item', [
        'quantity' => 3,
        'metadata' => [
            'product_key' => 'gday-mate',
        ],
    ]);

    expect(1)->toBe($order->lineItems()->count());

    expect(3)->toBe($order->lineItems()->first()->quantity());
    expect($order->lineItems()->first()->metadata()->has('product_key'))->toBeTrue();
});

test('can clear line items', function () {
    $product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Four Five Six',
        ]);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => 'ideeeeee-of-item',
            'product' => $product->id,
            'quantity' => 2,
        ],
    ]);

    $order->save();

    $lineItems = $order->clearlineItems();

    expect($lineItems instanceof Collection)->toBeTrue();
    expect(0)->toBe($lineItems->count());
});
