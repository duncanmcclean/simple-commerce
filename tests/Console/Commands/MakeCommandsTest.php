<?php

use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;

uses(TestCase::class);
beforeEach(function () {
    $this->files = app(Filesystem::class);
});

afterEach(function () {
    cleanupPaths();
});


it('can make an onsite gateway using argument fallback', function () {
    $path = preparePath('app/Gateways/StriPal.php');

    $this->assertFileDoesNotExist($path);

    $this->artisan('statamic:make:gateway', ['name' => 'StriPal']);

    $this->assertFileExists($path);
    $this->assertStringContainsString('namespace App\Gateways;', $this->files->get($path));
    $this->assertStringContainsString('checkout(Request $request, Order $order): array', $this->files->get($path));
    $this->assertStringNotContainsString('isOffsiteGateway(): bool', $this->files->get($path));
});

it('can make an onsite gateway by specifying argument', function () {
    $path = preparePath('app/Gateways/StriPal.php');

    $this->assertFileDoesNotExist($path);

    $this->artisan('statamic:make:gateway', ['name' => 'StriPal', 'type' => 'onsite']);

    $this->assertFileExists($path);
    $this->assertStringContainsString('namespace App\Gateways;', $this->files->get($path));
    $this->assertStringContainsString('checkout(Request $request, Order $order): array', $this->files->get($path));
    $this->assertStringNotContainsString('isOffsiteGateway(): bool', $this->files->get($path));
});

it('can make an offsite gateway by specifying argument', function () {
    $path = preparePath('app/Gateways/Molipe.php');

    $this->assertFileDoesNotExist($path);

    $this->artisan('statamic:make:gateway', ['name' => 'Molipe', 'type' => 'offsite']);

    $this->assertFileExists($path);
    $this->assertStringContainsString('namespace App\Gateways;', $this->files->get($path));
    $this->assertStringNotContainsString('purchase(Purchase $data): Response', $this->files->get($path));
    $this->assertStringContainsString('isOffsiteGateway(): bool', $this->files->get($path));
});

it('can make a shipping method', function () {
    $path = preparePath('app/ShippingMethods/FirstClass.php');

    $this->assertFileDoesNotExist($path);

    $this->artisan('statamic:make:shipping-method', ['name' => 'FirstClass']);

    $this->assertFileExists($path);
    $this->assertStringContainsString('namespace App\ShippingMethods;', $this->files->get($path));
});

// Helpers
function preparePath($path)
{
    $path = base_path($path);

    test()->testedPaths[] = $path;

    return $path;
}

function cleanupPaths()
{
    foreach (test()->testedPaths as $path) {
        test()->files->isDirectory($path)
            ? test()->files->deleteDirectory($path)
            : test()->files->delete($path);
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
        test()->files->deleteDirectory($dir, true);
    }
}
