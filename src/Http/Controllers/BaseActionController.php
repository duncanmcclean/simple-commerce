<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;

class BaseActionController extends Controller
{
    protected function withSuccess(Request $request, array $data = []): RedirectResponse
    {
        return $request->has('_params') ? 
            redirect(decrypt($request->all()['_params'])[0])->with($data) : 
            back()->with($data);
    }

    protected function withErrors(Request $request, string $errorMessage): RedirectResponse
    {
        return back()
            ->withErrors($errorMessage, 'simple-commerce');
    }

    // protected function redirect($field, Request $request): RedirectResponse
    // {
    //     $redirect = $request->input($field, false);

    //     return $redirect ? redirect($redirect) : back();
    // }
}