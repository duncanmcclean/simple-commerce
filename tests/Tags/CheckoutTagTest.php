<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Tags\CheckoutTags;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
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
    }

    /** @test */
    public function can_output_checkout_form()
    {
        // fake cart
        // set gateway on cart

        // run tag

        // ensure gateway prep data is passed into form
        // assert has token and endpoint is correct
    }

    /** @test */
    public function can_redirect_user_to_offsite_gateway()
    {
        // fake cart
        // set gateway on cart

        // ensure user is redirected via `abort()` method to gateway's checkout url
    }

    /** @test */
    public function can_redirect_user_to_offsite_gateway_with_redirect_url()
    {
        // fake cart
        // set gateway on cart

        // ensure user is redirected via `abort()` method to gateway's checkout url
    }
}
