<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'uid',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
}
