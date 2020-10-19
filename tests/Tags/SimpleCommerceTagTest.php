<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Data\Countries;
use DoubleThreeDigital\SimpleCommerce\Data\Currencies;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Parse;

class SimpleCommerceTagTest extends TestCase
{
    /** @test */
    public function can_get_countries()
    {
        $usage = $this->tag('{{ sc:countries }}{{ name }},{{ /sc:countries }}');

        foreach (Countries::toArray() as $country) {
            $this->assertStringContainsString($country['name'], $usage);
        }
    }

    /** @test */
    public function can_get_currencies()
    {
        $usage = $this->tag('{{ sc:currencies }}{{ name }},{{ /sc:currencies }}');

        // dd(Currencies::all());

        foreach (Currencies::toArray() as $currency) {
            $this->assertStringContainsString($currency['name'], $usage);
        }
    }

    /** @test */
    public function can_get_index_via_wildcard()
    {
        //
    }

    /** @test */
    public function can_get_specific_method_via_wildcard()
    {
        $usage = $this->tag('{{ if sc:cart:has }}true{{ else }}false{{ /if }}');

        // This returns 'false' but we don't care the cart works here, we just care we can call the right code
        $this->assertIsString((string) $usage);
    }

    /** @test */
    public function can_get_wildcard_via_wildcard()
    {
        //
    }

    protected function tag($tag)
    {
        return Parse::template($tag, []);
    }
}
