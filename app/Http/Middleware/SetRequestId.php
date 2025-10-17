<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SetRequestId
{
    public function handle(Request $request, Closure $next)
    {
        $id = $request->header(config('app.request_id_header', 'X-Request-Id')) ?? Str::uuid()->toString();
        $request->headers->set('X-Request-Id', $id);
        return $next($request);
    }
}
