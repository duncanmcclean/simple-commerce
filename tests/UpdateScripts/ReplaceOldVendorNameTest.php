<?php

use DuncanMcClean\SimpleCommerce\UpdateScripts\v6_0\ReplaceOldVendorName;
use Illuminate\Support\Facades\File;

it('updates product_type field for physical product', function () {
    // Obviously, the contents aren't real PHP but all we need to test is that
    // the string replacement works as expected.
    File::shouldReceive('get')
        ->with(config_path('simple-commerce.php'))
        ->andReturn("\DoubleThreeDigital\SimpleCommerce\Shipping\FreeShipping::class // 'tax_engine' => \DoubleThreeDigital\SimpleCommerce\Tax\BasicTaxEngine::class");

    File::shouldReceive('put')->with(
        config_path('simple-commerce.php'),
        "\DuncanMcClean\SimpleCommerce\Shipping\FreeShipping::class // 'tax_engine' => \DuncanMcClean\SimpleCommerce\Tax\BasicTaxEngine::class"
    );

    (new ReplaceOldVendorName('duncanmcclean/simple-commerce', '6.0.0'))->update();
});
