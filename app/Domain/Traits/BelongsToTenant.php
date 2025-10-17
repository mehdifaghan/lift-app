<?php

namespace App\Domain\Traits;

use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::creating(function (Model $model) {
            if (app()->has('tenant.id') && empty($model->tenant_id)) {
                $model->tenant_id = app('tenant.id');
            }
        });
    }
}
