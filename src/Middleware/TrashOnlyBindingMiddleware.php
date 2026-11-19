<?php

namespace Teksite\Extralaravel\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Restricts a route that was bound with withTrashed() to trashed records only.
 *
 * Route::withTrashed() applies to every soft-deletable binding of the route, including parents of
 * nested resources. This middleware therefore enforces two rules:
 *
 *  - the target parameter must be a soft-deleted model;
 *  - every other soft-deletable model parameter (the parents) must not be soft-deleted.
 *
 * The target is the parameter named by the middleware argument ("trash.only:post"), or the last
 * route parameter when no argument is given.
 *
 * Model binding must already have run (SubstituteBindings middleware). Parameters that are still
 * raw scalars are ignored here and are expected to be resolved by the controller.
 */
class TrashOnlyBindingMiddleware
{
    public function handle(Request $request, Closure $next, ?string $parameter = null): Response
    {
        $route = $request->route();

        if (!$route instanceof \Illuminate\Routing\Route) return $next($request);

        $parameters = $route->parameters();

        if ($parameters === []) return $next($request);

        $targetName = $parameter ?? array_key_last($parameters);

        foreach ($parameters as $name => $value) {
            if (!$value instanceof Model) continue;

            $isTarget = $name === $targetName;

            if ($isTarget !== $this->isTrashed($value)) throw new NotFoundHttpException();
        }

        return $next($request);
    }

    private function isTrashed(Model $model): bool
    {
        return method_exists($model, 'trashed') && $model->trashed();
    }
}
