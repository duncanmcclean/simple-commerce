<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Support\Traits;

use DoubleThreeDigital\SimpleCommerce\Data\HasData;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class HasDataTest extends TestCase
{
    protected TraitAccess $trait;

    public function setUp(): void
    {
        $this->trait = new TraitAccess();
    }

    /** @test */
    public function can_get_all_data()
    {
        $this->trait->data = collect([
            'foo' => 'bar',
            'fiz' => 'baa',
        ]);

        $data = $this->trait->data();

        $this->assertIsObject($data);

        $this->assertTrue($data->has('foo'));
        $this->assertTrue($data->has('fiz'));
    }

    /** @test */
    public function can_set_data()
    {
        $data = $this->trait->data([
            'joo' => 'mla',
            'dru' => 'pal',
        ]);

        $this->assertIsObject($data);

        $this->assertArrayHasKey('joo', $this->trait->data->toArray());
        $this->assertArrayHasKey('dru', $this->trait->data->toArray());
    }

    /** @test */
    public function can_set_data_and_ensure_existing_data_has_been_overwritten()
    {
        $this->trait->data = collect([
            'foo' => 'bar',
            'fiz' => 'baa',
        ]);

        $data = $this->trait->data([
            'joo' => 'mla',
            'dru' => 'pal',
        ]);

        $this->assertIsObject($data);

        $this->assertArrayNotHasKey('foo', $this->trait->data->toArray());
        $this->assertArrayNotHasKey('fiz', $this->trait->data->toArray());
        $this->assertArrayHasKey('joo', $this->trait->data->toArray());
        $this->assertArrayHasKey('dru', $this->trait->data->toArray());
    }

    /** @test */
    public function returns_true_if_has_data()
    {
        $this->trait->data = collect([
            'foo' => 'bar',
        ]);

        $has = $this->trait->has('foo');

        $this->assertTrue($has);
    }

    /** @test */
    public function returns_false_if_does_not_have_data()
    {
        $this->trait->data = collect([
            'foo' => 'bar',
        ]);

        $has = $this->trait->has('bar');

        $this->assertFalse($has);
    }

    /** @test */
    public function can_get_data()
    {
        $this->trait->data = collect([
            'foo' => 'bar',
        ]);

        $get = $this->trait->get('foo');

        $this->assertIsString($get);
        $this->assertSame($get, 'bar');
    }

    /** @test */
    public function returns_null_if_data_does_not_exist()
    {
        $this->trait->data = collect([
            'foo' => 'bar',
        ]);

        $get = $this->trait->get('bar');

        $this->assertNull($get);
    }

    /** @test */
    public function can_set_new_data()
    {
        $this->trait->data = collect([
            'foo' => 'bar',
        ]);

        $set = $this->trait->set('bar', 'foo');

        $this->assertArrayHasKey('bar', $this->trait->data->toArray());
        $this->assertSame($this->trait->get('bar'), 'foo');
    }

    /** @test */
    public function can_set_existing_data()
    {
        $this->trait->data = collect([
            'foo' => 'bar',
        ]);

        $set = $this->trait->set('foo', 'barz');

        $this->assertArrayHasKey('foo', $this->trait->data->toArray());
        $this->assertSame($this->trait->data->get('foo'), 'barz');
    }

    /** @test */
    public function can_merge_data()
    {
        $this->trait->data = collect([
            'foo' => 'barz',
        ]);

        $set = $this->trait->merge([
            'fiz' => 'baa',
        ]);

        $this->assertArrayHasKey('foo', $this->trait->data->toArray());
        $this->assertArrayHasKey('fiz', $this->trait->data->toArray());

        $this->assertSame($this->trait->data->get('foo'), 'barz');
        $this->assertSame($this->trait->data->get('fiz'), 'baa');
    }

    /** @test */
    public function can_get_data_as_array()
    {
        $this->trait->data = collect([
            'foo' => 'bar',
        ]);

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

    public function __construct()
    {
        $this->data = collect();
    }
}
