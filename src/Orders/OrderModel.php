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

    protected $fillable = [
        'is_paid', 'is_shipped', 'is_refunded', 'items', 'grand_total', 'items_total', 'tax_total',
        'shipping_total', 'coupon_total', 'shipping_name', 'shipping_address', 'shipping_address_line2',
        'shipping_city', 'shipping_postal_code', 'shipping_region', 'shipping_country', 'billing_name',
        'billing_address', 'billing_address_line2', 'billing_city', 'billing_postal_code', 'billing_region',
        'billing_country', 'use_shipping_address_for_billing', 'customer_id', 'coupon', 'gateway', 'data',
        'paid_date',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_shipped' => 'boolean',
        'is_refunded' => 'boolean',
        'items' => 'json',
        'grand_total' => 'integer',
        'items_total' => 'integer',
        'tax_total' => 'integer',
        'shipping_total' => 'integer',
        'coupon_total' => 'integer',
        'use_shipping_address_for_billing' => 'boolean',
        'gateway' => 'json',
        'data' => 'json',
        'paid_date' => 'datetime',
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
        if (array_key_exists('title', $this->data)) {
            return $this->data['title'];
        }

        return "#{$this->id}";
    }
}
