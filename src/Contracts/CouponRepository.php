<?php

namespace DuncanMcClean\SimpleCommerce\Contracts;

use DuncanMcClean\SimpleCommerce\Coupons\CouponQueryBuilder;
use Statamic\Data\DataCollection;

interface CouponRepository
{
    public function all(): DataCollection;

    public function find($id): ?Coupon;

    public function findByCode(string $code): ?Coupon;

    public function save(Coupon $coupon): void;

    public function delete(Coupon $coupon): void;

    public function query(): CouponQueryBuilder;

    public function make(): Coupon;
}
