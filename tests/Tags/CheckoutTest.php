<?php

namespace Tests\Tags;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Parse;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    public function it_outputs_the_checkout_form()
    {
        $this->makeCartWithLineItems();

        $output = $this->tag('{{ sc:checkout }}<p>You are {{ customer:name }}.</p> <button>Checkout!</button>{{ /sc:checkout }}');

        $this->assertStringContainsString('<form method="POST" action="http://localhost/!/simple-commerce/checkout">', $output);
        $this->assertStringContainsString('<p>You are John Doe.</p>', $output);
        $this->assertStringContainsString('<button>Checkout!</button>', $output);
    }

    private function tag($tag, $variables = [])
    {
        return Parse::template($tag, $variables);
    }

    protected function makeCartWithLineItems($product = null)
    {
        $cart = Cart::make()
            ->customer(['name' => 'John Doe', 'email' => 'john.doe@example.com'])
            ->lineItems([
                [
                    'id' => 'line-item-1',
                    'product' => $this->makeProduct($product)->id(),
                    'quantity' => 1,
                    'foo' => 'bar',
                    'baz' => 'qux',
                ],
            ]);

        $cart->save();

        Cart::setCurrent($cart);

        return $cart;
    }

    protected function makeProduct($id = null)
    {
        Collection::make('products')->save();

        return tap(Entry::make()->collection('products')->id($id))->save();
    }
}