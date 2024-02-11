<?php

use DuncanMcClean\SimpleCommerce\Products\DigitalProducts\LicenseKeyRepository;

$repository = app(LicenseKeyRepository::class);

it('can generate license key', function () use ($repository) {
    $key = $repository->generate();

    $this->assertIsString($key);
    $this->assertSame(24, strlen($key));
});
