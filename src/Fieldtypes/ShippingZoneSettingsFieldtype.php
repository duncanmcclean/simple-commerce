<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use Statamic\Facades\Blueprint;
use Statamic\Fields\Fieldtype;

class ShippingZoneSettingsFieldtype extends Fieldtype
{
    protected $categories = ['commerce'];
    protected $icon = 'select';

    public function preload()
    {
        $blueprint = Blueprint::find('simple-commerce/shipping_zone');
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return [
            'index' => cp_route('commerce-api.shipping-zones.index'),
            'store' => cp_route('commerce-api.shipping-zones.store'),
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
        return 'Shipping Zone Settings';
    }

    public function component(): string
    {
        return 'shipping-zone-settings';
    }
}
