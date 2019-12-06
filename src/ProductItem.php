<?php

namespace Damcclean\Commerce;

use Statamic\Data\ExistsAsFile;
use Statamic\Facades\Blueprint;

class ProductItem
{
    use ExistsAsFile;

    public $data;
    public $filename;

    public function __construct($data, $filename)
    {
        $this->data = $data;
        $this->filename = $filename;
    }

    public function id($id = null)
    {
        //
    }

    public function blueprint()
    {
        return Blueprint::find('product');
    }

    public function editUrl()
    {
        //
    }

    public function updateUrl()
    {
        //
    }

    public function path()
    {
        return base_path().'/content/commerce/products/'.$this->filename.'.yaml';
    }

    public function fileData()
    {
        return $this->data;
    }
}
