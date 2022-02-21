<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as ContractsOrder;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Gateways\Purchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\Response;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tags\CheckoutTags;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Statamic\Facades\Antlers;

class CheckoutTagTest extends TestCase
{
    protected $tag;

    public function setUp(): void
    {
        parent::setUp();

        $this->tag = resolve(CheckoutTags::class)
            ->setParser(Antlers::parser())
            ->setContext([]);

        SimpleCommerce::registerGateway(TestOnsiteGateway::class, [
            'is-duncan-cool' => 'yes',
        ]);

        SimpleCommerce::registerGateway(TestOffsiteGateway::class, [
            'is-duncan-cool' => 'no',
        ]);
    }

    /** @test */
    public function can_output_checkout_form()
    {
        $this->fakeCart();

        $this->tag->setParameters([]);

        $this->tag->setContent('
            <h2>Checkout</h2>

            {{ sc:gateways }}
                ---
                {{ name }} - Duncan Cool ({{ gateway-config:is-duncan-cool }}) - Haggis - Tatties
                ---
            {{ /sc:gateways }}
        ');

        $usage = $this->tag->index();

        $this->assertStringContainsString('Test On-site Gateway - Duncan Cool (yes) - Haggis - Tatties', $usage);
        $this->assertStringContainsString('<form method="POST" action="http://localhost/!/simple-commerce/checkout"', $usage);
    }

    /** @test */
    public function can_redirect_user_to_offsite_gateway()
    {
        $this->fakeCart();

        $this->tag->setParameters([]);

        $this->expectException(HttpResponseException::class);

        $usage = $this->tag->wildcard('testoffsitegateway');
    }

    /** @test */
    public function can_redirect_user_to_offsite_gateway_with_redirect_url()
    {
        $this->fakeCart();

        $this->tag->setParameters([
            'redirect' => 'http://localhost/thanks',
        ]);

        $this->expectException(HttpResponseException::class);

        $this->tag->wildcard('testoffsitegateway');
    }

    protected function fakeCart($cart = null)
    {
        if (is_null($cart)) {
            $cart = Order::make();
            $cart->save();
        }

        Session::shouldReceive('get')
            ->with('simple-commerce-cart')
            ->andReturn($cart->id);

        Session::shouldReceive('has')
            ->with('simple-commerce-cart')
            ->andReturn(true);

        Session::shouldReceive('token')
            ->andReturn('random-token');

        Session::shouldReceive('has')
            ->with('errors')
            ->andReturn(null);
    }
}

class TestOnsiteGateway extends BaseGateway implements Gateway
{
    public function name(): string
    {
        return 'Test On-site Gateway';
    }

    public function prepare(Prepare $data): Response
    {
        return new Response(true, [
            'haggis'  => true,
            'tatties' => true,
        ]);
    }

    public function purchase(Purchase $data): Response
    {
        return new Response(true);
    }

    public function purchaseRules(): array
    {
        return [];
    }

    public function getCharge(ContractsOrder $order): Response
    {
        return new Response(true, []);
    }

    public function refundCharge(ContractsOrder $order): Response
    {
        return new Response(true, []);
    }

    public function webhook(Request $request)
    {
        return null;
    }
}

class TestOffsiteGateway extends BaseGateway implements Gateway
{
    public function name(): string
    {
        return 'Test Off-site Gateway';
    }

    public function prepare(Prepare $data): Response
    {
        return new Response(true, [
            'bagpipes' => 'music',
        ], 'http://backpipes.com');
    }

    public function purchase(Purchase $data): Response
    {
        return new Response(true);
    }

    public function purchaseRules(): array
    {
        return [];
    }

    public function getCharge(ContractsOrder $order): Response
    {
        return new Response(true, []);
    }

    public function refundCharge(ContractsOrder $order): Response
    {
        return new Response(true, []);
    }

    public function webhook(Request $request)
    {
        return 'Success.';
    }
}
