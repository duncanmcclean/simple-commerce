<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'color', 'primary', 'uid',
    ];

    protected $casts = [
        'primary' => 'boolean',
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
