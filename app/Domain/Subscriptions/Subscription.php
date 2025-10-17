<?php

namespace App\Domain\Subscriptions;

use Illuminate\Database\Eloquent\Model;
use App\Domain\Traits\TenantScope;
use App\Domain\Traits\BelongsToTenant;

class Subscription extends Model
{
    protected $guarded = [];
    \n    use \\App\\Domain\\Traits\\BelongsToTenant;\n    protected static function booted(){ static::addGlobalScope(new \\App\\Domain\\Traits\\TenantScope); }
    
}
