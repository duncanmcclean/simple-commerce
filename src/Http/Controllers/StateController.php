<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Fieldtypes\StateFieldtype;
use Illuminate\Http\Request;

class StateController
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'country' => ['required', 'string'],
        ]);

        return (new StateFieldtype())->getStates($validated['country']);
    }
}