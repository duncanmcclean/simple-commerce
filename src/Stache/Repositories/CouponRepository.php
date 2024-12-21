<?php

namespace DuncanMcClean\SimpleCommerce\Stache\Repositories;

use DuncanMcClean\SimpleCommerce\Contracts\Coupons\Coupon;
use DuncanMcClean\SimpleCommerce\Contracts\Coupons\CouponRepository as RepositoryContract;
use DuncanMcClean\SimpleCommerce\Contracts\Coupons\QueryBuilder;
use DuncanMcClean\SimpleCommerce\Coupons\Blueprint;
use DuncanMcClean\SimpleCommerce\Exceptions\CouponNotFound;
use Statamic\Fields\Blueprint as StatamicBlueprint;
use Statamic\Stache\Stache;

class CouponRepository implements RepositoryContract
{
    protected $stache;
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->store = $stache->store('coupons');
    }

    public function all()
    {
        return $this->query()->get();
    }

    public function query()
    {
        return app(QueryBuilder::class);
    }

    public function find($id): ?Coupon
    {
        return $this->query()->where('id', $id)->first();
    }

    public function findOrFail($id): Coupon
    {
        $coupon = $this->find($id);

        if (! $coupon) {
            throw new CouponNotFound("Coupon [{$id}] could not be found.");
        }

        return $coupon;
    }

    public function findByCode(string $code): ?Coupon
    {
        return $this->query()->where('code', strtoupper($code))->first();
    }

    public function make(): Coupon
    {
        return app(Coupon::class);
    }

    public function save(Coupon $coupon): void
    {
        if (! $coupon->id()) {
            $coupon->id($this->stache->generateId());
        }

        $this->store->save($coupon);
    }

    public function delete(Coupon $coupon): void
    {
        $this->store->delete($coupon);
    }

    public function blueprint(): StatamicBlueprint
    {
        return (new Blueprint)();
    }

    public static function bindings(): array
    {
        return [
            Coupon::class => \DuncanMcClean\SimpleCommerce\Coupons\Coupon::class,
            \DuncanMcClean\SimpleCommerce\Contracts\Coupons\QueryBuilder::class => \DuncanMcClean\SimpleCommerce\Stache\Query\CouponQueryBuilder::class,
        ];
    }
}
