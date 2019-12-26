<?php

namespace Damcclean\Commerce\Tests\Facades;

use Damcclean\Commerce\Facades\Customer;
use Damcclean\Commerce\Tests\TestCase;

class CustomerFacadeTest extends TestCase
{
    /**
     * @test
     * We just want to make sure that we get through
     * to the FileCouponRepository.
     */
    public function facade_can_get_create_rules()
    {
        $rules = Customer::createRules('customers');

        $this->assertIsArray($rules);
    }
}
