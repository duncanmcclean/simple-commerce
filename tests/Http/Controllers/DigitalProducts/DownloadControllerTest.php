<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\RefreshContent;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Statamic\Events\EntryBlueprintFound;
use Statamic\Facades\Stache;

uses(SetupCollections::class);
uses(RefreshContent::class);

beforeEach(function () {
    $this->useBasicTaxEngine();

    Event::listen(EntryBlueprintFound::class, function ($event) {
        $event->blueprint->ensureField('product_variants', ['type' => 'product_variants']);
    });

    Storage::disk('test')->put('manual.pdf', 'Manual');

    $this->product = Product::make()
        ->data(['title' => 'E-book', 'slug' => 'e-book'])
        ->productVariants([
            'variants' => [
                ['name' => 'Format', 'values' => ['PDF']],
            ],
            'options' => [
                [
                    'key' => 'PDF',
                    'variant' => 'PDF',
                    'price' => 1000,
                    'download_limit' => 1,
                    'downloadable_asset' => ['manual.pdf'],
                ],
            ],
        ]);

    $this->product->save();
});

afterEach(function () {
    Storage::disk('test')->delete('manual.pdf');
});

function makeOrderWithVariantDownload($product, array $downloadHistory = [], PaymentStatus $paymentStatus = PaymentStatus::Paid)
{
    $order = Order::make()->paymentStatus($paymentStatus)->lineItems([
        [
            'id' => Stache::generateId(),
            'product' => $product->id(),
            'variant' => ['variant' => 'PDF', 'product' => $product->id()],
            'quantity' => 1,
            'total' => 1000,
            'metadata' => array_filter([
                'license_key' => 'secret-license-key',
                'download_history' => $downloadHistory,
            ]),
        ],
    ]);

    $order->save();

    return $order;
}

it('downloads a variant and records the download history', function () {
    $order = makeOrderWithVariantDownload($this->product);
    $lineItem = $order->lineItems()->first();

    $this
        ->get(route('statamic.simple-commerce.digital-products.download', [
            'orderId' => $order->id(),
            'lineItemId' => $lineItem->id(),
            'license_key' => 'secret-license-key',
        ]))
        ->assertOk()
        ->assertDownload('e-book.zip');

    expect($order->fresh()->lineItems()->first()->metadata()->get('download_history'))->toHaveCount(1);
});

it('refuses to download a variant once its download limit has been reached', function () {
    $order = makeOrderWithVariantDownload($this->product, [
        ['timestamp' => now()->timestamp, 'ip_address' => '127.0.0.1'],
    ]);

    $lineItem = $order->lineItems()->first();

    $this
        ->get(route('statamic.simple-commerce.digital-products.download', [
            'orderId' => $order->id(),
            'lineItemId' => $lineItem->id(),
            'license_key' => 'secret-license-key',
        ]))
        ->assertStatus(405);

    expect($order->fresh()->lineItems()->first()->metadata()->get('download_history'))->toHaveCount(1);
});

it('refuses to download a variant when the order has not been paid', function () {
    $order = makeOrderWithVariantDownload($this->product, paymentStatus: PaymentStatus::Unpaid);
    $lineItem = $order->lineItems()->first();

    $this
        ->get(route('statamic.simple-commerce.digital-products.download', [
            'orderId' => $order->id(),
            'lineItemId' => $lineItem->id(),
            'license_key' => 'secret-license-key',
        ]))
        ->assertForbidden();

    expect($order->fresh()->lineItems()->first()->metadata()->get('download_history'))->toBeNull();
});
