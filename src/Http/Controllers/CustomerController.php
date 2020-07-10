<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\User;

class CustomerController extends BaseActionController
{
    public function index()
    {
        if (Auth::guest()) {
            return back()->with('errors', 'You can only get a customer when logged in.');
        }

        return User::current()->data();
    }

    public function update(Request $request)
    {
        if (Auth::guest()) {
            return back()->with('errors', 'You can only save a customer when logged in.');
        }

        $user = User::current();

        $values = $user
            ->blueprint()
            ->fields()
            ->addValues($request->all())
            ->process()
            ->values()
            ->except([
                'email', 'groups', 'roles',
            ]);

        foreach ($values as $key => $value) {
            $user->set($key, $value);
        }

        if ($request->has('email')) $user->email($request->email);

        $user->save();

        return $this->withSuccess($request);
    }
}