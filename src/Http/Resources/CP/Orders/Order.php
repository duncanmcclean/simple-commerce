<?php

namespace DuncanMcClean\SimpleCommerce\Http\Resources\CP\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Order extends JsonResource
{
    public function toArray(Request $request)
    {
        $data = [
            'order_number' => $this->orderNumber(),
        ];

        return ['data' => $data];
    }
}