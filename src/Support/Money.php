<?php

namespace DuncanMcClean\SimpleCommerce\Support;

use DuncanMcClean\SimpleCommerce\Currencies;
use DuncanMcClean\SimpleCommerce\Exceptions\CurrencyFormatterNotWorking;
use DuncanMcClean\SimpleCommerce\Exceptions\SiteNotConfiguredException;
use Illuminate\Support\Facades\Config;
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
        $siteSettings = collect(Config::get('simple-commerce.sites'))
            ->get($site->handle());

        if (! $siteSettings) {
            throw new SiteNotConfiguredException("Site config not found [{$site->handle()}]");
        }

        return Currencies::where('code', $siteSettings['currency'])
            ->first();
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

            $currencyFormattingConfig = Config::get("simple-commerce.sites.{$site->handle()}.currency_formatting");

            if (isset($currencyFormattingConfig['decimal_separator'])) {
                $numberFormatter->setSymbol(\NumberFormatter::MONETARY_SEPARATOR_SYMBOL, $currencyFormattingConfig['decimal_separator']);
            }

            if (isset($currencyFormattingConfig['thousand_separator'])) {
                $numberFormatter->setSymbol(\NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, $currencyFormattingConfig['thousand_separator']);
            }

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
