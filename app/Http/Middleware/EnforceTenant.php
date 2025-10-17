<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceTenant
{
    public function handle(Request $request, Closure $next)
    {
        if (config('tenancy.enforce') && Auth::check()) {
            $tenantId = Auth::user()->tenant_id;
            app()->instance('tenant.id', $tenantId);
        }
        return $next($request);
    }
}
