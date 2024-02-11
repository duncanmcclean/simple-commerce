<?php

namespace DuncanMcClean\SimpleCommerce\Coupons;

use DuncanMcClean\SimpleCommerce\Contracts\CouponRepository as ContractsCouponRepository;
use Statamic\Data\DataCollection;
use Statamic\Stache\Stache;

class CouponRepository implements ContractsCouponRepository
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

    public function save($coupon): void
    {
        $this->store->save($coupon);
    }

    public function delete($coupon): void
    {
        $this->store->delete($coupon);
    }

    public function query(): CouponQueryBuilder
    {
        return new CouponQueryBuilder($this->store);
    }

    public function make(): Coupon
    {
        return new Coupon();
    }
}
