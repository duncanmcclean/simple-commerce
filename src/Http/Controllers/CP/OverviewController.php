<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\OverviewRequest;
use DoubleThreeDigital\SimpleCommerce\Overview;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Collection;

class OverviewController
{
    public function index(OverviewRequest $request)
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
            && Collection::find(SimpleCommerce::orderDriver()['collection'])->queryEntries()->count() > 5000;

        return view('simple-commerce::cp.overview', [
            'widgets' => Overview::widgets(),
            'showEntriesWarning' => $showEntriesWarning,
        ]);
    }
}
