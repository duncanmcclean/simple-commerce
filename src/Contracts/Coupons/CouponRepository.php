<?php

namespace DuncanMcClean\SimpleCommerce\Contracts\Coupons;

use Statamic\Fields\Blueprint;

interface CouponRepository
{
    public function all();

    public function query();

    public function find($id): ?Coupon;

    public function findOrFail($id): Coupon;

    public function findByCode(string $code): ?Coupon;

    public function make(): Coupon;

    public function save(Coupon $coupon): void;

    public function delete(Coupon $coupon): void;

    public function blueprint(): Blueprint;

    public static function bindings(): array;
}
