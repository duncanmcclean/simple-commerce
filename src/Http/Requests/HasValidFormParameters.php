<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests;

use Illuminate\Http\Request;

trait HasValidFormParameters
{
    // Kept in sync with $knownParams on `FormBuilder`
    private static $knownParams = [
        'redirect', 'error_redirect', 'action_needed_redirect', 'request',
    ];

    public function hasValidFormParameters(Request $request = null)
    {
        if (! $request) {
            $request = $this;
        }

        if (! $request->has('_params')) {
            throw new \Exception("Given form parameters are not valid.");
        }

        $parameters = collect($request->all())
            ->filter(function ($value, $key) {
                return in_array(ltrim($key, '_'), static::$knownParams);
            });

        return hash_equals(hash_hmac('sha256', $parameters->join('|'), app('encrypter')->getKey()), $request->get('_params'));
    }
}
