<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\Telemetry;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\RefreshContent;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

use function PHPUnit\Framework\assertFileDoesNotExist;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNotSame;

uses(DuncanMcClean\SimpleCommerce\Tests\TestCase::class);
uses(SetupCollections::class);
uses(RefreshContent::class);
uses(PreventsSavingStacheItemsToDisk::class);

beforeEach(function () {
    $this->setupCollections();

    File::delete(storage_path('statamic/addons/simple-commerce/telemetry.json'));
});

test('does not send telemetry if disabled', function () {
    Http::fake();

    Config::set('simple-commerce.enable_telemetry', false);

    Telemetry::send();

    Http::assertNothingSent();

    assertFileDoesNotExist(storage_path('statamic/addons/simple-commerce/telemetry.json'));
});

test('does not send telemetry if telemetry was last sent within the last 30 days', function () {
    Http::fake();

    File::ensureDirectoryExists(storage_path('statamic/addons/simple-commerce'));

    File::put(storage_path('statamic/addons/simple-commerce/telemetry.json'), json_encode([
        'last_sent_at' => now()->subDays(29)->timestamp,
    ]));

    Telemetry::send();

    Http::assertNothingSent();
});

test('does send telemetry if telemetry was last sent more then 30 days ago', function () {
    Http::fake(function (Request $request) {
        return Http::response([
            'success' => true,
        ], 200, [
            'Content-Type' => 'application/json',
        ]);
    });

    File::ensureDirectoryExists(storage_path('statamic/addons/simple-commerce'));

    File::put(storage_path('statamic/addons/simple-commerce/telemetry.json'), json_encode([
        'last_sent_at' => $originalLastSentAt = now()->subDays(31)->timestamp,
    ]));

    Telemetry::send();

    $telemetryData = json_decode(File::get(storage_path('statamic/addons/simple-commerce/telemetry.json')), true);

    assertNotSame($originalLastSentAt, $telemetryData['last_sent_at']);
});

test('does send telemetry if telemetry has never been sent before', function () {
    Http::fake(function (Request $request) {
        return Http::response([
            'success' => true,
        ], 200, [
            'Content-Type' => 'application/json',
        ]);
    });

    Telemetry::send();

    $telemetryData = json_decode(File::get(storage_path('statamic/addons/simple-commerce/telemetry.json')), true);

    assertNotNull($telemetryData['last_sent_at']);
});

test('orders count and orders grand total are correct since last telemetry', function () {
    Http::fake(function (Request $request) {
        return Http::response([
            'success' => true,
        ], 200, [
            'Content-Type' => 'application/json',
        ]);
    });

    $product = Product::make()->price(1000)->data(['title' => 'Food']);
    $product->save();

    $orderOne = Order::make()
        ->grandTotal(1000)
        ->paymentStatus(PaymentStatus::Paid)
        ->data(['status_log' => ['paid' => now()->subDays(60)->format('Y-m-d H:i')]]);

    $orderTwo = Order::make()
        ->grandTotal(1523)
        ->paymentStatus(PaymentStatus::Paid)
        ->data(['status_log' => ['paid' => now()->subDays(45)->format('Y-m-d H:i')]]);

    $orderThree = Order::make()
        ->grandTotal(1523)
        ->paymentStatus(PaymentStatus::Paid)
        ->data(['status_log' => ['paid' => now()->subDays(10)->format('Y-m-d H:i')]]);

    $orderOne->save();
    $orderTwo->save();
    $orderThree->save();

    File::ensureDirectoryExists(storage_path('statamic/addons/simple-commerce'));

    File::put(storage_path('statamic/addons/simple-commerce/telemetry.json'), json_encode([
        'last_sent_at' => $originalLastSentAt = now()->subDays(30)->timestamp,
    ]));

    Telemetry::send();

    Http::assertSent(function (Request $request) {
        $body = json_decode($request->body(), true);

        return $body['data']['orders_count'] === 1
            && $body['data']['orders_grand_total'] === 1523;
    });

    $telemetryData = json_decode(File::get(storage_path('statamic/addons/simple-commerce/telemetry.json')), true);

    assertNotSame($originalLastSentAt, $telemetryData['last_sent_at']);
});

test('orders count and orders grand total are correct when telemetry has never been sent before', function () {
    Http::fake(function (Request $request) {
        return Http::response([
            'success' => true,
        ], 200, [
            'Content-Type' => 'application/json',
        ]);
    });

    $product = Product::make()->price(1000)->data(['title' => 'Food']);
    $product->save();

    $orderOne = Order::make()
        ->grandTotal(1000)
        ->paymentStatus(PaymentStatus::Paid)
        ->data(['status_log' => ['paid' => now()->subDays(60)->format('Y-m-d H:i')]]);

    $orderTwo = Order::make()
        ->grandTotal(1523)
        ->paymentStatus(PaymentStatus::Paid)
        ->data(['status_log' => ['paid' => now()->subDays(45)->format('Y-m-d H:i')]]);

    $orderThree = Order::make()
        ->grandTotal(1523)
        ->paymentStatus(PaymentStatus::Paid)
        ->data(['status_log' => ['paid' => now()->subDays(10)->format('Y-m-d H:i')]]);

    $orderOne->save();
    $orderTwo->save();
    $orderThree->save();

    Telemetry::send();

    Http::assertSent(function (Request $request) {
        $body = json_decode($request->body(), true);

        return $body['data']['orders_count'] === 3
            && $body['data']['orders_grand_total'] === 4046;
    });

    $telemetryData = json_decode(File::get(storage_path('statamic/addons/simple-commerce/telemetry.json')), true);

    assertNotNull($telemetryData['last_sent_at']);
});

test('can handle server error by telemetry endpoint', function () {
    Http::fake(function (Request $request) {
        return Http::response([
            'success' => false,
        ], 500, [
            'Content-Type' => 'application/json',
        ]);
    });

    Telemetry::send();

    assertFileDoesNotExist(storage_path('statamic/addons/simple-commerce/telemetry.json'));
});
