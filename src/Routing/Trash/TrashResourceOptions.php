<?php

namespace Teksite\Extralaravel\Routing\Trash;

use BackedEnum;
use InvalidArgumentException;

final readonly class TrashResourceOptions
{

    private function __construct(
        public string $prefix,
        public array  $middleware,
        public string $namePrefix,
        public ?array $only,
        public array  $except,
        public array  $where,
        public array  $parameters,
        public array  $names,
        public array  $scoped,
        public array  $bindingFields,
        public bool   $scopeBindings,
        public bool   $shallow,
    ) {}

    public static function from(array $options): self
    {
        $only = self::normalizeActions($options['only'] ?? null);
        $except = self::normalizeActions($options['except'] ?? []);

        self::validateActionConflict($only, $except);
        self::validateActions($only);
        self::validateActions($except);

        return new self(
            self::normalizePrefix($options['prefix'] ?? ''),
            self::normalizeMiddleware($options['middleware'] ?? []),
            self::normalizeNamePrefix($options['as'] ?? ''),
            $only,
            $except,
            self::normalizeStringMap($options['where'] ?? [], 'where'),
            self::normalizeStringMap($options['parameters'] ?? [], 'parameters'),
            self::normalizeStringMap($options['names'] ?? [], 'names'),
            self::normalizeStringMap($options['scoped'] ?? [], 'scoped'),
            self::normalizeStringMap($options['bindingFields'] ?? [], 'bindingFields'),
            (bool)($options['scopeBindings'] ?? false),
            (bool)($options['shallow'] ?? false),
        );
    }

    private static function normalizePrefix(mixed $prefix): string
    {
        if (!is_string($prefix)) throw new InvalidArgumentException('Trash resource prefix must be a string.',);
        return trim($prefix, '/');
    }

    private static function normalizeNamePrefix(mixed $name): string
    {
        if (!is_string($name)) throw new InvalidArgumentException('Trash resource route name must be a string.',);
        return trim($name, '.');
    }

    private static function normalizeMiddleware(mixed $middleware): array
    {
        if (is_string($middleware)) return [$middleware];

        if (!is_array($middleware)) throw new InvalidArgumentException('Trash resource middleware must be a string or array.',);

        return array_values($middleware);
    }

    private static function normalizeActions(mixed $actions): ?array
    {
        if ($actions === null) return null;

        if (is_string($actions) || $actions instanceof BackedEnum) return [self::stringifyAction($actions)];

        if (!is_array($actions)) throw new InvalidArgumentException('Trash resource actions must be an array.',);

        return array_values(array_unique(array_map(
            static fn(mixed $action): string => self::stringifyAction($action),
            $actions,
        )));
    }

    private static function stringifyAction(mixed $action): string
    {
        if ($action instanceof BackedEnum)  return (string) $action->value;
        if (is_string($action)) return $action;
        throw new InvalidArgumentException('Trash resource action must be a string or a BackedEnum.');
    }

    private static function validateActions(?array $actions): void
    {
        if ($actions === null) return;

        $available = array_keys(TrashResourceDefinition::actions());

        $invalid = array_diff($actions, $available);

        if ($invalid === []) return;

        throw new InvalidArgumentException(
            sprintf(
                'Unknown trash resource action(s): %s. Available actions: %s.',
                implode(', ', $invalid),
                implode(', ', $available)
            )
        );
    }

    private static function validateActionConflict(?array $only, array $except): void
    {
        if ($only === null || $except === []) return;

        $conflicts = array_intersect($only, $except);

        if ($conflicts === []) return;

        throw new InvalidArgumentException(
            sprintf(
                'Trash resource actions cannot exist in both only and except: %s.',
                implode(', ', $conflicts)
            )
        );
    }

    private static function normalizeStringMap(mixed $value, string $name): array
    {
        if (!is_array($value)) throw new InvalidArgumentException("Trash resource {$name} must be an array.");

        foreach ($value as $key => $item) {
            if (!is_string($key) || !is_string($item)) {
                throw new InvalidArgumentException(
                    "Trash resource {$name} must contain string keys and values."
                );
            }
        }
        return $value;
    }
}
