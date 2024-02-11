<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Helpers;

use Illuminate\Support\Facades\File;

trait RefreshContent
{
    public function refreshContent()
    {
        File::deleteDirectory(base_path('content/collections/customers'));
        File::deleteDirectory(base_path('content/collections/orders'));
        File::deleteDirectory(base_path('content/collections/products'));
    }
}
