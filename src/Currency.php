<?php

namespace DoubleThreeDigital\SimpleCommerce;

use DoubleThreeDigital\SimpleCommerce\Exceptions\CurrencyFormatterNotWorking;
use DoubleThreeDigital\SimpleCommerce\Exceptions\SiteNotConfiguredException;
use Illuminate\Support\Facades\Config;
use Money\Currencies\ISOCurrencies;
use Money\Currency as MoneyCurrency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;
use Statamic\Sites\Site;

class Currency
{
    public static function get(Site $site): array
    {
        $siteSettings = collect(Config::get('simple-commerce.sites'))
            ->get($site->handle());

        if (! $siteSettings) {
            throw new SiteNotConfiguredException("Site config not found [{$site->handle()}]");
        }

        return Currencies::where('code', $siteSettings['currency'])
            ->first();
    }

    public static function parse($amount, Site $site): string
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
            $money = new Money(str_replace('.', '', (int) $amount), new MoneyCurrency(self::get($site)['code']));

            $numberFormatter = new NumberFormatter($site->locale(), \NumberFormatter::CURRENCY);
            $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

            return $moneyFormatter->format($money);
        } catch (\ErrorException $e) {
            throw new CurrencyFormatterNotWorking('Extension PHP-intl not installed.');
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
