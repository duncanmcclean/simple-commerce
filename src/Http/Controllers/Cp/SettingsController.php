<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\SettingsUpdateRequest;
use Illuminate\Support\Facades\Config;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class SettingsController extends CpController
{
    public function edit()
    {
        if (! auth()->user()->hasPermission('edit settings') && auth()->user()->isSuper() != true) {
            abort(401);
        }

        $crumbs = Breadcrumbs::make([
            ['text' => 'Simple Commerce', 'url' => '#'],
        ]);

        $values = Config::get('commerce');
        $blueprint = Blueprint::find('simplecommerce_settings');

        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('commerce::cp.settings.index', [
            'crumbs' => $crumbs,
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $values,
            'meta'      => $fields->meta(),
            'action' => cp_route('settings.update'),
        ]);
    }

    public function update(SettingsUpdateRequest $request)
    {
        // TODO: use a policy for this?
        if (! auth()->user()->hasPermission('edit settings') && auth()->user()->isSuper() != true) {
            abort(401);
        }

        foreach ($request->all() as $key => $value) {
            Config::set("commerce.{$key}", $value); // setting like this doesn't actually write back to the file
        }

        return back();
    }
}
