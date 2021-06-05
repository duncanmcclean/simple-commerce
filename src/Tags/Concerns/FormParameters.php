<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags\Concerns;

class FormParameters
{
    // TODO: this isn't really a 'concern', so maybe we should move this elsewhere?

    // Kept in sync with $knownParams on `FormBuilder`
    private static $knownParams = [
        'redirect', 'error_redirect', 'action_needed_redirect', 'request',
    ];

    public static function generate(array $parameters): string
    {
        $parameters = collect($parameters)
            ->filter(function ($value, $key) {
                return in_array(ltrim($key, '_'), static::$knownParams);
            })
            ->mapWithKeys(function ($value, $key) {
                $key = ltrim($key, '_');

                return ["_$key" => $value];
            })
            ->toArray();

        return hash_hmac('sha256', json_encode($parameters), app('encrypter')->getKey());
    }

    public static function check(string $hash, array $parameters)
    {
        return hash_equals(static::generate($parameters), $hash);
    }
}
