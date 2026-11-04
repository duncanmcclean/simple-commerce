<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Gateways\Builtin\PayPalGateway;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\RefreshContent;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Http\Request;
use PayPalHttp\HttpResponse;

uses(SetupCollections::class);
uses(RefreshContent::class);

beforeEach(function () {
    $this->setupCollections();
});

test('can hit webhook with approved order', function () {
    $order = Order::make()->grandTotal(1234);
    $order->save();

    $gateway = fakePayPalGateway(paypalCapture($order->id(), '12.34'));

    $gateway->webhook(paypalWebhookRequest('PAYPAL_ORDER_123', $order->id()));

    expect($gateway->capturedOrderIds)->toBe(['PAYPAL_ORDER_123']);
    expect(PaymentStatus::Paid)->toBe($order->fresh()->paymentStatus());
});

test('can hit webhook with approved order and ignores the order id in the posted payload', function () {
    $cheapOrder = Order::make()->grandTotal(1);
    $cheapOrder->save();

    $expensiveOrder = Order::make()->grandTotal(50000);
    $expensiveOrder->save();

    $gateway = fakePayPalGateway(paypalCapture($cheapOrder->id(), '0.01'));

    $gateway->webhook(paypalWebhookRequest('PAYPAL_ORDER_123', $expensiveOrder->id()));

    expect(PaymentStatus::Unpaid)->toBe($expensiveOrder->fresh()->paymentStatus());
    expect(PaymentStatus::Paid)->toBe($cheapOrder->fresh()->paymentStatus());
});

test('cant hit webhook with approved order when capture does not match order', function (string $status, string $amount, string $currency) {
    $order = Order::make()->grandTotal(50000);
    $order->save();

    $gateway = fakePayPalGateway(paypalCapture($order->id(), $amount, $currency, $status));

    $gateway->webhook(paypalWebhookRequest('PAYPAL_ORDER_123', $order->id()));

    expect(PaymentStatus::Unpaid)->toBe($order->fresh()->paymentStatus());
})->with([
    'capture not completed' => ['PENDING', '500.00', 'GBP'],
    'amount less than order total' => ['COMPLETED', '0.01', 'GBP'],
    'different currency' => ['COMPLETED', '500.00', 'USD'],
]);

function paypalWebhookRequest(string $paypalOrderId, string $customId): Request
{
    return new Request([], [], [], [], [], [], json_encode([
        'event_type' => 'CHECKOUT.ORDER.APPROVED',
        'resource' => [
            'id' => $paypalOrderId,
            'purchase_units' => [['custom_id' => $customId]],
        ],
    ]));
}

function paypalCapture(string $orderId, string $amount, string $currency = 'GBP', string $status = 'COMPLETED'): array
{
    return [
        'id' => 'PAYPAL_ORDER_123',
        'status' => $status,
        'payer' => [
            'name' => ['given_name' => 'Duncan', 'surname' => 'McClean'],
            'email_address' => 'duncan@example.com',
        ],
        'purchase_units' => [
            [
                'custom_id' => $orderId,
                'payments' => [
                    'captures' => [
                        [
                            'id' => 'CAPTURE_123',
                            'status' => 'COMPLETED',
                            'amount' => ['value' => $amount, 'currency_code' => $currency],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

function fakePayPalGateway(array $capture): PayPalGateway
{
    return new class($capture) extends PayPalGateway
    {
        public array $capturedOrderIds = [];

        public function __construct(private array $capture)
        {
            parent::__construct([], 'paypal');
        }

        protected function setupPayPal()
        {
            $this->paypalClient = new class($this->capture, $this->capturedOrderIds)
            {
                public function __construct(private array $capture, private array &$capturedOrderIds) {}

                public function execute($request)
                {
                    $this->capturedOrderIds[] = explode('/', $request->path)[4];

                    return new HttpResponse(201, json_decode(json_encode($this->capture)), []);
                }
            };
        }
    };
}
