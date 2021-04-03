<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Eloquent;

use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderFacade;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'is_paid', 'grand_total', 'items_total', 'tax_total', 'shipping_total', 'coupon_total',
        'billing_name', 'billing_address', 'billing_city', 'billing_postal_code', 'billing_country',
        'shipping_name', 'shipping_address', 'shipping_city', 'shipping_postal_code', 'shipping_country',
        'gateway', 'stripe', 'gateway_data', 'coupon_id', 'user_id', 'paid_at',
    ];

    protected $casts = [
        'paid_at'      => 'datetime',
        'stripe'       => 'json',
        'gateway_data' => 'json',
        'paid_at'      => 'datetime',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public static function booted()
    {
        static::created(function ($model) {
            $model->update([
                // 'title' => GenerateOrderNumber::generateOrderNumber($model->id),
            ]);
        });
    }

    public function asSimpleCommerceOrder()
    {
        return OrderFacade::find($this->id);
    }
}
