<?php

namespace Tests\Tags;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Parse;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CartTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    public function can_get_cart()
    {
        $cart = tap(Cart::make()->grandTotal(1523)->set('foo', 'bar'))->saveWithoutRecalculating();

        Cart::setCurrent($cart);

        $this->assertEquals(
            '£15.23-bar',
            (string) $this->tag('{{ cart }}{{ grand_total }}-{{ foo }}{{ /cart }}')
        );
    }

    #[Test]
    public function can_get_data_using_wildcard()
    {
        $cart = tap(Cart::make()->grandTotal(1523)->set('foo', 'bar'))->saveWithoutRecalculating();

        Cart::setCurrent($cart);

        $this->assertEquals('£15.23', (string) $this->tag('{{ cart:grand_total }}'));
        $this->assertEquals('bar', (string) $this->tag('{{ cart:foo }}'));
    }

    #[Test]
    public function doesnt_create_an_empty_cart_when_calling_wildcard()
    {
        $this->assertEquals('£0.00', (string) $this->tag('{{ cart:grand_total }}'));
        $this->assertEmpty((string) $this->tag('{{ cart:foo }}'));
    }

    #[Test]
    public function can_check_if_cart_exists()
    {
        $this->assertEquals('no', $this->tag('{{ if {cart:exists} }}yes{{ else }}no{{ /if }}'));

        Cart::setCurrent(Cart::make());

        $this->assertEquals('yes', $this->tag('{{ if {cart:exists} }}yes{{ else }}no{{ /if }}'));
    }

    #[Test]
    public function can_check_if_product_is_already_in_cart()
    {
        $this->makeProduct('123');

        $this->assertEquals('no', $this->tag('{{ if {cart:already_exists product="123"} }}yes{{ else }}no{{ /if }}'));

        $cart = Cart::make();
        $cart->lineItems()->create(['product' => '123', 'quantity' => 1]);

        Cart::setCurrent($cart);

        $this->assertEquals('yes', $this->tag('{{ if {cart:already_exists product="123"} }}yes{{ else }}no{{ /if }}'));
    }

    #[Test]
    public function can_check_if_variant_is_already_in_cart()
    {
        $this->makeVariantProduct('123');

        $this->assertEquals('no', $this->tag('{{ if {cart:already_exists product="123" variant="Red"} }}yes{{ else }}no{{ /if }}'));

        $cart = Cart::make();
        $cart->lineItems()->create(['product' => '123', 'variant' => 'Red', 'quantity' => 1]);

        Cart::setCurrent($cart);

        $this->assertEquals('yes', $this->tag('{{ if {cart:already_exists product="123" variant="Red"} }}yes{{ else }}no{{ /if }}'));
    }

    #[Test]
    public function it_outputs_add_form()
    {
        $output = $this->tag('{{ cart:add class="add-to-cart" }}<button>Add to Cart</button>{{ /cart:add }}');

        $this->assertStringContainsString('<form method="POST" action="http://localhost/!/simple-commerce/cart/line-items" class="add-to-cart">', $output);
        $this->assertStringContainsString('<button>Add to Cart</button>', $output);
    }

    #[Test]
    public function it_outputs_update_line_item_form()
    {
        $this->makeCartWithLineItems('123');

        $output = $this->tag('{{ cart:update_line_item product="123" }}<button>Update</button>{{ /cart:update_line_item }}');

        $this->assertStringContainsString('<form method="POST" action="http://localhost/!/simple-commerce/cart/line-items/line-item-1">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_method" value="PATCH">', $output);
        $this->assertStringContainsString('<button>Update</button>', $output);
    }

    #[Test]
    public function it_outputs_remove_form()
    {
        $this->makeCartWithLineItems('123');

        $output = $this->tag('{{ cart:remove product="123" }}<button>Remove</button>{{ /cart:remove }}');

        $this->assertStringContainsString('<form method="POST" action="http://localhost/!/simple-commerce/cart/line-items/line-item-1">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_method" value="DELETE">', $output);
        $this->assertStringContainsString('<button>Remove</button>', $output);
    }

    #[Test]
    public function it_outputs_update_form()
    {
        $output = $this->tag('{{ cart:update class="cart-form" }}<button>Update</button>{{ /cart:update }}');

        $this->assertStringContainsString('<form method="POST" action="http://localhost/!/simple-commerce/cart" class="cart-form">', $output);
        $this->assertStringContainsString('<button>Update</button>', $output);
    }

    #[Test]
    public function it_outputs_empty_form()
    {
        $output = $this->tag('{{ cart:empty class="get-rid-of-everything" }}<button>Empty the cart!</button>{{ /cart:empty }}');

        $this->assertStringContainsString('<form method="POST" action="http://localhost/!/simple-commerce/cart" class="get-rid-of-everything">', $output);
        $this->assertStringContainsString('<input type="hidden" name="_method" value="DELETE">', $output);
        $this->assertStringContainsString('<button>Empty the cart!</button>', $output);
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

    protected function makeVariantProduct($id = null)
    {
        Collection::make('products')->save();

        $product = Entry::make()
            ->collection('products')
            ->id($id)
            ->set('product_variants', [
                'variants' => [['name' => 'Colour', 'values' => ['Red', 'Yellow', 'Blue']]],
                'options' => [
                    ['key' => 'Red', 'variant' => 'Red', 'price' => 1000],
                    ['key' => 'Yellow', 'variant' => 'Yellow', 'price' => 1500],
                    ['key' => 'Blue', 'variant' => 'Blue', 'price' => 1799],
                ],
            ]);

        $product->save();

        return $product;
    }
}
