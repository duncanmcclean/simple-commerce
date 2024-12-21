<?php

namespace Tests\Feature\Cart;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Jobs\PurgeAbandonedCarts;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class PurgeAbandonedCartsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_purges_abandoned_carts()
    {
        $this->travelTo(now()->subDays(95), function () {
            Cart::make()->id('123')->set('updated_at', now()->subDays(95)->timestamp)->save();
        });

        PurgeAbandonedCarts::dispatch();

        $this->assertNull(Cart::find('123'));
    }

    #[Test]
    public function it_doesnt_purge_active_cart()
    {
        $this->travelTo(now()->subDays(5), function () {
            Cart::make()->id('123')->set('updated_at', now()->subDays(95)->timestamp)->save();
        });

        PurgeAbandonedCarts::dispatch();

        $this->assertNotNull(Cart::find('123'));
    }
}
