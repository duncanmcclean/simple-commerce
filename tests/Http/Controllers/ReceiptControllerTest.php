<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\URL;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class ReceiptControllerTest extends TestCase
{
    /** @test */
    public function can_show_receipt()
    {
        $this->markTestIncomplete();

        $product = Product::make()
            ->title('Food')
            ->slug('food')
            ->data(['price' => 1000])
            ->save();

        $cart = Cart::make()->save()->update([
            'items' => [
                [
                    'id' => Stache::generateId(),
                    'product' => $product->id,
                    'quantity' => 1,
                    'total' => 1000,
                ],
            ],
        ])->calculateTotals()->markAsCompleted();

        $url = URL::temporarySignedRoute('statamic.simple-commerce.receipt.show', now()->addHour(), [
            'orderId' => $cart->id,
        ]);
        $url = preg_replace('#^.+://[^/]+#', '', $url);

        $response = $this->get($url);

        $response->assertOk();
    }
}
