<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Support\Traits;

use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class HasDataTest extends TestCase
{
    protected TraitAccess $trait;

    public function setUp(): void
    {
        $this->trait = new TraitAccess;
    }

    /** @test */
    public function can_get_all_data()
    {
        $this->trait->data = [
            'foo' => 'bar',
            'fiz' => 'baa',
        ];

        $data = $this->trait->data();

        $this->assertIsArray($data);

        $this->assertArrayHasKey('foo', $data);
        $this->assertArrayHasKey('fiz', $data);
    }

    /** @test */
    public function can_add_to_array()
    {
        $this->trait->data = [
            'foo' => 'bar',
            'fiz' => 'baa',
        ];

        $data = $this->trait->data([
            'joo' => 'mla',
            'dru' => 'pal',
        ]);

        $this->assertIsObject($data);

        $this->assertArrayHasKey('foo', $this->trait->data);
        $this->assertArrayHasKey('fiz', $this->trait->data);
        $this->assertArrayHasKey('joo', $this->trait->data);
        $this->assertArrayHasKey('dru', $this->trait->data);
    }

    /** @test */
    public function can_ensure_new_value_updates_existing_value()
    {
        $this->trait->data = [
            'foo' => 'bar',
            'fiz' => 'baa',
        ];

        $data = $this->trait->data(['foo' => 'barz']);

        $this->assertIsObject($data);

        $this->assertArrayHasKey('foo', $this->trait->data);
        $this->assertArrayHasKey('fiz', $this->trait->data);

        $this->assertSame($this->trait->data['foo'], 'barz');
    }

    /** @test */
    public function returns_true_if_has_data()
    {
        $this->trait->data = [
            'foo' => 'bar',
        ];

        $has = $this->trait->has('foo');

        $this->assertTrue($has);
    }

    /** @test */
    public function returns_false_if_does_not_have_data()
    {
        $this->trait->data = [
            'foo' => 'bar',
        ];

        $has = $this->trait->has('bar');

        $this->assertFalse($has);
    }

    /** @test */
    public function can_get_data()
    {
        $this->trait->data = [
            'foo' => 'bar',
        ];

        $get = $this->trait->get('foo');

        $this->assertIsString($get);
        $this->assertSame($get, 'bar');
    }

    /** @test */
    public function returns_null_if_data_does_not_exist()
    {
        $this->trait->data = [
            'foo' => 'bar',
        ];

        $get = $this->trait->get('bar');

        $this->assertNull($get);
    }

    /** @test */
    public function can_set_new_data()
    {
        $this->trait->data = [
            'foo' => 'bar',
        ];

        $set = $this->trait->set('bar', 'foo');

        $this->assertArrayHasKey('bar', $this->trait->data);
        $this->assertSame($this->trait->data['bar'], 'foo');
    }

    /** @test */
    public function can_set_existing_data()
    {
        $this->trait->data = [
            'foo' => 'bar',
        ];

        $set = $this->trait->set('foo', 'barz');

        $this->assertArrayHasKey('foo', $this->trait->data);
        $this->assertSame($this->trait->data['foo'], 'barz');
    }

    /** @test */
    public function can_get_data_as_array()
    {
        $this->trait->data = [
            'foo' => 'bar',
        ];

        $toArray = $this->trait->toArray();

        $this->assertIsArray($toArray);

        $this->assertSame($toArray, [
            'foo' => 'bar',
        ]);
    }
}

// We're using this `TraitAccess` class instead of simply 'using' the class
// as some of the trait's method name's conflict with those of Testbench's Test Case.
class TraitAccess
{
    public $data;

    use HasData;

    protected function entry()
    {
        return new Entry;
    }
}

class Entry
{
    public function set()
    {
        return $this;
    }

    public function save()
    {
        return $this;
    }
}
