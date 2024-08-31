<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;

class BaseActionController extends Controller
{
    protected function formSuccess(Request $request, $data)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $data;
        }

        return $request->_redirect ?
            redirect($request->_redirect)->with($data)
            : back()->with($data);
    }
}
