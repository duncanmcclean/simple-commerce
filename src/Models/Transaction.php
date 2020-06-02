<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'uuid', 'gateway', 'amount', 'is_complete', 'is_refunded', 'gateway_data', 'order_id', 'customer_id', 'currency_id',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
        'is_refunded' => 'boolean',
        'gateway_data' => 'json',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        $model = config('simple-commerce.customers.model');

        return $this->belongsTo(new $model());
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
