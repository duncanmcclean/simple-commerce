<?php

namespace DoubleThreeDigital\SimpleCommerce\Events;

use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class ProductCategoryUpdated
{
    use Dispatchable;
    use InteractsWithSockets;

    public $category;

    /**
     * ProductCategoryUpdated constructor.
     *
     * @param ProductCategory $category
     */
    public function __construct(ProductCategory $category)
    {
        $this->category = $category;
    }
}
