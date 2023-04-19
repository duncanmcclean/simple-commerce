<?php

use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;

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

    expect($path)->toBeFile();
    expect($this->files->get($path))->toContain('namespace App\Gateways;');
    expect($this->files->get($path))->toContain('checkout(Request $request, Order $order): array');
    $this->assertStringNotContainsString('isOffsiteGateway(): bool', $this->files->get($path));
});

it('can make an onsite gateway by specifying argument', function () {
    $path = preparePath('app/Gateways/StriPal.php');

    $this->assertFileDoesNotExist($path);

    $this->artisan('statamic:make:gateway', ['name' => 'StriPal', 'type' => 'onsite']);

    expect($path)->toBeFile();
    expect($this->files->get($path))->toContain('namespace App\Gateways;');
    expect($this->files->get($path))->toContain('checkout(Request $request, Order $order): array');
    $this->assertStringNotContainsString('isOffsiteGateway(): bool', $this->files->get($path));
});

it('can make an offsite gateway by specifying argument', function () {
    $path = preparePath('app/Gateways/Molipe.php');

    $this->assertFileDoesNotExist($path);

    $this->artisan('statamic:make:gateway', ['name' => 'Molipe', 'type' => 'offsite']);

    expect($path)->toBeFile();
    expect($this->files->get($path))->toContain('namespace App\Gateways;');
    $this->assertStringNotContainsString('purchase(Purchase $data): Response', $this->files->get($path));
    expect($this->files->get($path))->toContain('isOffsiteGateway(): bool');
});

it('can make a shipping method', function () {
    $path = preparePath('app/ShippingMethods/FirstClass.php');

    $this->assertFileDoesNotExist($path);

    $this->artisan('statamic:make:shipping-method', ['name' => 'FirstClass']);

    expect($path)->toBeFile();
    expect($this->files->get($path))->toContain('namespace App\ShippingMethods;');
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
