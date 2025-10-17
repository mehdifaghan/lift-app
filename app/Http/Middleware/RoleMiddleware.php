<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();
        if (!$user) abort(401);
        if ($user->system_role === 'SYSTEM_ADMIN') return $next($request);
        if (!in_array($user->role, $roles)) abort(403, 'Forbidden');
        return $next($request);
    }
}
