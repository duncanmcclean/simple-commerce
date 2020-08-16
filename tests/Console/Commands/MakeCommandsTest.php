<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Console\Commands;

use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;

class MakeCommandsTest extends TestCase
{
    public $testedPaths = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);
    }

    public function tearDown(): void
    {
        $this->cleanupPaths();

        parent::tearDown();
    }

    /** @test */
    public function it_can_make_a_gateway()
    {
        $path = $this->preparePath('app/Gateways/StriPal.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:gateway', ['name' => 'StriPal']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Gateways;', $this->files->get($path));
    }

    /** @test */
    public function it_can_make_a_shipping_method()
    {
        $path = $this->preparePath('app/ShippingMethods/FirstClass.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:shipping-method', ['name' => 'FirstClass']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\ShippingMethods;', $this->files->get($path));
    }

    private function preparePath($path)
    {
        $path = base_path($path);

        $this->testedPaths[] = $path;

        return $path;
    }

    private function cleanupPaths()
    {
        foreach ($this->testedPaths as $path) {
            $this->files->isDirectory($path)
                ? $this->files->deleteDirectory($path)
                : $this->files->delete($path);
        }

        $dirs = [
            base_path('addons'),
            base_path('app/Actions'),
            base_path('app/Fieldtypes'),
            base_path('app/Scopes'),
            base_path('app/Tags'),
            base_path('app/Widgets'),
        ];

        foreach ($dirs as $dir) {
            $this->files->deleteDirectory($dir, true);
        }
    }
}
