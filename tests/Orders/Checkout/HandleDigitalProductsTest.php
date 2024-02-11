<?php

use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Notifications\DigitalDownloadsNotification;
use DuncanMcClean\SimpleCommerce\Orders\Checkout\HandleDigitalProducts;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Statamic\Facades\Stache;

uses(SetupCollections::class);

beforeEach(function () {
    $this->useBasicTaxEngine();
});

it('can handle digital products in order', function () {
    Notification::fake();

    Config::set('simple-commerce.notifications', [
        'digital_download_ready' => [
            DigitalDownloadsNotification::class => [
                'to' => 'customer',
            ],
        ],
    ]);

    $product = Product::make()
        ->price(1200)
        ->merge([
            'product_type' => 'digital',
        ]);

    $product->save();

    $customer = Customer::make()
        ->email('duncan@example.com')
        ->merge([
            'name' => 'Duncan',
        ]);

    $customer->save();

    $order = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id(),
                'quantity' => 1,
                'total' => 1200,
            ],
        ])
        ->customer($customer->id());

    $order->save();

    app(Pipeline::class)
        ->send($order)
        ->through([HandleDigitalProducts::class])
        ->thenReturn();

    $order->fresh();

    // Asset metadata is saved
    $lineItem = $order->lineItems()->first();

    $this->assertTrue($lineItem->metadata()->has('license_key'));
    $this->assertTrue($lineItem->metadata()->has('download_url'));
    $this->assertTrue($lineItem->metadata()->has('download_history'));

    // Assert notification has been sent
    Notification::assertSentTo(
        new AnonymousNotifiable,
        DigitalDownloadsNotification::class,
    );
});

it('can handle digital products in order with product variants', function () {
    Notification::fake();

    Config::set('simple-commerce.notifications', [
        'digital_download_ready' => [
            DigitalDownloadsNotification::class => [
                'to' => 'customer',
            ],
        ],
    ]);

    $product = Product::make()
        ->price(1200)
        ->productVariants([
            'variants' => [
                ['name' => 'Colours', 'values' => ['Red']],
                ['name' => 'Sizes', 'values' => ['Small']],
            ],
            'options' => [
                ['key' => 'Red_Small', 'variant' => 'Red Small', 'price' => 1200, 'product_type' => 'digital'],
            ],
        ])
        ->set('product_type', 'digital');

    $product->save();

    $customer = Customer::make()
        ->email('duncan@example.com')
        ->merge([
            'name' => 'Duncan',
        ]);

    $customer->save();

    $order = Order::make()
        ->lineItems([
            [
                'id' => Stache::generateId(),
                'product' => $product->id(),
                'quantity' => 1,
                'variant' => [
                    'variant' => 'Red_Small',
                    'product' => $product->id(),
                ],
                'total' => 1200,
            ],
        ])
        ->customer($customer->id());

    $order->save();

    app(Pipeline::class)
        ->send($order)
        ->through([HandleDigitalProducts::class])
        ->thenReturn();

    $order->fresh();

    // Asset metadata is saved
    $lineItem = $order->lineItems()->first();

    $this->assertTrue($lineItem->metadata()->has('license_key'));
    $this->assertTrue($lineItem->metadata()->has('download_url'));
    $this->assertTrue($lineItem->metadata()->has('download_history'));

    // Assert notification has been sent
    Notification::assertSentTo(
        new AnonymousNotifiable,
        DigitalDownloadsNotification::class,
    );
});
