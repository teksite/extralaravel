<?php

namespace Teksite\Extralaravel\Routing\Trash;

use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

final class TrashResourceRegistrar
{
    /**
     * Register a trash resource.
     */
    public function register(string $name, string $controller, TrashResourceOptions $options): void
    {
        $name = $this->normalizeResourceName($name);

        $actions = $this->resolveActions($options);

        if ($actions === []) return;


        Route::group($this->buildGroupAttributes($options),
            function () use ($name, $controller, $options, $actions): void {
                foreach ($actions as $actionName => $action) {
                    $this->registerRoute(
                        name: $name,
                        controller: $controller,
                        options: $options,
                        actionName: $actionName,
                        action: $action,
                    );
                }
            },
        );
    }

    /**
     * @return array<string, array{
     *     method: string,
     *     uri: string,
     *     action: string
     * }>
     */
    private function resolveActions(TrashResourceOptions $options): array
    {
        $actions = TrashResourceDefinition::actions();

        if ($options->only !== null) {
            $actions = array_intersect_key($actions, array_flip($options->only));
        }

        if ($options->except !== []) {
            $actions = array_diff_key($actions, array_flip($options->except));
        }

        return $actions;
    }

    private function registerRoute(string $name, string $controller, TrashResourceOptions $options, string $actionName, array $action): void
    {
        $route = Route::match(
            [$action['method']],
            $this->buildUri(resource: $name, actionUri: $action['uri']),
            [$controller, $action['action'],],
        )->name($this->buildRouteName(resourceName: $name, actionName: $actionName, options: $options));

        if ($options->where !== []) $route->where($options->where);

    }

    private function buildUri(string $resource, string $actionUri): string
    {
        return trim("{$resource}/{$actionUri}", '/');
    }

    private function buildRouteName(string $resourceName, string $actionName, TrashResourceOptions $options): string
    {
        if (isset($options->names[$actionName])) return trim($options->names[$actionName], '.');

        $prefix = $options->namePrefix !== ''
            ? "{$options->namePrefix}."
            : '';

        return "{$prefix}{$resourceName}.trash.{$actionName}";
    }

    private function buildGroupAttributes(TrashResourceOptions $options): array
    {
        $attributes = [];

        if ($options->prefix !== '') $attributes['prefix'] = $options->prefix;

        if ($options->middleware !== []) $attributes['middleware'] = $options->middleware;

        if ($options->scopeBindings) $attributes['scopeBindings'] = true;

        return $attributes;
    }

    private function normalizeResourceName(string $name): string
    {
        $name = trim($name, '/');

        if ($name === '') throw new InvalidArgumentException('Trash resource name cannot be empty.',);

        return $name;
    }
}
