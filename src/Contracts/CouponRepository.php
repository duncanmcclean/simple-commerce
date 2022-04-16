<?php

namespace DoubleThreeDigital\SimpleCommerce\Contracts;

interface CouponRepository
{
    public function all();

    public function find($id): ?Coupon;

    public function findByCode(string $code): ?Coupon;

    public function make(): Coupon;

    public function save(Coupon $coupon): void;

    public function delete(Coupon $coupon): void;

    public static function bindings(): array;
}
