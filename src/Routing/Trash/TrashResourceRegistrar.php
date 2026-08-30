<?php

namespace Teksite\Extralaravel\Routing\Trash;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Teksite\Extralaravel\Middleware\TrashOnlyBindingMiddleware;

final class TrashResourceRegistrar
{
    public function register(string $name, string $controller, TrashResourceOptions $options): void
    {
        $segments = $this->parseResourceSegments($name);
        $resourceName = implode('.', $segments);
        $actions = $this->resolveActions($options);

        if ($actions === []) return;

        Route::group(
            $this->groupAttributes($options),
            function () use ($resourceName, $segments, $controller, $options, $actions): void {
                foreach ($actions as $actionName => $action) {
                    $this->registerRoute($resourceName, $segments, $controller, $options, $actionName, $action);
                }
            },
        );
    }

    private function resolveActions(TrashResourceOptions $options): array
    {
        $actions = TrashResourceDefinition::actions();
        if ($options->only !== null) $actions = array_intersect_key($actions, array_flip($options->only));
        if ($options->except !== []) $actions = array_diff_key($actions, array_flip($options->except));

        return $actions;
    }

    private function registerRoute(string $name, array $segments, string $controller, TrashResourceOptions $options, string $actionName, array $action): void
    {
        $uri = $this->buildUri($segments, $action, $options);

        $route = Route::match([$action['method']], $uri, [$controller, $action['action']]);

        $route->name($this->buildRouteName($name, $actionName, $options));

        if ($options->where !== []) $route->where($options->where);

        if ($options->bindingFields !== []) $route->setBindingFields($options->bindingFields);

        $route->withTrashed();

        $route->middleware(TrashOnlyBindingMiddleware::class);

        if ($options->scopeBindings) $route->scopeBindings();
    }

    private function buildUri(array $segments, array $action, TrashResourceOptions $options): string
    {
        $lastSegment = end($segments);

        if ($options->shallow && count($segments) > 1 && $action['binding']) {
            $resourceUri = $this->resourceSegment($lastSegment);
        } else {
            $resourceUri = $this->nestedResourceUri($segments, $options);
        }

        $wildcard = $this->resourceWildcard($lastSegment, $options);
        $actionUri = str_replace('{resource}', '{' . $wildcard . '}', $action['uri']);

        return trim("{$resourceUri}/{$actionUri}", '/');
    }

    private function nestedResourceUri(array $segments, TrashResourceOptions $options): string
    {
        $parts = [];
        $lastIndex = count($segments) - 1;

        foreach ($segments as $index => $segment) {
            $parts[] = $this->resourceSegment($segment);

            if ($index < $lastIndex) $parts[] = '{' . $this->resourceWildcard($segment, $options) . '}';
        }
        return implode('/', $parts);
    }

    private function resourceSegment(string $segment): string
    {
        return trim($segment, '/');
    }

    private function resourceWildcard(string $segment, TrashResourceOptions $options): string
    {
        $parameter = $this->resourceParameter($segment, $options);

        if (isset($options->scoped[$segment])) return "{$parameter}:{$options->scoped[$segment]}";

        return $parameter;
    }

    private function resourceParameter(string $resource, TrashResourceOptions $options): string
    {
        if (isset($options->parameters[$resource])) {
            return $this->normalizeParameter($options->parameters[$resource]);
        }
        return Str::singular(str_replace('-', '_', trim($resource, '/')));
    }

    private function normalizeParameter(string $parameter): string
    {
        $parameter = trim($parameter, '{}');

        if ($parameter === '') throw new InvalidArgumentException('Trash resource parameter cannot be empty.');

        return $parameter;
    }

    private function buildRouteName(string $resourceName, string $actionName, TrashResourceOptions $options): string
    {
        if (isset($options->names[$actionName])) {
            return trim($options->names[$actionName], '.');
        }

        $prefix = $options->namePrefix !== '' ? $options->namePrefix . '.' : '';

        return "{$prefix}{$resourceName}.trash.{$actionName}";
    }

    private function groupAttributes(TrashResourceOptions $options): array
    {
        $attributes = [];

        if ($options->prefix !== '') $attributes['prefix'] = $options->prefix;

        if ($options->middleware !== []) $attributes['middleware'] = $options->middleware;

        return $attributes;
    }

    /**
     * @return array<int, string>
     */
    private function parseResourceSegments(string $name): array
    {
        $segments = array_values(array_filter(
            explode('.', str_replace('/', '.', trim($name, '/.'))),
            static fn(string $segment): bool => $segment !== '',
        ));

        if ($segments === []) throw new InvalidArgumentException('Trash resource name cannot be empty.');

        return $segments;
    }
}
