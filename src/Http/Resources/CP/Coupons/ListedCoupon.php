<?php

namespace DuncanMcClean\SimpleCommerce\Http\Resources\CP\Coupons;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Facades\User;

class ListedCoupon extends JsonResource
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
        $coupon = $this->resource;

        return [
            'id' => $coupon->id(),
            'code' => $coupon->code(),
            'type' => $coupon->type()->value,
            'amount' => $coupon->amount(),
            'discount_text' => $coupon->discountText(),

            $this->merge($this->values()),

            'edit_url' => $coupon->editUrl(),
            'editable' => User::current()->can('edit coupons'),
            'viewable' => User::current()->can('view coupons'),
            'actions' => Action::for($coupon),
        ];
    }

    protected function values($extra = [])
    {
        return $this->columns->mapWithKeys(function ($column) use ($extra) {
            $key = $column->field;
            $field = $this->blueprint->field($key);

            $value = $extra[$key] ?? $this->resource->get($key);

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
}
