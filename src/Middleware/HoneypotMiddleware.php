<?php

namespace Teksite\Extralaravel\Middleware;

use Closure;
use Illuminate\Http\Request;

class HoneypotMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        $honeyField=config('extralaravel.honeypot.field_name' ?? 'data_info.fullname');
        if ($request->has($honeyField) && !empty($request->input($honeyField))) {
            // Block the request if the honeypot field is not empty.
            return response('Forbidden', 403);
        }

        return $next($request);
    }


}
