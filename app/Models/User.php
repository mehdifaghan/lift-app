<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Domain\Traits\TenantScope;
use App\Domain\Traits\BelongsToTenant;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, BelongsToTenant;

    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }
}
