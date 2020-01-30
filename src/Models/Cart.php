<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Helpers\CartCalculator;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'uid',
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

    public function getTotalAttribute()
    {
        return (new CartCalculator($this))->calculate();
    }
}
