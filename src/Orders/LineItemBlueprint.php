<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Statamic\Facades\Blueprint as BlueprintFacade;
use Statamic\Fields\Blueprint as StatamicBlueprint;

class LineItemBlueprint
{
    public function __invoke(): StatamicBlueprint
    {
        return BlueprintFacade::makeFromFields([
            'product' => ['type' => 'entries', 'max_items' => 1, 'collections' => config('simple-commerce.products.collections')],
            'variant' => ['type' => 'text'],
            'quantity' => ['type' => 'integer'],
            'unit_price' => ['type' => 'money', 'save_zero_value' => true],
            'total' => ['type' => 'money', 'save_zero_value' => true],
        ])->setHandle('line_item');
    }
}
