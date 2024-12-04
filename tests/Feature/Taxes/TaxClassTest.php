<?php

namespace Tests\Feature\Taxes;

use DuncanMcClean\SimpleCommerce\Facades;
use DuncanMcClean\SimpleCommerce\Taxes\TaxClass;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\YAML;
use Tests\TestCase;

class TaxClassTest extends TestCase
{
    protected $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = base_path('content/simple-commerce/tax-classes.yaml');

        File::delete($this->path);
        File::ensureDirectoryExists(Str::beforeLast($this->path, '/'));
    }

    #[Test]
    public function it_can_get_all_tax_classes()
    {
        File::put($this->path, YAML::dump([
            'standard' => ['name' => 'Standard Tax'],
            'reduced' => ['name' => 'Reduced Tax'],
        ]));

        $all = Facades\TaxClass::all();

        $this->assertEquals(2, $all->count());
        $this->assertInstanceOf(Collection::class, $all);

        $this->assertEquals('Standard Tax', $all->first()->get('name'));
        $this->assertEquals('Reduced Tax', $all->last()->get('name'));
    }

    #[Test]
    public function it_can_find_a_tax_class()
    {
        File::put($this->path, YAML::dump([
            'standard' => ['name' => 'Standard Tax'],
            'reduced' => ['name' => 'Reduced Tax'],
        ]));

        $taxClass = Facades\TaxClass::find('standard');

        $this->assertInstanceOf(TaxClass::class, $taxClass);
        $this->assertEquals('standard', $taxClass->handle());
        $this->assertEquals('Standard Tax', $taxClass->get('name'));
    }

    #[Test]
    public function it_can_make_a_tax_class()
    {
        $taxClass = Facades\TaxClass::make();

        $this->assertInstanceOf(TaxClass::class, $taxClass);
    }

    #[Test]
    public function it_can_save_a_tax_class()
    {
        File::put($this->path, YAML::dump([
            'standard' => ['name' => 'Standard Tax'],
        ]));

        $taxClass = Facades\TaxClass::make()
            ->handle('reduced')
            ->data(['name' => 'Reduced Tax']);

        $save = $taxClass->save();

        $this->assertTrue($save);

        $this->assertEquals([
            'standard' => ['name' => 'Standard Tax'],
            'reduced' => ['name' => 'Reduced Tax'],
        ], YAML::file($this->path)->parse());
    }

    #[Test]
    public function it_can_delete_a_tax_class()
    {
        File::put($this->path, YAML::dump([
            'standard' => ['name' => 'Standard Tax'],
            'reduced' => ['name' => 'Reduced Tax'],
        ]));

        $delete = Facades\TaxClass::find('standard')->delete();

        $this->assertTrue($delete);

        $this->assertEquals([
            'reduced' => ['name' => 'Reduced Tax'],
        ], YAML::file($this->path)->parse());
    }
}