<?php

namespace DuncanMcClean\SimpleCommerce\Orders\Eloquent;

use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $primaryKey = 'uuid';

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
            'line_items' => 'json',
            'data' => 'json',
        ];
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('statamic.simple-commerce.orders.table', 'orders'));
    }
}
