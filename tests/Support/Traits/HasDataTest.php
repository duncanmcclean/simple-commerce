<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Support\Traits;

use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class HasDataTest extends TestCase
{
    use HasData;

    public $data;

    /** @test */
    public function can_get_all_data()
    {
        $this->data = [
            'foo' => 'bar',
            'fiz' => 'baa',
        ];

        $data = $this->data();

        $this->assertIsArray($data);

        $this->assertArrayHasKey('foo', $data);
        $this->assertArrayHasKey('fiz', $data);
    }

    /** @test */
    public function can_add_to_array()
    {
        $this->data = [
            'foo' => 'bar',
            'fiz' => 'baa',
        ];

        $data = $this->data([
            'joo' => 'mla',
            'dru' => 'pal',
        ]);

        $this->assertIsObject($data);

        $this->assertArrayHasKey('foo', $this->data);
        $this->assertArrayHasKey('fiz', $this->data);
        $this->assertArrayHasKey('joo', $this->data);
        $this->assertArrayHasKey('dru', $this->data);
    }

    /** @test */
    public function can_ensure_new_value_updates_existing_value()
    {
        $this->data = [
            'foo' => 'bar',
            'fiz' => 'baa',
        ];

        $data = $this->data(['foo' => 'barz']);

        $this->assertIsObject($data);

        $this->assertArrayHasKey('foo', $this->data);
        $this->assertArrayHasKey('fiz', $this->data);

        $this->assertSame($this->data['foo'], 'barz');
    }

    /** @test */
    public function returns_true_if_has_data()
    {
        $this->data = [
            'foo' => 'bar',
        ];

        $has = $this->has('foo');

        $this->assertTrue($has);
    }

    /** @test */
    public function returns_false_if_does_not_have_data()
    {
        $this->data = [
            'foo' => 'bar',
        ];

        $has = $this->has('bar');

        $this->assertFalse($has);
    }

    /** @test */
    public function can_get_data()
    {
        $this->data = [
            'foo' => 'bar',
        ];

        $get = $this->get('foo');

        $this->assertIsString($get);
        $this->assertSame($get, 'bar');
    }

    /** @test */
    public function returns_null_if_data_does_not_exist()
    {
        $this->data = [
            'foo' => 'bar',
        ];

        $get = $this->get('bar');

        $this->assertNull($get);
    }

    /** @test */
    public function can_set_new_data()
    {
        $this->data = [
            'foo' => 'bar',
        ];

        $set = $this->set('bar', 'foo');

        $this->assertArrayHasKey('bar', $this->data);
        $this->assertSame($this->data['bar'], 'foo');
    }

    /** @test */
    public function can_set_existing_data()
    {
        $this->data = [
            'foo' => 'bar',
        ];

        $set = $this->set('foo', 'barz');

        $this->assertArrayHasKey('foo', $this->data);
        $this->assertSame($this->data['foo'], 'barz');
    }

    /** @test */
    public function can_get_data_as_array()
    {
        $this->data = [
            'foo' => 'bar',
        ];

        $toArray = $this->toArray();

        $this->assertIsArray($toArray);

        $this->assertSame($toArray, [
            'foo' => 'bar',
        ]);
    }

    // Let's fake the `entry` method, it's called in the `set` method
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
