<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'iso', 'uid', 'symbol', 'name',
    ];

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
