<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Actions\RefundAction;
use DuncanMcClean\SimpleCommerce\Facades\Gateway;
use DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Support\Runway;
use Statamic\Facades\Action;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;

class GatewayFieldtype extends Fieldtype
{
    public static function title()
    {
        return __('Payment Gateway');
    }

    public function preload()
    {
        return [
            'gateways' => SimpleCommerce::gateways()->toArray(),
        ];
    }

    public function preProcess($value)
    {
        if (! $value) {
            return null;
        }

        $actionUrl = null;

        $gateway = SimpleCommerce::gateways()->firstWhere('handle', $value['use']);

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

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            $actionUrl = cp_route(
                'collections.entries.actions.run',
                $this->field->parent()->collection->handle()
            );
        }

        if (isset(SimpleCommerce::orderDriver()['model'])) {
            $orderModel = SimpleCommerce::orderDriver()['model'];

            $actionUrl = cp_route('runway.actions.run', [
                'resource' => Runway::orderModel()->handle(),
            ]);
        }

        return [
            'data' => $value,
            'entry' => optional($this->field->parent())->id,

            'gateway_class' => $gateway['class'],
            'display' => Gateway::use($gateway['handle'])->fieldtypeDisplay($value),

            'actions' => $actions,
            'action_url' => $actionUrl,
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
        if (! $value) {
            return null;
        }

        $gateway = SimpleCommerce::gateways()->firstWhere('handle', $value['use']);

        if (! $gateway) {
            return null;
        }

        return array_merge($gateway, [
            'data' => Arr::pull($value, 'data', []),
        ]);
    }

    public function preProcessIndex($value)
    {
        if (! $value) {
            return;
        }

        $gateway = SimpleCommerce::gateways()->firstWhere('handle', $value['use']);

        if (! $gateway) {
            return null;
        }

        return $gateway['name'];
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
