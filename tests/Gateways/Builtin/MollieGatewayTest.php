<?php

use DuncanMcClean\SimpleCommerce\Events\OrderPaymentFailed;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Gateways\Builtin\MollieGateway;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\Invader;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Mollie\Api\MollieApiClient;
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
        new Request,
        $order
    );

    expect($prepare)->toBeArray();
    expect($prepare['id'])->toContain('tr_');

    $molliePayment = (new Invader($this->gateway))->mollie->payments->get($prepare['id']);

    expect($molliePayment->amount->value)->toBe('55.00');
    expect($molliePayment->description)->toBe('Order '.$order->orderNumber());
    expect($molliePayment->redirectUrl)->toContain('/!/simple-commerce/gateways/mollie/callback?_order_id='.$order->id());
})->skip('Mollie test account is not activated.');

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
})->skip('Mollie test account is not activated.');

test('can hit webhook with paid payment', function () {
    $order = Order::make()->grandTotal(1234)->merge([
        'mollie' => ['id' => $molliePaymentId = 'tr_'.Str::random()],
    ]);

    $order->save();

    $gateway = fakeMollieGateway(['id' => $molliePaymentId, 'amount' => ['value' => '12.34', 'currency' => 'GBP']]);

    $gateway->webhook(new Request([], ['id' => $molliePaymentId]));

    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
});

test('can hit webhook with paid payment after a newer payment was prepared for the order', function () {
    $order = Order::make()->grandTotal(1234)->merge([
        'mollie' => ['id' => 'tr_'.Str::random()],
    ]);

    $order->save();

    $gateway = fakeMollieGateway([
        'id' => $molliePaymentId = 'tr_'.Str::random(),
        'amount' => ['value' => '12.34', 'currency' => 'GBP'],
        'metadata' => ['order_id' => $order->id()],
    ]);

    $gateway->webhook(new Request([], ['id' => $molliePaymentId]));

    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
});

test('cant hit webhook with failed or expired payment once the order has been paid', function (string $status) {
    Event::fake([OrderPaymentFailed::class]);

    $order = Order::make()->grandTotal(1234)->paymentStatus(PaymentStatus::Paid)->merge([
        'mollie' => ['id' => $molliePaymentId = 'tr_'.Str::random()],
    ]);

    $order->gatewayData(gateway: 'mollie', data: ['id' => $paidMolliePaymentId = 'tr_'.Str::random()]);
    $order->save();

    $gateway = fakeMollieGateway([
        'id' => $molliePaymentId,
        'status' => $status,
        'amount' => ['value' => '12.34', 'currency' => 'GBP'],
        'metadata' => ['order_id' => $order->id()],
    ]);

    $gateway->webhook(new Request([], ['id' => $molliePaymentId]));

    expect($order->fresh()->gatewayData()->data()->get('id'))->toBe($paidMolliePaymentId);
    Event::assertNotDispatched(OrderPaymentFailed::class);
})->with(['failed', 'expired']);

test('cant hit webhook with paid payment when amount paid does not match order', function (array $amount) {
    $order = Order::make()->grandTotal(50000)->merge([
        'mollie' => ['id' => $molliePaymentId = 'tr_'.Str::random()],
    ]);

    $order->save();

    $gateway = fakeMollieGateway(['id' => $molliePaymentId, 'amount' => $amount]);

    $gateway->webhook(new Request([], ['id' => $molliePaymentId]));

    expect(PaymentStatus::Unpaid)->toBe($order->fresh()->paymentStatus());
})->with([
    'amount less than order total' => [['value' => '12.34', 'currency' => 'GBP']],
    'different currency' => [['value' => '500.00', 'currency' => 'EUR']],
]);

function fakeMollieGateway(array $payment): MollieGateway
{
    $payment = array_merge([
        'resource' => 'payment',
        'status' => 'paid',
    ], $payment);

    return new class($payment) extends MollieGateway
    {
        public function __construct(private array $payment)
        {
            parent::__construct([], 'mollie');
        }

        protected function setupMollie()
        {
            $this->mollie = new MollieApiClient(new GuzzleClient([
                'handler' => HandlerStack::create(new MockHandler([
                    new GuzzleResponse(200, [], json_encode($this->payment)),
                ])),
            ]));

            $this->mollie->setApiKey('test_dHar4XY7LxsDOtmnkVtjNVWXLSlXsM');
        }
    };
}
