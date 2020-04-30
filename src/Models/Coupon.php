<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Carbon\Carbon;
use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Statamic\Facades\Blueprint;

class Coupon extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'name', 'code', 'type', 'value', 'minimum_total', 'total_uses', 'start_date', 'end_date',
    ];

    protected $appends = [
        'affect',
    ];

    protected $casts = [
        'start_date'    => 'datetime:Y-m-d',
        'end_date'      => 'datetime:Y-m-d',
    ];

    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }

    public function createUrl()
    {
        return cp_route('coupons.create');
    }

    public function editUrl()
    {
        return cp_route('coupons.edit', ['coupon' => $this->uuid]);
    }

    public function updateUrl()
    {
        return cp_route('coupons.update', ['coupon' => $this->uuid]);
    }

    public function deleteUrl()
    {
        return cp_route('coupons.destroy', ['coupon' => $this->uuid]);
    }

    public function blueprint()
    {
        return Blueprint::find('coupon');
    }

    public function getAffectAttribute()
    {
        switch ($this->type) {
            case 'percent_discount':
                return "{$this->value}% Off";
            case 'fixed_discount':
                $amount = Currency::parse($this->value);
                return "{$amount} Off";
            case 'free_shipping':
                return 'Free Shipping';        
        }
    }

    public function isActive()
    {
        // If the coupon has maxed out the uses
        if ($this->uses() >= $this->total_uses) {
            return false;
        }

        // If there are no dates set...
        if ($this->start_date === null && $this->end_date === null) {
            return true;
        }

        // TODO: deal with the state here
        
        return false;
    }

    public function uses()
    {
        $couponId = $this->id;

        $count = Order::completed()
            ->get()
            ->reject(function ($order) use ($couponId) {
                if ($order->lineItems->where('coupon_id', $couponId)->count() > 0) {
                    return false;
                }

                return true;
            })
            ->count(); 

        return sprintf('%s %s', $count = $count, Str::plural('use', $count));
    }
}