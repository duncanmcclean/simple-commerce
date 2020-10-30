<?php

namespace DoubleThreeDigital\SimpleCommerce;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Statamic\Facades\Addon;
use Statamic\Statamic;

class Woodland
{
    /**
     * 'Woodland' is a web service that is hit by Simple Commerce regularly to gather anonymous
     * statistics about sites. It gathers information like Simple Commerce version and whether or
     * not you have a valid license from the Marketplace.
     *
     * For questions: please email addons@doublethree.digital
     */

    protected static $endpoint = 'https://doublethree.digital/woodland/check';
    protected static $cacheKey = 'simple-commerce.woodland.check';

    public static function check()
    {
        if (config('app.env') === 'testing') {
            return [
                'status' => true,
            ];
        }

        if (Cache::get(static::$cacheKey)) {
            return Cache::get(static::$cacheKey);
        }

        $addon = Addon::get('doublethreedigital/simple-commerce');

        $payload = [
            'statamic' => [
                'version' => Statamic::version(),
            ],
            'addon' => [
                'name'         => 'simple_commerce',
                'version'       => $addon->version(),
                'valid_license' => !is_null($addon->license()) ? $addon->license()->valid() : false,
            ],
            'site'  => [
                'hostname' => request()->getHost(),
            ],
        ];

        try {
            $request = Http::post(static::$endpoint, $payload);

            Cache::put(static::$cacheKey, $request->json(), now()->addHour());
        } catch (\Exception $e) {
            return 'Unable to reach the Simple Commerce Woodland. Try again later.';
        }
    }

    public static function response()
    {
        return Cache::get(static::$cacheKey);
    }

    public static function status()
    {
        return self::response()['status'];
    }
}
