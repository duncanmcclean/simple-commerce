<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Eloquent;

use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderModel extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function casts(): array
    {
        return [
            'order_number' => 'integer',
            'date' => 'datetime',
            'status' => OrderStatus::class,
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

        $this->setTable(config('statamic.simple-commerce.orders.table', 'orders'));
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(app('simple-commerce.orders.eloquent.line_items_model'), 'order_id');
    }
}
