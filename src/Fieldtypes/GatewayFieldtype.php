<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Actions\RefundAction;
use DoubleThreeDigital\SimpleCommerce\Facades\Gateway;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Action;
use Statamic\Fields\Fieldtype;

class GatewayFieldtype extends Fieldtype
{
    public static function title()
    {
        return 'Gateway';
    }

    public function preload()
    {
        return [
            'gateways' => SimpleCommerce::gateways(),
        ];
    }

    public function preProcess($value)
    {
        if (! $value) {
            return null;
        }

        $gateway = collect(SimpleCommerce::gateways())
            ->where('class', isset($value['use']) ? $value['use'] : $value)
            ->first();

        if (! $gateway) {
            return null;
        }

        $actions = Action::for($this->field->parent())
            ->filter(function ($action) {
                return in_array(get_class($action), [
                    RefundAction::class,
                ]);
            })
            ->values();

        return [
            'data' => $value,
            'entry' => optional($this->field->parent())->id(),

            'gateway_class' => $gateway['class'],
            'payment_display' => Gateway::use($gateway['class'])->paymentDisplay($value),

            'actions' => $actions,
            'action_url' => cp_route(
                'collections.entries.actions.run',
                $this->field->parent()->collection->handle()
            ),
        ];
    }

    public function process($value)
    {
        if (isset($value['data'])) {
            return $value['data'];
        }

        return $value;
    }

    public function augment($value)
    {
        $gateway = collect(SimpleCommerce::gateways())
            ->where('class', isset($value['use']) ? $value['use'] : $value)
            ->first();

        if (! $gateway) {
            return null;
        }

        return array_merge($gateway, [
            'data' => array_pull($value, 'data', []),
        ]);
    }

    public function preProcessIndex($value)
    {
        if (! $value) {
            return;
        }

        $gateway = collect(SimpleCommerce::gateways())
            ->where('class', isset($value['use']) ? $value['use'] : $value)
            ->first();

        if (! $gateway) {
            return null;
        }

        return $gateway['name'];
    }
}
