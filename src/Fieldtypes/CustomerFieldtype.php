<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use Statamic\CP\Column;
use Statamic\Fieldtypes\Relationship;

class CustomerFieldtype extends Relationship
{
    protected $categories = ['commerce'];
    protected $canCreate = false;
    protected $canEdit = false;
    protected $taggable = false;
    protected $icon = 'user';

    protected function toItemArray($id)
    {
        return Customer::find($id);
    }

    public function getIndexItems($request)
    {
        return Customer::all();
    }

    public function getColumns()
    {
        return [
            Column::make('name'),
            Column::make('email'),
        ];
    }

    public static function title()
    {
        return 'Customer';
    }
}
