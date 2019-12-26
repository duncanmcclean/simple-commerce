<?php

namespace Damcclean\Commerce\Tests\Facades;

use Damcclean\Commerce\Facades\Coupon;
use Damcclean\Commerce\Tests\TestCase;

class CouponFacadeTest extends TestCase
{
    /**
     * @test
     * We just want to make sure that we get through
     * to the FileCouponRepository.
     */
    public function facade_can_get_create_rules()
    {
        $rules = Coupon::createRules('coupons');

        $this->assertIsArray($rules);
    }
}
