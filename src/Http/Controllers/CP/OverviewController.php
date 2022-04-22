<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Overview;
use Illuminate\Http\Request;

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

        return view('simple-commerce::cp.overview', [
            'widgets' => Overview::widgets(),
        ]);
    }
}
