<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\RefreshContent;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Facades\Storage;

uses(SetupCollections::class);
uses(RefreshContent::class);

beforeEach(function () {
    Storage::disk('test')->put('ebook.pdf', 'Once upon a time...');

    $this->product = Product::make()
        ->price(1000)
        ->data([
            'title' => 'Ebook',
            'slug' => 'ebook',
            'product_type' => 'digital',
            'downloadable_asset' => ['ebook.pdf'],
        ]);

    $this->product->save();
});

test('can download digital product', function () {
    $order = Order::make()
        ->paymentStatus(PaymentStatus::Paid)
        ->lineItems([
            [
                'id' => 'line-item-1',
                'product' => $this->product->id(),
                'quantity' => 1,
                'total' => 1000,
                'metadata' => ['license_key' => 'abc123'],
            ],
        ]);

    $order->save();

    $this
        ->get(route('statamic.simple-commerce.digital-products.download', [
            'orderId' => $order->id(),
            'lineItemId' => 'line-item-1',
            'license_key' => 'abc123',
        ]))
        ->assertOk()
        ->assertDownload('ebook.zip');
});

test('cant download digital product when order is not paid', function () {
    $order = Order::make()
        ->paymentStatus(PaymentStatus::Unpaid)
        ->lineItems([
            [
                'id' => 'line-item-1',
                'product' => $this->product->id(),
                'quantity' => 1,
                'total' => 1000,
                'metadata' => ['license_key' => 'abc123'],
            ],
        ]);

    $order->save();

    $this
        ->get(route('statamic.simple-commerce.digital-products.download', [
            'orderId' => $order->id(),
            'lineItemId' => 'line-item-1',
            'license_key' => 'abc123',
        ]))
        ->assertForbidden();
});
