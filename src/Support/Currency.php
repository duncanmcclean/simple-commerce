<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

use DoubleThreeDigital\SimpleCommerce\Contracts\Currency as Contract;
use DoubleThreeDigital\SimpleCommerce\Data\Currencies;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CurrencyFormatterNotWorking;
use Illuminate\Support\Facades\Config;
use Money\Currencies\ISOCurrencies;
use Money\Currency as MoneyCurrency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;
use Statamic\Sites\Site;

class Currency implements Contract
{
    public function get(Site $site): array
    {
        $siteSettings = collect(Config::get('simple-commerce.sites'))
            ->get($site->handle());

        return Currencies::where('code', $siteSettings['currency'])
            ->first();
    }

    public function parse($price, Site $site): string
    {
        try {
            $money = new Money(str_replace('.', '', $price), new MoneyCurrency($this->get($site)['code']));

            $numberFormatter = new NumberFormatter('en_US', \NumberFormatter::CURRENCY);
            $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

            return $moneyFormatter->format($money);
        } catch(\ErrorException $e) {
            throw new CurrencyFormatterNotWorking(__('simple-commerce::messages.currency_formatter_not_working'));
        }
    }

    public static function bindings(): array
    {
        return [];
    }
}
