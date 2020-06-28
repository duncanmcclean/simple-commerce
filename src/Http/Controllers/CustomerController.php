<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\User;

class CustomerController extends BaseActionController
{
    public function show()
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

        dd($request->all());

        $user = User::current();

        $values = $user
            ->blueprint()
            ->fields()
            ->addValues(Arr::except($request->all(), ['_token', '_params']))
            // ->validate([])
            ->process()
            ->values()
            ->except([
                'email', 'groups', 'roles',
            ]);

        foreach ($values as $key => $value) {
            $user->set($key, $value);
        }

        if ($request->has('email')) $user->email($request->email);

        if (User::current()->can('edit roles')) {
            $user->roles($request->roles);
        }

        if (User::current()->can('edit user groups')) {
            $user->groups($request->groups);
        }

        $user->save();

        return $this->withSuccess($request);



        
        // dd($request->all());

        // return $request->has('redirect') ?
        //     redirect($request->redirect) :
        //     back();
    }
}