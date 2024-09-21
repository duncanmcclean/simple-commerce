<?php

namespace DuncanMcClean\SimpleCommerce\Http\Resources\CP\Coupons;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Coupon extends JsonResource
{
    public function toArray(Request $request)
    {
        $data = [
            'id' => $this->resource->id(),
            'reference' => $this->resource->reference(),
            'title' => $this->resource->code(),
            'code' => $this->resource->code(),
            'type' => $this->resource->type(),
            'amount' => $this->resource->amount(),
            'edit_url' => $this->resource->editUrl(),
            'redeemed_count' => $this->resource->redeemedCount(),
        ];

        return ['data' => $data];
    }
}