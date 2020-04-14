<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ShippingCategory extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'name', 'description', 'primary',
    ];

    protected $casts = [
        'primary' => 'boolean',
    ];

    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }
}
