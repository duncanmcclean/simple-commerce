<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tax;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Tax\BasicTaxEngine;
use DoubleThreeDigital\SimpleCommerce\Tax\TaxCalculation;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Collection;

class BasicTaxEngineTest extends TestCase
{
    /**
     * @test
     * Inline with the fix suggested here: https://github.com/doublethreedigital/simple-commerce/pull/438#issuecomment-888498198
     */
    public function can_calculate_tax_when_not_included_in_price()
    {
        Config::set('simple-commerce.tax_engine_config.rate', 20);
        Config::set('simple-commerce.tax_engine_config.included_in_prices', false);

        $product = Product::make()->price(1000);
        $product->save();

        $order = Order::make()->isPaid(false)->lineItems([
            $lineItem = [
                'product'  => $product->id,
                'quantity' => 1,
                'total'    => 1000,
            ],
        ]);

        $order->save();

        $taxCalculation = (new BasicTaxEngine)->calculate($order, $lineItem);

        $this->assertTrue($taxCalculation instanceof TaxCalculation);

        $this->assertSame($taxCalculation->amount(), 200);
        $this->assertSame($taxCalculation->priceIncludesTax(), false);
        $this->assertSame($taxCalculation->rate(), 20);
    }

    /** @test */
    public function can_calculate_tax_when_included_in_price()
    {
        Config::set('simple-commerce.tax_engine_config.rate', 20);
        Config::set('simple-commerce.tax_engine_config.included_in_prices', true);

        $product = Product::make()->price(1000);
        $product->save();

        $order = Order::make()->isPaid(false)->lineItems([
            $lineItem = [
                'product'  => $product->id,
                'quantity' => 2,
                'total'    => 2000,
            ],
        ]);

        $order->save();

        $taxCalculation = (new BasicTaxEngine)->calculate($order, $lineItem);

        $this->assertTrue($taxCalculation instanceof TaxCalculation);

        $this->assertSame($taxCalculation->amount(), 333);
        $this->assertSame($taxCalculation->priceIncludesTax(), true);
        $this->assertSame($taxCalculation->rate(), 20);
    }

    /** @test */
    public function can_calculate_tax_when_tax_rate_is_decimal_number()
    {
        Config::set('simple-commerce.tax_engine_config.rate', 10.5);

        Collection::make('products')->save();
        Collection::make('orders')->save();

        $product = Product::make()->price(1000);
        $product->save();

        $order = Order::make()->isPaid(false)->lineItems([
            $lineItem = [
                'product'  => $product->id,
                'quantity' => 2,
                'total'    => 2000,
            ],
        ]);

        $order->save();

        $taxCalculation = (new BasicTaxEngine)->calculate($order, $lineItem);

        $this->assertTrue($taxCalculation instanceof TaxCalculation);

        $this->assertSame($taxCalculation->amount(), 210);
        $this->assertSame($taxCalculation->priceIncludesTax(), false);
        $this->assertSame($taxCalculation->rate(), 10.5);
    }

    /** @test */
    public function can_calculate_tax_when_it_is_nothing()
    {
        Config::set('simple-commerce.tax_engine_config.rate', 0);

        $product = Product::make()->price(1000);
        $product->save();

        $order = Order::make()->isPaid(false)->lineItems([
            $lineItem = [
                'product'  => $product->id,
                'quantity' => 2,
                'total'    => 2000,
            ],
        ]);

        $order->save();

        $taxCalculation = (new BasicTaxEngine)->calculate($order, $lineItem);

        $this->assertTrue($taxCalculation instanceof TaxCalculation);

        $this->assertSame($taxCalculation->amount(), 0);
        $this->assertSame($taxCalculation->priceIncludesTax(), false);
        $this->assertSame($taxCalculation->rate(), 0);
    }

    /**
     * @test
     * Covers #430 (https://github.com/doublethreedigital/simple-commerce/pull/430)
     */
    public function ensure_round_value_tax_is_calculated_correctly()
    {
        Config::set('simple-commerce.tax_engine_config.rate', 20);
        Config::set('simple-commerce.tax_engine_config.included_in_prices', true);

        $product = Product::make()->price(2600);
        $product->save();

        $order = Order::make()->isPaid(false)->lineItems([
            $lineItem = [
                'product'  => $product->id,
                'quantity' => 3,
                'total'    => 7800,
            ],
        ]);

        $order->save();

        $taxCalculation = (new BasicTaxEngine)->calculate($order, $lineItem);

        $this->assertTrue($taxCalculation instanceof TaxCalculation);

        $this->assertSame($taxCalculation->amount(), 1300);
        $this->assertSame($taxCalculation->priceIncludesTax(), true);
        $this->assertSame($taxCalculation->rate(), 20);
    }
}
