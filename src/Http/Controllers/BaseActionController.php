<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;

class BaseActionController extends Controller
{
    protected function withSuccess(Request $request, array $data = [])
    {
        if ($request->wantsJson()) {
            $data = array_merge($data, [
                'status'  => 'success',
                'message' => null,
            ]);

            return response()->json($data);
        }

        return $request->_redirect ?
            redirect($request->_redirect)->with($data)
            : back()->with($data);
    }

    protected function withErrors(Request $request, string $errorMessage)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'error',
                'message' => $errorMessage,
            ]);
        }

        return $request->_error_redirect
            ? redirect($request->_error_redirect)->withErrors($errorMessage, 'simple-commerce')
            : back()->withErrors($errorMessage, 'simple-commerce');
    }
}
