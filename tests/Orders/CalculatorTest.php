<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Orders;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\Calculator;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CalculatorTest extends TestCase
{
    // TODO: calculator does not run if order is paid

    /** @test */
    public function can_calculate_correct_tax_amount()
    {
        $product = Product::create([
            'price' => 1000,
        ]);

        $cart = Order::create([
            'is_paid' => false,
            'items' => [
                [
                    'product' => $product->id,
                    'quantity' => 2,
                    'total' => 2000,
                ],
            ],
        ]);

        $calculate = (new Calculator)->calculate($cart);

        $this->assertIsArray($calculate);

        $this->assertSame($calculate['grand_total'], 2333);
        $this->assertSame($calculate['items_total'], 2000);
        $this->assertSame($calculate['shipping_total'], 0);
        $this->assertSame($calculate['tax_total'], 333);
        $this->assertSame($calculate['coupon_total'], 0);

        $this->assertSame($calculate['items'][0]['total'], 2000);
    }
}
