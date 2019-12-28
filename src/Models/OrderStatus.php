<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'color', 'primary',
    ];

    protected $casts = [
        'primary' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
