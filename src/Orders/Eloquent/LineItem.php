<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product', 'variant', 'quantity', 'total', 'metadata', 'order_id',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
