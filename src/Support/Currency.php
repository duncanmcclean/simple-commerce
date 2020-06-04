<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

use DoubleThreeDigital\SimpleCommerce\Exceptions\CurrencyNotConfigured;
use DoubleThreeDigital\SimpleCommerce\Models\Currency as CurrencyModel;

class Currency
{
    public static $currency = [];

    public function __construct()
    {
        if (config('simple-commerce.currency.iso') === null) {
            throw new CurrencyNotConfigured();
        }

        static::$currency = CurrencyModel::where('iso', config('simple-commerce.currency.iso'))->first()->toArray();
    }

    public function get(): array
    {
        return self::$currency;
    }

    public function symbol(): string
    {
        return self::$currency['symbol'];
    }

    public function iso(): string
    {
        return self::$currency['iso'];
    }

    public function parse(float $total, bool $showSeparator = true, bool $showSymbol = true): string
    {
        if ($showSeparator == true) {
            $total = number_format($total, 2, '.', config('simple-commerce.currency.separator'));
        }

        if ($showSymbol == true) {
            $symbol = self::$currency['symbol'];

            if (config('simple-commerce.currency.position') === 'left') {
                $total = $symbol.$total;
            }

            if (config('simple-commerce.currency.position') === 'right') {
                $total = $total.$symbol;
            }
        }

        return $total;
    }
}
