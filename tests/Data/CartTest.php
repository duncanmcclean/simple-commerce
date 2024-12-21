<?php

namespace Data;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class CartTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function recalculates_totals_when_saving()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 500])->save();

        // The totals here are wrong. They'll get fixed when the totals are recalculated.
        $cart = Cart::make()
            ->grandTotal(2000)
            ->subtotal(2000)
            ->discountTotal(500)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 2, 'total' => 2000],
            ]);

        $cart->save();

        $this->assertEquals(1000, $cart->grandTotal());
        $this->assertEquals(1000, $cart->subtotal());
        $this->assertEquals(0, $cart->discountTotal());
        $this->assertEquals(1000, $cart->lineItems()->first()->total());
    }

    #[Test]
    public function does_not_recalculate_totals_when_nothing_has_changed()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 500])->save();

        $cart = Cart::make()
            ->grandTotal(1000)
            ->subtotal(1000)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 2, 'total' => 1000],
            ]);

        $cart->set('fingerprint', $fingerprint = $cart->fingerprint());

        $cart = \Mockery::mock($cart)->makePartial();
        $cart->shouldNotReceive('recalculate');

        $cart->save();

        $this->assertEquals(1000, $cart->grandTotal());
        $this->assertEquals(1000, $cart->subtotal());
        $this->assertEquals(0, $cart->discountTotal());
        $this->assertEquals(1000, $cart->lineItems()->first()->total());
        $this->assertEquals($fingerprint, $cart->get('fingerprint'));
    }

    #[Test]
    public function does_not_recalculate_totals_when_recalculating_is_disabled()
    {
        Collection::make('products')->save();
        Entry::make()->id('product-id')->collection('products')->data(['price' => 500])->save();

        // The totals here are wrong. They'll get fixed when the totals are recalculated.
        $cart = Cart::make()
            ->grandTotal(2000)
            ->subtotal(2000)
            ->discountTotal(500)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 2, 'total' => 2000],
            ]);

        $cart->saveWithoutRecalculating();

        $this->assertEquals(2000, $cart->grandTotal());
        $this->assertEquals(2000, $cart->subtotal());
        $this->assertEquals(500, $cart->discountTotal());
        $this->assertEquals(2000, $cart->lineItems()->first()->total());
    }
}
