<?php

namespace Damcclean\Commerce\Tests;

use Illuminate\Filesystem\Filesystem;

class SwappableContent
{
    public function swapOutContent()
    {
        (new Filesystem())->moveDirectory(base_path().'/content', storage_path().'/swappable-content');
        (new Filesystem())->copyDirectory(base_path().'/backup/content', base_path().'/content');
    }
}
