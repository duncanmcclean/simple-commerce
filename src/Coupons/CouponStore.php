<?php

namespace DuncanMcClean\SimpleCommerce\Coupons;

use DuncanMcClean\SimpleCommerce\Facades\Coupon;
use Illuminate\Support\Arr;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;

class CouponStore extends BasicStore
{
    public function key()
    {
        return 'simple-commerce-coupons';
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = YAML::file($path)->parse($contents);

        if (! $id = Arr::pull($data, 'id')) {
            $idGenerated = true;
            $id = app('stache')->generateId();
        }

        $coupon = Coupon::make()
            ->id($id)
            ->code(Arr::pull($data, 'code'))
            ->type(Arr::pull($data, 'type'))
            ->value(Arr::pull($data, 'value'))
            ->data(Arr::except($data, ['code', 'type', 'value']));

        if (isset($idGenerated)) {
            $coupon->save();
        }

        return $coupon;
    }
}
