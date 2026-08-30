<?php

namespace Teksite\Extralaravel\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TrashOnlyBindingMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();

        if ($route === null) return $next($request);

        foreach ($route->parameters() as $parameter) {

            if (!$parameter instanceof Model) continue;

            if (!in_array(SoftDeletes::class, class_uses_recursive($parameter), true)) {
                throw (new NotFoundHttpException());
            }
            if (!$parameter->trashed()) throw (new NotFoundHttpException());
        }

        return $next($request);
    }
}
