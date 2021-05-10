<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Requests\Cart;

use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class UpdateRequest extends FormRequest
{
    use CartDriver;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return Arr::except($this->getCart()->rules(), [
            'title',
            'items',
            'slug',
            'paid_date',
            'items_total',
            'coupon_total',
            'tax_total',
            'shipping_total',
            'grand_total',
        ]);
    }
}
