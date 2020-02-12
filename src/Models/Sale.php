<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'uuid', 'name', 'description', 'is_enabled', 'start_date', 'end_date', 'affect',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    protected $dates = [
        'start_date',
        'end_date',
    ];
}
