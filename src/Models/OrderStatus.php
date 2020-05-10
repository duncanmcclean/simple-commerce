<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Blueprint;

class OrderStatus extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'name', 'slug', 'description', 'color', 'primary',
    ];

    protected $casts = [
        'primary' => 'boolean',
    ];

    protected $appends = [
        'updateUrl', 'deleteUrl',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getUpdateUrlAttribute()
    {
        return cp_route('order-status.update', ['status' => $this->attributes['uuid']]);
    }

    public function getDeleteUrlAttribute()
    {
        return cp_route('order-status.destroy', ['status' => $this->attributes['uuid']]);
    }

    public function blueprint()
    {
        return Blueprint::setDirectory(__DIR__.'/../../resources/blueprints')
            ->find('order_status');
    }
}
