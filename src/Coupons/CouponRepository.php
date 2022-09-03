<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons;

use Statamic\Data\DataCollection;
use Statamic\Stache\Stache;

class CouponRepository
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('simple-commerce-coupons');
    }

    public function all(): DataCollection
    {
        return $this->query()->get();
    }

    public function find($id): ?Coupon
    {
        return $this->query()->where('id', $id)->first();
    }

    public function findByCode(string $code): ?Coupon
    {
        return $this->query()->where('code', $code)->first();
    }

    public function save($coupon)
    {
        $this->store->save($coupon);
    }

    public function delete($coupon)
    {
        $this->store->delete($coupon);
    }

    public function query()
    {
        return new CouponQueryBuilder($this->store);
    }

    public function make(): Coupon
    {
        return new Coupon();
    }
}
