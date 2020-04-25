<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Support;

use DoubleThreeDigital\SimpleCommerce\Models\Currency as CurrencyModel;
use DoubleThreeDigital\SimpleCommerce\Support\Currency;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Config;

class CurrencyTest extends TestCase
{
    public $currencies;
    public $currency;

    public function setUp(): void
    {
        parent::setUp();

        $this->currencies = factory(CurrencyModel::class, 5)->create();
        Config::set('simple-commerce.currency.iso', $this->currencies[2]->iso);

        $this->currency = new Currency();
    }

    /** @test */
    public function can_get_primary_currency()
    {
        $currency = $this->currency->primary();

        $this->assertIsObject($currency);
        $this->assertSame($currency->iso, $this->currencies[2]->iso);
    }

    /** @test */
    public function can_get_currency_symbol()
    {
        $symbol = $this->currency->symbol();

        $this->assertIsString($symbol);
        $this->assertSame($symbol, $this->currencies[2]->symbol);
    }

    /** @test */
    public function can_get_currency_iso()
    {
        $iso = $this->currency->iso();

        $this->assertIsString($iso);
        $this->assertSame($iso, $this->currencies[2]->iso);
    }

    /** @test */
    public function can_parse_currency_with_separator()
    {
        $parse = $this->currency->parse(15, true, false);

        $this->assertSame($parse, '15.00');
    }

    /** @test */
    public function can_parse_currency_without_separator()
    {
        $parse = $this->currency->parse(15, false, false);

        $this->assertSame($parse, 15.0);
    }

    /** @test */
    public function can_parse_currency_with_symbol()
    {
        $parse = $this->currency->parse(15, false, true);

        $this->assertSame($parse, '$15');
    }

    /** @test */
    public function can_parse_currency_with_separator_and_symbol()
    {
        $parse = $this->currency->parse(15, true, true);

        $this->assertSame($parse, '$15.00');
    }
}
