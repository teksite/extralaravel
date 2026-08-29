<?php

namespace Teksite\Extralaravel\Middleware;

use Closure;
use Illuminate\Http\Request;

class HoneypotMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        $config = config('extralaravel.honeypot');
        if (!isset($config['enabled']) || $config['enabled'] === false) return $next($request);

        $field = $config['field_name'] ?? 'honeypot';
        $status = $config('status') ?? 403;

        if ($request->filled($field)) abort($status);

        return $next($request);
    }


}
