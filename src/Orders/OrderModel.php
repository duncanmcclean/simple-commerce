<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Customers\CustomerModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderModel extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $guarded = [];

    protected $casts = [
        'items' => 'json',
        'grand_total' => 'integer',
        'items_total' => 'integer',
        'tax_total' => 'integer',
        'shipping_total' => 'integer',
        'coupon_total' => 'integer',
        'use_shipping_address_for_billing' => 'boolean',
        'gateway' => 'json',
        'data' => 'json',
    ];

    protected $appends = [
        'order_number',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class);
    }

    public function getOrderNumberAttribute()
    {
        if (array_key_exists('title', $this->data ?? [])) {
            return $this->data['title'];
        }

        return "#{$this->id}";
    }
}
