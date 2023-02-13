<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Tags\CheckoutTags;
use DoubleThreeDigital\SimpleCommerce\Tags\GatewayTags;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Statamic\Facades\Antlers;
use Statamic\Statamic;

class CheckoutTagTest extends TestCase
{
    protected $tag;

    protected $gatewaysTag;

    public function setUp(): void
    {
        parent::setUp();

        $this->tag = resolve(CheckoutTags::class)
            ->setParser(Antlers::parser())
            ->setContext([]);

        $this->gatewaysTag = resolve(GatewayTags::class)
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
    public function can_fetch_checkout_form_data()
    {
        $form = Statamic::tag('sc:checkout')->fetch();

        $this->assertStringContainsString('<input type="hidden" name="_token"', $form['params_html']);
        $this->assertEquals($form['attrs_html'], 'method="POST" action="http://localhost/!/simple-commerce/checkout"');

        $this->assertArrayHasKey('_token', $form['params']);
        $this->assertEquals($form['attrs']['action'], 'http://localhost/!/simple-commerce/checkout');
        $this->assertEquals($form['attrs']['method'], 'POST');
    }

    /** @test */
    public function gateways_tag_can_get_specific_gateway()
    {
        $this->fakeCart();

        $this->gatewaysTag->setParameters([]);

        $usage = $this->gatewaysTag->wildcard('testonsitegateway');

        $this->assertStringContainsString('Test On-site Gateway', $usage['name']);
        $this->assertStringContainsString('testonsitegateway', $usage['handle']);
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

    public function isOffsiteGateway(): bool
    {
        return false;
    }

    public function prepare(Request $request, OrderContract $order): array
    {
        return [
            'haggis'  => true,
            'tatties' => true,
        ];
    }

    public function checkout(Request $request, OrderContract $order): array
    {
        return [];
    }

    public function refund(OrderContract $order): array
    {
        return [];
    }
}

class TestOffsiteGateway extends BaseGateway implements Gateway
{
    public function name(): string
    {
        return 'Test Off-site Gateway';
    }

    public function isOffsiteGateway(): bool
    {
        return true;
    }

    public function prepare(Request $request, OrderContract $order): array
    {
        return [
            'bagpipes' => 'music',
            'checkout_url' => 'http://backpipes.com',
        ];
    }

    public function checkout(Request $request, OrderContract $order): array
    {
        return [];
    }

    public function refund(OrderContract $order): array
    {
        return [];
    }

    public function webhook(Request $request)
    {
        return 'Success.';
    }
}
