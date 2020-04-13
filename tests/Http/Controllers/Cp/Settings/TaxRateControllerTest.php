<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp\Settings;

use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class TaxRateControllerTest extends TestCase
{
    /** @test */
    public function can_get_index()
    {
        $this
            ->actAsSuper()
            ->get(cp_route('settings.tax-rates.index'))
            ->assertOk()
            ->assertSee('Tax Rates');
    }
}
