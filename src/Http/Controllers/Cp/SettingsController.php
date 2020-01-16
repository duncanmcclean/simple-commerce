<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use Statamic\CP\Breadcrumbs;
use Statamic\Http\Controllers\CP\CpController;

class SettingsController extends CpController
{
    public function index()
    {
        if (! auth()->user()->hasPermission('edit settings') || ! auth()->user()->isSuper()) {
            abort(401);
        }

        $crumbs = Breadcrumbs::make([
            ['text' => 'Simple Commerce', 'url' => '#'],
        ]);

        return view('commerce::cp.settings.index', [
            'crumbs' => $crumbs
        ]);
    }
}
