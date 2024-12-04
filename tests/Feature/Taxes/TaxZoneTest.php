<?php

namespace Feature\Taxes;

use DuncanMcClean\SimpleCommerce\Facades;
use DuncanMcClean\SimpleCommerce\Taxes\TaxZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\YAML;
use Tests\TestCase;

class TaxZoneTest extends TestCase
{
    protected $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = base_path('content/simple-commerce/tax-zones.yaml');

        File::delete($this->path);
        File::ensureDirectoryExists(Str::beforeLast($this->path, '/'));
    }

    #[Test]
    public function it_can_get_all_tax_zones()
    {
        File::put($this->path, YAML::dump([
            'uk' => ['name' => 'United Kingdom', 'type' => 'countries', 'countries' => ['GBR'], 'rates' => ['standard' => 20]],
            'eu' => ['name' => 'European Union', 'type' => 'countries', 'countries' => ['FRA', 'DEU'], 'rates' => ['standard' => 20]],
        ]));

        $all = Facades\TaxZone::all();

        $this->assertEquals(2, $all->count());
        $this->assertInstanceOf(Collection::class, $all);

        $this->assertEquals('United Kingdom', $all->first()->get('name'));
        $this->assertEquals('European Union', $all->last()->get('name'));
    }

    #[Test]
    public function it_can_find_a_tax_zone()
    {
        File::put($this->path, YAML::dump([
            'uk' => ['name' => 'United Kingdom', 'type' => 'countries', 'countries' => ['GBR'], 'rates' => ['standard' => 20]],
            'eu' => ['name' => 'European Union', 'type' => 'countries', 'countries' => ['FRA', 'DEU'], 'rates' => ['standard' => 20]],
        ]));

        $taxZone = Facades\TaxZone::find('uk');

        $this->assertInstanceOf(TaxZone::class, $taxZone);
        $this->assertEquals('uk', $taxZone->handle());
        $this->assertEquals('United Kingdom', $taxZone->get('name'));
        $this->assertEquals('countries', $taxZone->get('type'));
        $this->assertEquals(20, $taxZone->rates()->get('standard'));
    }

    #[Test]
    public function it_can_make_a_tax_zone()
    {
        $taxZone = Facades\TaxZone::make();

        $this->assertInstanceOf(TaxZone::class, $taxZone);
    }

    #[Test]
    public function it_can_save_a_tax_zone()
    {
        File::put($this->path, YAML::dump([
            'uk' => ['name' => 'United Kingdom', 'type' => 'countries', 'countries' => ['GBR'], 'rates' => ['standard' => 20]],
        ]));

        $taxZone = Facades\TaxZone::make()
            ->handle('eu')
            ->data([
                'name' => 'European Union',
                'type' => 'countries',
                'countries' => ['FRA', 'DEU'],
                'rates' => ['standard' => 20],
            ]);

        $save = $taxZone->save();

        $this->assertTrue($save);

        $this->assertEquals([
            'uk' => ['name' => 'United Kingdom', 'type' => 'countries', 'countries' => ['GBR'], 'rates' => ['standard' => 20]],
            'eu' => ['name' => 'European Union', 'type' => 'countries', 'countries' => ['FRA', 'DEU'], 'rates' => ['standard' => 20]],
        ], YAML::file($this->path)->parse());
    }

    #[Test]
    public function it_can_delete_a_tax_zone()
    {
        File::put($this->path, YAML::dump([
            'uk' => ['name' => 'United Kingdom', 'type' => 'countries', 'countries' => ['GBR'], 'rates' => ['standard' => 20]],
            'eu' => ['name' => 'European Union', 'type' => 'countries', 'countries' => ['FRA', 'DEU'], 'rates' => ['standard' => 20]],
        ]));

        $delete = Facades\TaxZone::find('uk')->delete();

        $this->assertTrue($delete);

        $this->assertEquals([
            'eu' => ['name' => 'European Union', 'type' => 'countries', 'countries' => ['FRA', 'DEU'], 'rates' => ['standard' => 20]],
        ], YAML::file($this->path)->parse());
    }
}