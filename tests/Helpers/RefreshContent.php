<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Helpers;

use Illuminate\Support\Facades\File;
use Statamic\Facades\Stache;

trait RefreshContent
{
    public function refreshContent()
    {
        File::deleteDirectory(Stache::store('entries')->directory().'customers');
        File::deleteDirectory(Stache::store('entries')->directory().'orders');
        File::deleteDirectory(Stache::store('entries')->directory().'products');

        Stache::store('entries')->clear();
    }
}
