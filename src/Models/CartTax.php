<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CartTax extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'tax_rate_id', 'cart_id',
    ];

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}
