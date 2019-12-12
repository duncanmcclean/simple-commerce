<?php

namespace Damcclean\Commerce\Http\Requests;

use Damcclean\Commerce\Facades\Order;
use Illuminate\Foundation\Http\FormRequest;

class OrderUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return Order::updateRules([]);
    }
}
