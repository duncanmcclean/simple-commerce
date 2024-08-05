<?php

namespace DuncanMcClean\SimpleCommerce\Http\Resources\CP\Orders;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Statamic\Facades\Action;
use Statamic\Facades\User;
use Statamic\Fields\Field;

class ListedOrder extends JsonResource
{
    protected $blueprint;

    protected $columns;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function columns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function toArray($request)
    {
        $order = $this->resource;

        return [
            'id' => $order->orderNumber(),
            'order_number' => $order->orderNumber(),

            $this->merge($this->values()),

            'edit_url' => $order->editUrl(),
            'viewable' => User::current()->can('view', $order),
            'editable' => User::current()->can('edit', $order),
            'actions' => Action::for($order),
        ];
    }

    protected function values($extra = [])
    {
        return $this->columns->mapWithKeys(function ($column) {
            $key = $column->field;
            $field = $this->blueprint->field($key);

            $value = $this->value($field, $key);

            if (! $field) {
                return [$key => $value];
            }

            $value = $field->setValue($value)
                ->setParent($this->resource)
                ->preProcessIndex()
                ->value();

            return [$key => $value];
        });
    }

    private function value(Field $field, string $key)
    {
        $method = Str::camel($key);

        // todo: we can probably figure out something else clever, but to get totals etc, that are properties, this seems fine for now.
        if (method_exists($this->resource, $method)) {
            return $this->resource->$method();
        }

        return $extra[$key] ?? $this->resource->get($key) ?? $field?->defaultValue();
    }
}
