<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Data\OrderData;
use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Blueprint;

class Order extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'gateway', 'is_paid', 'is_completed', 'total', 'item_total', 'tax_total', 'shipping_total', 'coupon_total', 'currency_id', 'order_status_id', 'billing_address_id', 'shipping_address_id', 'customer_id',
    ];

    protected $casts = [
        'is_paid'       => 'boolean',
        'is_completed'  => 'boolean',
    ];

    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }

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

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeNotCompleted($query)
    {
        return $query->where('is_completed', false);
    }

    public function recalculate()
    {
        return \DoubleThreeDigital\SimpleCommerce\Facades\Cart::calculateTotals($this);
    }

    public function templatePrep()
    {
        return (new OrderData)->data($this->toArray(), $this);
    }
}
