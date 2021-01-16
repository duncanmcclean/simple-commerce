<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;

class BaseActionController extends Controller
{
    protected function withSuccess(Request $request, array $data = []): RedirectResponse
    {
        if ($request->wantsJson()) {
            $data = array_merge($data, [
                'status' => 'success',
            ]);

            return response()->json($data);
        }

        return $request->_redirect ?
            redirect($request->_redirect)->with($data) :
            back()->with($data);
    }

    protected function withErrors(Request $request, string $errorMessage): RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $errorMessage,
            ]);
        }

        return back()
            ->withErrors($errorMessage, 'simple-commerce');
    }
}
