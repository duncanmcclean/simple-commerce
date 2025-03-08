<?php

namespace DuncanMcClean\SimpleCommerce\Cart\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CartModel extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function casts(): array
    {
        return [
            'grand_total' => 'integer',
            'sub_total' => 'integer',
            'discount_total' => 'integer',
            'tax_total' => 'integer',
            'shipping_total' => 'integer',
            'data' => 'json',
        ];
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('statamic.simple-commerce.carts.table', 'carts'));
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(app('simple-commerce.carts.eloquent.line_items_model'), 'cart_id');
    }
}