<?php

namespace Teksite\Extralaravel\Routing\Trash;

use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Teksite\Extralaravel\Middleware\TrashOnlyBindingMiddleware;

final class TrashResourceRegistrar
{
    /**
     * Register a trash resource.
     *
     * Examples:
     *
     * Route::trashResource('posts', PostTrashController::class);
     *
     * Route::trashResource(
     *     'posts.comments',
     *     CommentTrashController::class,
     *     ['scopeBindings' => true]
     * );
     */
    public function register(string $name, string $controller, TrashResourceOptions $options): void
    {
        $name = $this->normalizeResourceName($name);

        $segments = $this->parseResourceSegments($name);
        $actions = $this->resolveActions($options);

        if ($actions === []) return;

        Route::group($this->buildGroupAttributes($options),
            function () use ($name, $segments, $controller, $options, $actions): void {
                foreach ($actions as $actionName => $action) {
                    $this->registerRoute($name, $segments, $controller, $options, $actionName, $action);
                }
            }
        );
    }

    /**
     * @return array<string, array{
     *     method: string,
     *     uri: string,
     *     action: string,
     *     binding: bool
     * }>
     */
    private function resolveActions(TrashResourceOptions $options): array
    {
        $actions = TrashResourceDefinition::actions();

        if ($options->only !== null) $actions = array_intersect_key($actions, array_flip($options->only));


        if ($options->except !== []) $actions = array_diff_key($actions, array_flip($options->except));

        return $actions;
    }


    private function registerRoute(string $name, array $segments, string $controller, TrashResourceOptions $options, string $actionName, array $action): void
    {
        $uri = $this->buildUri($segments, $action['uri'], $actionName, $options);

        $route = Route::match([$action['method']], $uri, [$controller, $action['action']]);

        $route->name($this->buildRouteName($name, $actionName, $options));

        if ($options->where !== []) $route->where($options->where);


        if ($options->bindingFields !== []) $this->applyBindingFields($route, $options->bindingFields);

        $route->withTrashed();

        $route->middleware(TrashOnlyBindingMiddleware::class);

        if ($options->scopeBindings) $route->scopeBindings();


        if ($options->shallow && count($segments) > 1) {
            $this->applyShallowBinding($route, $segments, $actionName);
        }
    }


    private function buildUri(array $segments, string $actionUri, string $actionName, TrashResourceOptions $options): string
    {
        $resourceUri = $this->buildNestedResourceUri($segments, $options);

        $resourceParameter = $this->resourceParameter($segments[array_key_last($segments)], $options);

        $actionUri = str_replace('{resource}', '{' . $resourceParameter . '}', $actionUri);

        if ($options->shallow
            && count($segments) > 1
            && in_array($actionName, [TrashAction::REINSTATE->value, TrashAction::PRUNE->value,], true)) {
            $resourceUri = $this->resourceSegment($segments[array_key_last($segments)], $options);
        }

        return trim("{$resourceUri}/{$actionUri}", '/');
    }

    /**
     * @param array<int, string> $segments
     */
    private function buildNestedResourceUri(array $segments, TrashResourceOptions $options): string
    {
        $uri = [];
        foreach ($segments as $index => $segment) {
            $uri[] = $this->resourceSegment($segment, $options);

            if ($index < count($segments) - 1) {
                $uri[] = '{' . $this->resourceParameter(
                        $segment,
                        $options
                    ) . '}';
            }
        }

        return implode('/', $uri);
    }

    private function resourceSegment(string $segment, TrashResourceOptions $options): string
    {
        return trim($segment, '/');
    }

    private function resourceParameter(string $resource, TrashResourceOptions $options): string
    {
        if (isset($options->parameters[$resource])) return $this->normalizeParameter($options->parameters[$resource]);

        $resource = trim($resource, '/');

        return $this->singularizeLastSegment($resource);
    }

    private function normalizeParameter(string $parameter): string
    {
        $parameter = trim($parameter, '{}');

        if ($parameter === '') throw new InvalidArgumentException('Trash resource parameter cannot be empty.');

        return $parameter;
    }

    private function singularizeLastSegment(string $value): string
    {
        $value = trim($value, '/');

        $segments = explode('.', $value);
        $value = end($segments);

        $value = str_replace('_', '-', $value);

        return \Illuminate\Support\Str::singular($value);
    }

    private function buildRouteName(string $resourceName, string $actionName, TrashResourceOptions $options): string
    {
        if (isset($options->names[$actionName])) return trim($options->names[$actionName], '.');


        $resourceName = str_replace('/', '.', trim($resourceName, '.'));

        $prefix = $options->namePrefix !== ''
            ? $options->namePrefix . '.'
            : '';

        return "{$prefix}{$resourceName}.trash.{$actionName}";
    }

    private function buildGroupAttributes(TrashResourceOptions $options): array
    {
        $attributes = [];

        if ($options->prefix !== '') $attributes['prefix'] = $options->prefix;

        if ($options->middleware !== []) $attributes['middleware'] = $options->middleware;

        return $attributes;
    }

    private function normalizeResourceName(string $name): string
    {
        $name = trim($name, '. /');

        if ($name === '') throw new InvalidArgumentException('Trash resource name cannot be empty.');

        $segments = $this->parseResourceSegments($name);

        foreach ($segments as $segment) {
            if ($segment === '') throw new InvalidArgumentException("Invalid trash resource name [{$name}].");
        }

        return implode('.', $segments);
    }

    private function parseResourceSegments(string $name): array
    {
        $name = str_replace('/', '.', trim($name, '/.'));

        $segments = array_values(
            array_filter(
                explode('.', $name),
                static fn(string $segment): bool => $segment !== ''
            )
        );

        if ($segments === []) throw new InvalidArgumentException('Trash resource name cannot be empty.');

        return $segments;
    }

    /**
     * Apply route binding fields.
     *
     * Example:
     *
     * ['comment' => 'slug']
     *
     * results in:
     *
     * {comment:slug}
     */
    private function applyBindingFields(Route $route, array $bindingFields): void
    {
        $route->setBindingFields($bindingFields);
    }

    /**
     * Placeholder hook for shallow nested binding.
     *
     * Laravel handles the actual implicit model resolution.
     * The route URI itself determines which parent parameters exist.
     */
    private function applyShallowBinding(Route  $route, array  $segments, string $actionName): void
    {
        if (!in_array(
            $actionName,
            [
                TrashAction::REINSTATE->value,
                TrashAction::PRUNE->value,
            ],
            true
        )) {
            return;
        }

        $route->withoutScopedBindings();
    }
}
