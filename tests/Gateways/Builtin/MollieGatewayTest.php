<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Gateways\Builtin\MollieGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Statamic\Facades\Collection;

class MollieGatewayTest extends TestCase
{
    public MollieGateway $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $config = [
            'key' => env('MOLLIE_KEY'),
            'profile' => env('MOLLIE_PROFILE'),
        ];

        $this->gateway = new MollieGateway($config, 'mollie');

        Collection::make('products')->title('Products')->save();
        Collection::make('orders')->title('Order')->save();
    }

    /** @test */
    public function has_a_name()
    {
        $name = $this->gateway->name();

        $this->assertIsString($name);
        $this->assertSame('Mollie', $name);
    }

    /** @test */
    public function can_prepare()
    {
        if (! env('MOLLIE_KEY')) {
            $this->markTestSkipped('Skipping, no Mollie key has been defined for this environment.');
        }

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

        $this->assertIsArray($prepare);
        $this->assertStringContainsString('tr_', $prepare['id']);

        $molliePayment = (new Invader($this->gateway))->mollie->payments->get($prepare['id']);

        $this->assertSame('55.00', $molliePayment->amount->value);
        $this->assertSame('Order '.$order->orderNumber(), $molliePayment->description);
        $this->assertStringContainsString('/!/simple-commerce/gateways/mollie/callback?_order_id='.$order->id(), $molliePayment->redirectUrl);
    }

    /** @test */
    public function can_refund_charge()
    {
        $this->markTestIncomplete('Need to figure out how we can fake a REAL payment, so we can then go onto refund it.');

        if (! env('MOLLIE_KEY')) {
            $this->markTestSkipped('Skipping, no Mollie key has been defined for this environment.');
        }

        $order = Order::make();
        $order->save();

        $refund = $this->gateway->refund($order);

        $this->assertIsArray($refund);
    }

    /** @test */
    public function can_hit_webhook()
    {
        if (! env('MOLLIE_KEY')) {
            $this->markTestSkipped('Skipping, no Mollie key has been defined for this environment.');
        }

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

        $this->assertSame($webhook, null);
    }
}
