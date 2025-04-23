<?php

namespace Teksite\Extralaravel\Middleware;

use Closure;
use Illuminate\Http\Request;

class HoneypotMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        if ($request->has('data_info.fullname') && !empty($request->input('data_info.fullname'))) {
            // Block the request if the honeypot field is not empty.
            return response('Forbidden', 403);
        }

        return $next($request);
    }


}
