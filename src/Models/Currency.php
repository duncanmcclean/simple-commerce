<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'name', 'iso', 'symbol',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
