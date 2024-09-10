<?php

namespace Tests\Feature;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Cart::forgetCurrentCart();
    }

    #[Test]
    public function cant_checkout_without_customer_information()
    {
        $this->makeCart();

        $this
            ->post('/!/simple-commerce/checkout')
            ->assertSessionHasErrors('customer');
    }

    protected function makeCart()
    {
        $cart = tap(Cart::make())->save();

        Cart::setCurrent($cart);

        return $cart;
    }
}