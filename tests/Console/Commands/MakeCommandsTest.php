<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Console\Commands;

use DuncanMcClean\SimpleCommerce\Tests\TestCase;
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
    public function it_can_make_an_onsite_gateway_using_argument_fallback()
    {
        $path = $this->preparePath('app/Gateways/StriPal.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:gateway', ['name' => 'StriPal']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Gateways;', $this->files->get($path));
        $this->assertStringContainsString('checkout(Request $request, Order $order): array', $this->files->get($path));
        $this->assertStringNotContainsString('isOffsiteGateway(): bool', $this->files->get($path));
    }

    /** @test */
    public function it_can_make_an_onsite_gateway_by_specifying_argument()
    {
        $path = $this->preparePath('app/Gateways/StriPal.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:gateway', ['name' => 'StriPal', 'type' => 'onsite']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Gateways;', $this->files->get($path));
        $this->assertStringContainsString('checkout(Request $request, Order $order): array', $this->files->get($path));
        $this->assertStringNotContainsString('isOffsiteGateway(): bool', $this->files->get($path));
    }

    /** @test */
    public function it_can_make_an_offsite_gateway_by_specifying_argument()
    {
        $path = $this->preparePath('app/Gateways/Molipe.php');

        $this->assertFileDoesNotExist($path);

        $this->artisan('statamic:make:gateway', ['name' => 'Molipe', 'type' => 'offsite']);

        $this->assertFileExists($path);
        $this->assertStringContainsString('namespace App\Gateways;', $this->files->get($path));
        $this->assertStringNotContainsString('purchase(Purchase $data): Response', $this->files->get($path));
        $this->assertStringContainsString('isOffsiteGateway(): bool', $this->files->get($path));
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
