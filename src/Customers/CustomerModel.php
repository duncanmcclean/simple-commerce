<?php

namespace DoubleThreeDigital\SimpleCommerce\Customers;

use DoubleThreeDigital\SimpleCommerce\Orders\OrderModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerModel extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'email', 'data',
    ];

    protected $casts = [
        'data' => 'json',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(OrderModel::class);
    }
}
