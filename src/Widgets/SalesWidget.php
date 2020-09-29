<?php

namespace DoubleThreeDigital\SimpleCommerce\Widgets;

use Carbon\Carbon;
use Statamic\Entries\Entry as AnEntry;
use Statamic\Facades\Entry;
use Statamic\Widgets\Widget;

class SalesWidget extends Widget
{
    public function html()
    {
        $baseQuery = Entry::whereCollection(config('simple-commerce.collections.orders'))
            ->filter(function (AnEntry $entry) {
                return $entry->has('paid_date');
            });

        $sevenDays = $baseQuery
            ->filter(function (AnEntry $entry) {
                return Carbon::parse($entry->get('paid_date')) >= Carbon::now()->subDays(7);
            });

        $fourteenDays = $baseQuery
            ->filter(function (AnEntry $entry) {
                return Carbon::parse($entry->get('paid_date')) >= Carbon::now()->subDays(14);
            });

        $thirtyDays = $baseQuery
            ->filter(function (AnEntry $entry) {
                return Carbon::parse($entry->get('paid_date')) >= Carbon::now()->subDays(30);
            });

        return view('simple-commerce::widgets.sales-widget', [
            'sevenDays'    => $sevenDays,
            'fourteenDays' => $fourteenDays,
            'thirtyDays'   => $thirtyDays,
        ]);
    }
}
