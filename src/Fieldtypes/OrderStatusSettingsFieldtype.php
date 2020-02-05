<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use Statamic\Facades\Blueprint;
use Statamic\Fields\Fieldtype;

class OrderStatusSettingsFieldtype extends Fieldtype
{
    protected $categories = ['commerce'];
    protected $icon = 'select';

    public function preload()
    {
        $blueprint = Blueprint::find('simple-commerce/order_status');
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return [
            'index' => cp_route('commerce-api.order-status.index'),
            'store' => cp_route('commerce-api.order-status.store'),
            'blueprint' => $blueprint->toPublishArray(),
            'meta' => $fields->meta(),
            'values' => $fields->values(),
        ];
    }

    public function preProcess($data)
    {
        return $data;
    }

    public function process($data)
    {
        return $data;
    }

    public static function title()
    {
        return 'Order Status Settings';
    }

    public function component(): string
    {
        return 'order-status-settings';
    }
}
