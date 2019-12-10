<?php

namespace Damcclean\Commerce\Models\File;

use Statamic\Data\ExistsAsFile;

class Customer
{
    use ExistsAsFile;

    public $data;
    public $filename;

    public function __construct($data, $filename)
    {
        $this->data = $data;
        $this->filename = $filename;
    }

    public function path()
    {
        return base_path().'/content/commerce/customers/'.$this->filename.'.yaml';
    }

    public function fileData()
    {
        return $this->data;
    }
}
