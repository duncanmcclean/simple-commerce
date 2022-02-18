<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\URL;
use Statamic\Facades\Stache;

class ReceiptControllerTest extends TestCase
{
    /** @test */
    public function can_show_receipt()
    {
        $product = Product::create([
            'title' => 'Food',
            'price' => 1000,
        ]);

        $cart = Order::create([
            'items' => [
                [
                    'id'       => Stache::generateId(),
                    'product'  => $product->id,
                    'quantity' => 1,
                    'total'    => 1000,
                ],
            ],
        ])->recalculate()->markAsPaid();

        $url = URL::temporarySignedRoute('statamic.simple-commerce.receipt.show', now()->addHour(), [
            'orderId' => $cart->id,
        ]);
        $url = preg_replace('#^.+://[^/]+#', '', $url);

        $response = $this->get($url);

        $response->assertOk();
    }
}
