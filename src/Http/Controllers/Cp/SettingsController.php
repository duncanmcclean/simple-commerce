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
        $crumbs = Breadcrumbs::make([
            ['text' => 'Simple Commerce'],
        ]);

        $values = Config::get('commerce');
        $blueprint = Blueprint::find('simple-commerce/simplecommerce_settings');

        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('commerce::cp.settings.edit', [
            'crumbs' => $crumbs,
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $values,
            'meta'      => $fields->meta(),
            'action' => cp_route('settings.update'),
        ]);
    }

    public function update(SettingsUpdateRequest $request)
    {
        foreach ($request->all() as $key => $value) {
            Config::set("commerce.{$key}", $value); // setting like this doesn't actually write back to the file
        }

        return back();
    }
}
