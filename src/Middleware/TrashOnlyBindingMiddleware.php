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

        $parameters = $route->parameters();

        if ($parameters === []) return $next($request);

        $target = end($parameters);

        if (!$target instanceof Model) return $next($request);

        if (!in_array(SoftDeletes::class, class_uses_recursive($target), true)) throw new NotFoundHttpException();

        if (!$target->trashed()) throw new NotFoundHttpException();


        return $next($request);
    }
}
