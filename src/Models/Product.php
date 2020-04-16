<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Events\ProductUpdated;
use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasAttributes;
use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Statamic\Facades\Blueprint;

class Product extends Model
{
    use HasAttributes, HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid', 'title', 'slug', 'product_category_id', 'is_enabled', 'description', 'tax_rate_id',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    protected $appends = [
        'variant_count',
    ];

    protected $dates = [
        'deleted_at',
    ];

    protected $dispatchesEvents = [
        'updated' => ProductUpdated::class,
    ];

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function taxRate()
    {
        return $this->hasOne(TaxRate::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeDisabled($query)
    {
        return $query->where('is_enabled', false);
    }

    public function getVariantCountAttribute()
    {
        return sprintf('%s %s', $count = $this->variants->count(), Str::plural('variant', $count));
    }

    public function createUrl()
    {
        return cp_route('products.create');
    }

    public function editUrl()
    {
        return cp_route('products.edit', ['product' => $this->uuid]);
    }

    public function updateUrl()
    {
        return cp_route('products.update', ['product' => $this->uuid]);
    }

    public function deleteUrl()
    {
        return cp_route('products.destroy', ['product' => $this->uuid]);
    }

    public function blueprint()
    {
        return Blueprint::find('product');
    }

    public function delete()
    {
        if ($this->attributes()->count() > 0) {
            $this->attributes()->delete();
        }

        if ($this->variants()->count() > 0) {
            $this->variants()->delete();
        }

        parent::delete();
    }
}
