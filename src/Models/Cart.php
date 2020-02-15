<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Helpers\CartCalculator;
use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid',
    ];

    protected $appends = [
        'total',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function shipping()
    {
        return $this->hasOne(CartShipping::class);
    }

    public function tax()
    {
        return $this->hasOne(CartTax::class);
    }

    public function getTotalAttribute()
    {
        return (new CartCalculator($this))->calculate();
    }
}
