<?php

namespace DuncanMcClean\SimpleCommerce\Support;

use DuncanMcClean\SimpleCommerce\Data\Currencies;
use DuncanMcClean\SimpleCommerce\Exceptions\CurrencyFormatterNotWorking;
use Money\Currencies\ISOCurrencies;
use Money\Currency as MoneyCurrency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money as MoneyPhp;
use NumberFormatter;
use Statamic\Sites\Site;

class Money
{
    public static function get(Site $site): array
    {
        return Currencies::firstWhere('code', strtoupper($site->attribute('currency')));
    }

    public static function format($amount, Site $site): string
    {
        if (is_string($amount)) {
            if (str_contains($amount, '.')) {
                $amount = str_replace('.', '', $amount);
            }

            $amount = (int) $amount;
        }

        if (is_float($amount)) {
            $amount = $amount * 100;
        }

        try {
            $money = new MoneyPhp(str_replace('.', '', (int) $amount), new MoneyCurrency(self::get($site)['code']));

            $numberFormatter = new NumberFormatter($site->locale(), \NumberFormatter::CURRENCY);

            if ($decimalSeparator = $site->attribute('decimal_separator')) {
                $numberFormatter->setSymbol(\NumberFormatter::MONETARY_SEPARATOR_SYMBOL, $decimalSeparator);
            }

            if ($thousandSeparator = $site->attribute('thousand_separator')) {
                $numberFormatter->setSymbol(\NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, $thousandSeparator);
            }

            $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies);

            return $moneyFormatter->format($money);
        } catch (\ErrorException $e) {
            throw new CurrencyFormatterNotWorking;
        }
    }

    public static function toPence(float $amount): int
    {
        return $amount * 100;
    }

    public static function toDecimal(int $amount): float
    {
        return $amount / 100;
    }
}
