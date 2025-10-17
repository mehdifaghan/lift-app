<?php

namespace App\Domain;

use App\Domain\Traits\BelongsToTenant;
use App\Domain\Traits\TenantScope;
use Illuminate\Database\Eloquent\Model;

abstract class TenantModel extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }
}
