<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Fieldtypes\Helpers;

use DuncanMcClean\SimpleCommerce\Fieldtypes\MoneyFieldtype;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Statamic\Facades\Collection;
use Statamic\Fields\Field;

class MoneyFieldtypeWithMockedField extends MoneyFieldtype
{
    use SetupCollections;

    public function field(): ?Field
    {
        $this->setupProducts();

        $products = Collection::findByHandle('products');

        return (new Field('price', [
            'read_only' => false,
        ]))->setParent($products)->setValue(1599);
    }
}
