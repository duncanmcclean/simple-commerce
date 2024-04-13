<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Customers\CustomerModel;
use DuncanMcClean\SimpleCommerce\Customers\EloquentCustomerRepository;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Contracts\Database\Eloquent\Builder;
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

    protected $casts = [
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

    public function scopeRunwaySearch(Builder $query, string $searchQuery)
    {
        return $query
            ->where('order_number', 'like', "%$searchQuery%")
            ->orWhere('grand_total', 'like', "%" . str_replace('.', '', $searchQuery) . "%")
            ->orWhere('items_total', 'like', "%" . str_replace('.', '', $searchQuery) . "%")
            ->when($this->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], EloquentCustomerRepository::class), function ($query) use ($searchQuery) {
                $query->orWhereHas('customer', function ($query) use ($searchQuery) {
                    $query->where('name', 'like', "%$searchQuery%")
                        ->orWhere('email', 'like', "%$searchQuery%");
                });
            });
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
