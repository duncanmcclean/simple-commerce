<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Overview;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;
use Statamic\Facades\Collection;

class OverviewController
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $data = collect($request->get('widgets'))
                ->mapWithKeys(function ($widgetHandle) use ($request) {
                    $widget = Overview::widget($widgetHandle);

                    return [
                        $widgetHandle => $widget['callback']($request),
                    ];
                })
                ->toArray();

            return ['data' => $data];
        }

        $showEntriesWarning = $request->user()->isSuper()
            && isset(SimpleCommerce::orderDriver()['collection'])
            && Collection::find(SimpleCommerce::orderDriver()['collection'])->entries()->count() > 5000;

        return view('simple-commerce::cp.overview', [
            'widgets' => Overview::widgets(),
            'showEntriesWarning' => $showEntriesWarning,
        ]);
    }
}
