<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;

class BaseActionController extends Controller
{
    protected function withSuccess(Request $request, array $data = []): RedirectResponse
    {
        return $this->redirect('redirect', $request)->with([
            'simple-commerce' => $data,
        ]);
    }

    protected function withErrors(Request $request, string $errorMessage): RedirectResponse
    {
        return $this->redirect('error_redirect', $request)->withErrors($errorMessage, 'charge');
    }

    protected function redirect($field, Request $request): RedirectResponse
    {
        $redirect = $request->input($field, false);

        return $redirect ? redirect($redirect) : back();
    }
}