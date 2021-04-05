<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Tags;

use DoubleThreeDigital\SimpleCommerce\Support\Countries;
use DoubleThreeDigital\SimpleCommerce\Support\Currencies;
use DoubleThreeDigital\SimpleCommerce\Tags\SimpleCommerceTag as Tag;
use DoubleThreeDigital\SimpleCommerce\Tags\SubTag;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Parse;

class SimpleCommerceTagTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        SimpleCommerceTag::register();
    }

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

        foreach (Currencies::toArray() as $currency) {
            $this->assertStringContainsString($currency['name'], $usage);
        }
    }

    /** @test */
    public function can_get_sub_tag_index()
    {
        $usage = $this->tag('{{ sc:test }}');

        $this->assertSame('This is the index method.', (string) $usage);
    }

    /** @test */
    public function can_get_sub_tag_method()
    {
        $usage = $this->tag('{{ sc:test:cheese }}');

        $this->assertSame('This is the cheese method.', (string) $usage);
    }

    /** @test */
    public function can_get_sub_tag_wildcard()
    {
        $usage = $this->tag('{{ sc:test:something }}');

        $this->assertSame('This is the wildcard method.', (string) $usage);
    }

    protected function tag($tag)
    {
        return Parse::template($tag, []);
    }
}

class SimpleCommerceTag extends Tag
{
    protected $tagClasses = [
        'test'     => TestTag::class,
    ];
}

class TestTag extends SubTag
{
    public function index()
    {
        return 'This is the index method.';
    }

    public function cheese()
    {
        return 'This is the cheese method.';
    }

    public function wildcard()
    {
        return 'This is the wildcard method.';
    }
}
