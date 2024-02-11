<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Gateways\Builtin\MollieGateway;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\Invader;
use Illuminate\Http\Request;
use Statamic\Facades\Collection;

beforeEach(function () {
    $config = [
        'key' => env('MOLLIE_KEY'),
        'profile' => env('MOLLIE_PROFILE'),
    ];

    $this->gateway = new MollieGateway($config, 'mollie');

    Collection::make('products')->title('Products')->save();
    Collection::make('orders')->title('Order')->save();
});

test('has a name', function () {
    $name = $this->gateway->name();

    expect($name)->toBeString();
    expect($name)->toBe('Mollie');
});

test('can prepare', function () {
    $product = Product::make()
        ->price(5500)
        ->data(['title' => 'Concert Ticket']);

    $product->save();

    $order = Order::make()->lineItems([
        [
            'id' => app('stache')->generateId(),
            'product' => $product->id,
            'quantity' => 1,
            'total' => 5500,
            'metadata' => [],
        ],
    ])->grandTotal(5500)->merge([
        'title' => '#0001',
    ]);

    $order->save();

    $prepare = $this->gateway->prepare(
        new Request(),
        $order
    );

    expect($prepare)->toBeArray();
    expect($prepare['id'])->toContain('tr_');

    $molliePayment = (new Invader($this->gateway))->mollie->payments->get($prepare['id']);

    expect($molliePayment->amount->value)->toBe('55.00');
    expect($molliePayment->description)->toBe('Order '.$order->orderNumber());
    expect($molliePayment->redirectUrl)->toContain('/!/simple-commerce/gateways/mollie/callback?_order_id='.$order->id());
})->skip(! env('MOLLIE_KEY'));

test('can refund charge', function () {
    $this->markTestIncomplete('Need to figure out how we can fake a REAL payment, so we can then go onto refund it.');

    $order = Order::make();
    $order->save();

    $refund = $this->gateway->refund($order);

    expect($refund)->toBeArray();
})->skip(! env('MOLLIE_KEY'));

test('can hit webhook', function () {
    (new Invader($this->gateway))->setupMollie();

    $molliePayment = (new Invader($this->gateway))->mollie->payments->create([
        'amount' => [
            'currency' => 'GBP',
            'value' => '12.34',
        ],
        'description' => 'Order #12345689',
        'redirectUrl' => 'https://example.com/redirect',
        'webhookUrl' => 'https://example.com/webhook',
        'metadata' => [
            'order_id' => '12345689',
        ],
    ]);

    $payload = [
        'id' => $molliePayment->id,
    ];

    $webhook = $this->gateway->webhook(new Request([], $payload));

    expect(null)->toBe($webhook);
})->skip(! env('MOLLIE_KEY'));
