<?php

namespace DuncanMcClean\SimpleCommerce\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource
            ->toAugmentedCollection()
            ->withShallowNesting()
            ->toArray();
    }
}