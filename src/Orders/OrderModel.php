<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Customers\CustomerModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StatamicRadPack\Runway\Traits\HasRunwayResource;

class OrderModel extends Model
{
    use HasFactory, HasRunwayResource;

    protected $table = 'orders';

    protected $guarded = [];

    public function casts(): array
    {
        return [
            'order_number' => 'integer',
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
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class);
    }

    public function statusLog(): HasMany
    {
        return $this->hasMany(StatusLogModel::class, 'order_id');
    }

    public function orderDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->statusLog
                    ->where('status', OrderStatus::Placed)
                    ->map->date()
                    ->last();
            },
        );
    }
}
