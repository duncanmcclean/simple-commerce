<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Blueprint;

class Order extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'billing_address_id', 'shipping_address_id', 'customer_id', 'order_status_id', 'items', 'total', 'currency_id', 'gateway_data', 'is_paid', 'is_refunded',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'is_paid' => 'boolean',
        'items' => 'json',
        'gateway_data' => 'json',
    ];

    public function billingAddress()
    {
        return $this->belongsTo(Address::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class);
    }

    public function customer()
    {
        $model = config('simple-commerce.customers.model');
        return $this->belongsTo(new $model());
    }

    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function editUrl()
    {
        return cp_route('orders.edit', ['order' => $this->attributes['uuid']]);
    }

    public function updateUrl()
    {
        return cp_route('orders.update', ['order' => $this->attributes['uuid']]);
    }

    public function deleteUrl()
    {
        return cp_route('orders.destroy', ['order' => $this->attributes['uuid']]);
    }

    public function blueprint()
    {
        return Blueprint::find('order');
    }
}
