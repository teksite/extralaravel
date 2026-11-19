<?php

namespace Teksite\Extralaravel\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Teksite\Extralaravel\Support\Honeypot;

class HoneypotMiddleware
{

    private const int DEFAULT_STATUS = 403;

    public function handle(Request $request, Closure $next): Response
    {
        $config = (array)config('extralaravel.honeypot', []);

        if (!($config['enabled'] ?? false)) return $next($request);

        if ($request->filled(Honeypot::fieldName())) abort($this->status($config['status'] ?? null));

        return $next($request);
    }

    /**
     * Fall back to 403 when the configured status is not a valid HTTP error code,
     * because abort() would otherwise throw while handling a bot request.
     */
    private function status(mixed $status): int
    {
        return is_int($status) && $status >= 400 && $status <= 599 ? $status : self::DEFAULT_STATUS;
    }
}
