<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'color', 'primary', 'uid',
    ];

    protected $casts = [
        'primary' => 'boolean',
    ];

    protected $appends = [
        'updateUrl', 'deleteUrl',
    ];

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getUpdateUrlAttribute()
    {
        return cp_route('commerce-api.order-status.update', ['status' => $this->attributes['uid']]);
    }

    public function getDeleteUrlAttribute()
    {
        return cp_route('commerce-api.order-status.destroy', ['status' => $this->attributes['uid']]);
    }
}
