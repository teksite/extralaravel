<?php

namespace Teksite\Extralaravel\Middleware;

use Closure;
use Illuminate\Http\Request;

class HoneypotMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        $field = config('extralaravel.honeypot.field_name', 'honeypot');

        if ($request->filled($field)) abort(403);

        return $next($request);
    }


}
