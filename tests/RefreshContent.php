<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use Illuminate\Support\Facades\File;

trait RefreshContent
{
    public function refreshContent()
    {
        File::deleteDirectory(base_path('content/collections/customers'));
        File::deleteDirectory(base_path('content/collections/orders'));
        File::deleteDirectory(base_path('content/collections/coupons'));
        File::deleteDirectory(base_path('content/collections/products'));
    }
}
