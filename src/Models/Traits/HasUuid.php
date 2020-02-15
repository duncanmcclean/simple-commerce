<?php

namespace DoubleThreeDigital\SimpleCommerce\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Statamic\Stache\Stache;

trait HasUuid
{
    public static function bootHasUuid()
    {
        static::creating(function (Model $model) {
            $model->uuid = (new Stache)->generateId();
        });

        static::saving(function (Model $model) {
            if (empty($model->uuid)) {
                $model->uuid = (new Stache)->generateId();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
