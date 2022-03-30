<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Rules;

use DoubleThreeDigital\SimpleCommerce\Rules\CountryExists;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class CountryExistsTest extends TestCase
{
    /** @test */
    public function it_passes_for_matching_iso_code()
    {
        $data = [
            'country' => 'GB',
        ];

        $validate = Validator::make($data, [
            'country' => [new CountryExists()],
        ]);

        $this->assertFalse($validate->fails());
    }

    /** @test */
    public function it_fails_for_made_up_country()
    {
        $data = [
            'country' => 'stataland',
        ];

        $validate = Validator::make($data, [
            'country' => [new CountryExists()],
        ]);

        $this->assertTrue($validate->fails());
    }
}
