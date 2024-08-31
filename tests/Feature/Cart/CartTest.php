<?php

namespace Tests\Feature\Cart;

use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CartTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    public function it_returns_the_current_cart()
    {
        $cart = $this->makeCart();

        $this
            ->get('/!/simple-commerce/cart')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'customer',
                    'line_items' => [
                        [
                            'id',
                            'product' => ['id'],
                            'quantity',
                            'total',
                        ]
                    ],
                ],
            ])
            ->assertJsonPath('data.id', $cart->id());
    }

    #[Test]
    public function it_throws_a_not_found_exception_when_no_current_cart_is_set()
    {
        $this
            ->get('/!/simple-commerce/cart')
            ->assertNotFound();
    }

    #[Test]
    public function it_updates_the_cart()
    {
        $cart = $this->makeCart();

        $this
            ->from('/cart')
            ->patch('/!/simple-commerce/cart', [
                'customer' => [
                    'name' => 'Jane Doe',
                    'email' => 'jane.doe@example.com',
                ],
                'coupon' => 'ABC123',
                'shipping_line_1' => '123 Shipping St',
                'shipping_line_2' => 'Apt 1',
                'shipping_city' => 'Shippingville',
                'shipping_postcode' => '12345',
                'shipping_country' => 'US',

                // This field shouldn't get updated.
                'grand_total' => 1000,
            ])
            ->assertRedirect('/cart');

        $cart = $cart->fresh();

        $this->assertInstanceOf(GuestCustomer::class, $cart->customer());
        $this->assertEquals('Jane Doe', $cart->customer()->name());
        $this->assertEquals('jane.doe@example.com', $cart->customer()->email());

        // TODO: Assert the coupon was added to the cart.

        $this->assertEquals('123 Shipping St', $cart->get('shipping_line_1'));
        $this->assertEquals('Apt 1', $cart->get('shipping_line_2'));
        $this->assertEquals('Shippingville', $cart->get('shipping_city'));
        $this->assertEquals('12345', $cart->get('shipping_postcode'));
        $this->assertEquals('US', $cart->get('shipping_country'));

        $this->assertEquals(0, $cart->grandTotal());
    }

    #[Test]
    public function it_updates_the_cart_and_expects_a_json_response()
    {
        $cart = $this->makeCart();

        $this
            ->patchJson('/!/simple-commerce/cart')
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'customer', 'line_items']])
            ->assertJsonPath('data.id', $cart->id());
    }

    #[Test]
    public function it_deletes_the_cart()
    {
        $cart = $this->makeCart();

        $this
            ->from('/cart')
            ->delete('/!/simple-commerce/cart')
            ->assertRedirect('/cart');

        $this->assertNull(Cart::find($cart->id()));
    }

    #[Test]
    public function it_deletes_the_cart_and_expects_a_json_response()
    {
        $cart = $this->makeCart();

        $this
            ->deleteJson('/!/simple-commerce/cart')
            ->assertOk()
            ->assertJson([]);
    }

    protected function makeCart()
    {
        Collection::make('products')->save();
        Entry::make()->collection('products')->id('product-1')->data(['title' => 'Product 1'])->save();

        $cart = Cart::make()
            ->customer(['name' => 'John Doe', 'email' => 'john.doe@example.com'])
            ->lineItems([
                [
                    'product' => 'product-1',
                    'quantity' => 1,
                ],
            ]);

        $cart->save();

        Cart::setCurrent($cart);

        return $cart;
    }
}