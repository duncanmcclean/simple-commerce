<?php

namespace Damcclean\Commerce\Tests\Unit\Tags;

use Damcclean\Commerce\Tags\CommerceTags;
use Damcclean\Commerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class CommerceTagTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->commerceTag = new CommerceTags();
    }

    /** @test */
    public function commerce_currency_code_works()
    {
        Config::set('commerce.currency.code', 'gbp');

        $tag = $this->commerceTag->currencyCode();

        $this
            ->assertSame('gbp', $tag);
    }

    /** @test */
    public function commerce_currency_symbold_works()
    {
        Config::set('commerce.currency.symbol', '£');

        $tag = $this->commerceTag->currencySymbol();

        $this
            ->assertSame('£', $tag);
    }

    /** @test */
    public function commerce_stripe_key_works()
    {
        Config::set('commerce.stripe.key', 'pk_test_123456789');

        $tag = $this->commerceTag->stripeKey();

        $this
            ->assertSame('pk_test_123456789', $tag);
    }
}
