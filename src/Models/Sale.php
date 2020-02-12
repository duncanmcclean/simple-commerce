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

    protected $appends = [
        'create_url', 'edit_url', 'delete_url',
    ];

    public function createUrl()
    {
        return cp_route('sales.create');
    }

    public function editUrl()
    {
        return cp_route('sales.edit', ['sale' => $this->uuid]);
    }

    public function deleteUrl()
    {
        return cp_route('sales.destroy', ['sale' => $this->uuid]);
    }
}
