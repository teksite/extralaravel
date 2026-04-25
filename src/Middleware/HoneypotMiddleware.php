<?php

namespace Teksite\Extralaravel\Middleware;

use Closure;
use Illuminate\Http\Request;

class HoneypotMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        $honeyField=config('extralaravel.honeypot.field_name' ?? 'honeypot');
        if($request->input($honeyField)) abort(403);

        return $next($request);
    }


}
